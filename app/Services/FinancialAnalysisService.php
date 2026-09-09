<?php

namespace App\Services;

use App\Models\Project;

class FinancialAnalysisService
{
    /**
     * Menghitung indikator kelayakan investasi proyek:
     * - Net Present Value (NPV)
     * - Internal Rate of Return (IRR)
     * - Benefit Cost Ratio (BCR)
     * - Payback Period (PP) serta tabel jadwal akumulasi saldo
     */
    public static function calculateFeasibility(Project $project): array
    {
        $project->loadMissing(['capexComponents', 'plComponents.yearlyData']);

        // 1. Hitung Total CAPEX (Investasi Awal)
        $totalCapex = 0;
        foreach ($project->capexComponents as $comp) {
            if ($comp->parent_id !== null) {
                $vol = $comp->volume && $comp->volume > 0 ? (float)$comp->volume : 1;
                $luas = $comp->luas && $comp->luas > 0 ? (float)$comp->luas : 0;
                $harga = $comp->harga_m2 ? (float)$comp->harga_m2 : 0;

                if ($luas > 0) {
                    $totalCapex += $vol * $luas * $harga;
                } else {
                    $totalCapex += $vol * $harga;
                }
            }
        }

        // 2. Pendapatan & Biaya Operasional per Tahun
        $jangkaWaktu = max(1, (int)$project->jangka_waktu_tahun);
        $pendapatanPerTahun = [];
        $opexPerTahun = [];

        for ($t = 1; $t <= $jangkaWaktu; $t++) {
            $pendapatanPerTahun[$t] = 0;
            $opexPerTahun[$t] = 0;
        }

        foreach ($project->plComponents as $comp) {
            foreach ($comp->yearlyData as $yd) {
                $t = (int) $yd->tahun_ke;
                $val = (float) $yd->nilai;
                if ($t >= 1 && $t <= $jangkaWaktu) {
                    if ($comp->tipe_kategori === 'PENDAPATAN') {
                        $pendapatanPerTahun[$t] += $val;
                    } else if ($comp->tipe_kategori === 'BIAYA_OPERASIONAL') {
                        $opexPerTahun[$t] += $val;
                    }
                }
            }
        }

        // 3. Parameter Pembiayaan & Kredit
        $rasioEquity = (float) $project->rasio_modal_sendiri;
        $rasioDebt = (float) $project->rasio_pinjaman_kredit;
        $sukuBunga = (float) $project->suku_bunga_kredit;
        $tenor = (int) $project->tenor_kredit_tahun;

        $equityAmount = $totalCapex * ($rasioEquity / 100);
        $debtAmount = $totalCapex * ($rasioDebt / 100);

        $rate = $sukuBunga / 100;
        $pmt = 0;
        if ($debtAmount > 0 && $tenor > 0) {
            if ($rate > 0) {
                $factor = pow(1 + $rate, $tenor);
                $pmt = $debtAmount * ($rate * $factor) / ($factor - 1);
            } else {
                $pmt = $debtAmount / $tenor;
            }
        }

        $pokokPerTahun = [];
        $bungaPerTahun = [];
        $netCashflowPerTahun = [0 => -$totalCapex];
        $akumulasiSaldoPerTahun = [0 => -$totalCapex];

        $totalBenefit = 0;
        for ($t = 1; $t <= $jangkaWaktu; $t++) {
            $totalBenefit += $pendapatanPerTahun[$t];

            if ($t <= $tenor && $debtAmount > 0) {
                $bunga = $debtAmount * $rate;
                $pokok = max(0, $pmt - $bunga);
            } else {
                $pokok = 0;
                $bunga = 0;
            }

            $pokokPerTahun[$t] = $pokok;
            $bungaPerTahun[$t] = $bunga;

            $opBersih = ($pendapatanPerTahun[$t] ?? 0) - ($opexPerTahun[$t] ?? 0);
            $netCashflow = $opBersih - $pokok - $bunga;
            $netCashflowPerTahun[$t] = $netCashflow;

            $akumulasiSaldoPerTahun[$t] = $akumulasiSaldoPerTahun[$t - 1] + $netCashflow;
        }

        // 4. Perhitungan Payback Period & Jadwal
        $paybackTahun = null;
        $paybackBulan = null;
        $paybackText = 'Belum Tercapai';
        $breakevenYear = null;

        $paybackSchedule = [
            [
                'tahun' => 0,
                'saldo' => -$totalCapex,
                'akumulasi' => -$totalCapex,
                'is_breakeven' => false,
            ]
        ];

        for ($t = 1; $t <= $jangkaWaktu; $t++) {
            $isBreakeven = false;
            if ($akumulasiSaldoPerTahun[$t] >= 0 && $breakevenYear === null) {
                $breakevenYear = $t;
                $isBreakeven = true;
                $prevDeficit = abs($akumulasiSaldoPerTahun[$t - 1]);
                $currCashflow = $netCashflowPerTahun[$t];

                $fraction = $currCashflow > 0 ? ($prevDeficit / $currCashflow) : 0;
                $months = round($fraction * 12);
                $years = $t - 1;
                if ($months >= 12) {
                    $years += 1;
                    $months -= 12;
                }
                $paybackTahun = $years;
                $paybackBulan = (int)$months;
                if ($paybackBulan > 0) {
                    $paybackText = "{$paybackTahun} Tahun {$paybackBulan} Bulan";
                } else {
                    $paybackText = "{$paybackTahun} Tahun";
                }
            }

            $paybackSchedule[] = [
                'tahun' => $t,
                'saldo' => $netCashflowPerTahun[$t],
                'akumulasi' => $akumulasiSaldoPerTahun[$t],
                'is_breakeven' => $isBreakeven,
            ];
        }

        // 5. Benefit Cost Ratio (BCR)
        $totalCost = $totalCapex;
        $bcr = $totalCost > 0 ? ($totalBenefit / $totalCost) : 0;

        // 6. Net Present Value (NPV)
        $discountRate = ($sukuBunga > 0) ? ($sukuBunga / 100) : 0.0805;
        $npv = -$totalCapex;
        for ($t = 1; $t <= $jangkaWaktu; $t++) {
            $npv += $netCashflowPerTahun[$t] / pow(1 + $discountRate, $t);
        }

        // 7. Internal Rate of Return (IRR)
        $irr = self::calculateIRR($netCashflowPerTahun);

        return [
            'total_capex' => $totalCapex,
            'total_cost' => $totalCost,
            'total_benefit' => $totalBenefit,
            'bcr' => $bcr,
            'discount_rate_pct' => $discountRate * 100,
            'npv' => $npv,
            'irr' => $irr,
            'irr_pct' => $irr !== null ? ($irr * 100) : null,
            'payback_tahun' => $paybackTahun,
            'payback_bulan' => $paybackBulan,
            'payback_text' => $paybackText,
            'payback_schedule' => $paybackSchedule,
            'breakeven_year' => $breakevenYear,
            'is_feasible' => ($npv > 0 && $bcr >= 1.0),
        ];
    }

