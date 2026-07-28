<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashFlowController extends Controller
{
    private function authorizeProjectOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }
    }

    public function index(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $project->load(['capexComponents', 'plComponents.yearlyData']);

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

        $pendapatanPerTahun = [];
        $opexPerTahun = [];

        for ($t = 1; $t <= $project->jangka_waktu_tahun; $t++) {
            $pendapatanPerTahun[$t] = 0;
            $opexPerTahun[$t] = 0;
        }

        foreach ($project->plComponents as $comp) {
            foreach ($comp->yearlyData as $yd) {
                $t = (int) $yd->tahun_ke;
                $val = (float) $yd->nilai;
                if ($t >= 1 && $t <= $project->jangka_waktu_tahun) {
                    if ($comp->tipe_kategori === 'PENDAPATAN') {
                        $pendapatanPerTahun[$t] += $val;
                    } else if ($comp->tipe_kategori === 'BIAYA_OPERASIONAL') {
                        $opexPerTahun[$t] += $val;
                    }
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'total_capex' => $totalCapex,
                'pendapatan_per_tahun' => $pendapatanPerTahun,
                'opex_per_tahun' => $opexPerTahun,
                'settings' => [
                    'rasio_modal_sendiri' => (float) $project->rasio_modal_sendiri,
                    'rasio_pinjaman_kredit' => (float) $project->rasio_pinjaman_kredit,
                    'suku_bunga_kredit' => (float) $project->suku_bunga_kredit,
                    'tenor_kredit_tahun' => (int) $project->tenor_kredit_tahun,
                    'pl_persentase_pajak_penghasilan' => (float) $project->pl_persentase_pajak_penghasilan,
                    'pl_persentase_pajak_daerah' => (float) $project->pl_persentase_pajak_daerah,
                ]
            ]);
        }

        return view('operator.peluang_investasi.projects.cashflow', compact('project', 'totalCapex', 'pendapatanPerTahun', 'opexPerTahun'));
    }

    public function updateSettings(Request $request, Project $project)
    {
        $this->authorizeProjectOwner($project);

        $validated = $request->validate([
            'rasio_modal_sendiri' => 'required|numeric|min:0|max:100',
            'rasio_pinjaman_kredit' => 'required|numeric|min:0|max:100',
            'suku_bunga_kredit' => 'required|numeric|min:0|max:100',
            'tenor_kredit_tahun' => 'required|integer|min:1|max:50',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Parameter Pembiayaan & Kredit berhasil diperbarui!',
            'project' => $project
        ]);
    }
}