    /**
     * Menghitung Internal Rate of Return (IRR) dengan metode Newton-Raphson dan fallback Bisection.
     */
    public static function calculateIRR(array $cashFlows, float $guess = 0.1, int $maxIterations = 1000, float $precision = 1e-6): ?float
    {
        $hasPositive = false;
        $hasNegative = false;
        foreach ($cashFlows as $cf) {
            if ($cf > 0) $hasPositive = true;
            if ($cf < 0) $hasNegative = true;
        }
        if (!$hasPositive || !$hasNegative) {
            return null;
        }

        // Newton-Raphson
        $rate = $guess;
        for ($i = 0; $i < $maxIterations; $i++) {
            $npv = 0.0;
            $dnpv = 0.0;

            foreach ($cashFlows as $t => $cf) {
                $denom = pow(1.0 + $rate, $t);
                if ($denom == 0) continue;
                $npv += $cf / $denom;
                $dnpv -= ($t * $cf) / (pow(1.0 + $rate, $t + 1));
            }

            if (abs($npv) < $precision) {
                return $rate;
            }

            if (abs($dnpv) < 1e-12) {
                break;
            }

            $newRate = $rate - ($npv / $dnpv);
            if ($newRate <= -0.99 || $newRate > 50.0 || is_nan($newRate)) {
                break;
            }

            if (abs($newRate - $rate) < $precision) {
                return $newRate;
            }
            $rate = $newRate;
        }

        // Bisection fallback
        $low = -0.90;
        $high = 10.0;

        $npvAt = function($r) use ($cashFlows) {
            $sum = 0.0;
            foreach ($cashFlows as $t => $cf) {
                $sum += $cf / pow(1.0 + $r, $t);
            }
            return $sum;
        };

        $npvLow = $npvAt($low);
        $npvHigh = $npvAt($high);

        if ($npvLow * $npvHigh > 0) {
            for ($h = 10.0; $h <= 50.0; $h += 10.0) {
                $npvH = $npvAt($h);
                if ($npvLow * $npvH <= 0) {
                    $high = $h;
                    $npvHigh = $npvH;
                    break;
                }
            }
        }

        if ($npvLow * $npvHigh <= 0) {
            for ($i = 0; $i < 200; $i++) {
                $mid = ($low + $high) / 2.0;
                $npvMid = $npvAt($mid);
                if (abs($npvMid) < $precision || ($high - $low) / 2.0 < $precision) {
                    return $mid;
                }
                if ($npvMid * $npvLow <= 0) {
                    $high = $mid;
                    $npvHigh = $npvMid;
                } else {
                    $low = $mid;
                    $npvLow = $npvMid;
                }
            }
            return ($low + $high) / 2.0;
        }

        return null;
    }
}
