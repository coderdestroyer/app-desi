<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class MoneyCurrencyController extends Controller
{
    /**
     * Lama penyimpanan kurs terbaru.
     */
    private const CACHE_MINUTES = 10;

    /**
     * Menampilkan halaman konversi.
     */
    public function index()
    {
        $rateData = $this->resolveRate();

        return view('admin.money-currency', [
            'amount' => null,
            'result' => null,
            'direction' => 'idr_to_usd',
            'rate' => $rateData['rate'],
            'updatedAt' => $rateData['updated_at'],
            'isStale' => $rateData['is_stale'],
            'rateError' => $rateData['error'],
        ]);
    }

    /**
     * Memproses konversi dua arah.
     */
    public function convert(Request $request)
    {
        $direction = (string) $request->input(
            'direction',
            'idr_to_usd'
        );

        $normalizedAmount = $this->normalizeAmount(
            (string) $request->input('amount', ''),
            $direction
        );

        $request->merge([
            'direction' => $direction,
            'amount' => $normalizedAmount,
        ]);

        $validated = $request->validate(
            [
                'direction' => [
                    'required',
                    Rule::in([
                        'idr_to_usd',
                        'usd_to_idr',
                    ]),
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'gt:0',
                    'max:999999999999999',
                ],
            ],
            [
                'direction.required' =>
                    'Arah konversi wajib dipilih.',

                'direction.in' =>
                    'Arah konversi tidak valid.',

                'amount.required' =>
                    'Nominal uang wajib diisi.',

                'amount.numeric' =>
                    'Nominal harus berupa angka.',

                'amount.gt' =>
                    'Nominal harus lebih besar dari nol.',

                'amount.max' =>
                    'Nominal terlalu besar.',
            ]
        );

        $rateData = $this->resolveRate();

        if (!$rateData['rate']) {
            return back()
                ->withInput()
                ->withErrors([
                    'currency' =>
                        $rateData['error']
                        ?? 'Kurs Dollar Amerika sedang tidak tersedia.',
                ]);
        }

        $amount = (float) $validated['amount'];
        $rate = (float) $rateData['rate'];

        /*
         * CurrencyFreaks mengembalikan:
         * 1 USD = sejumlah IDR.
         *
         * IDR ke USD: amount / rate
         * USD ke IDR: amount * rate
         */
        $result = $direction === 'idr_to_usd'
            ? $amount / $rate
            : $amount * $rate;

        return view('admin.money-currency', [
            'amount' => $amount,
            'result' => $result,
            'direction' => $direction,
            'rate' => $rate,
            'updatedAt' => $rateData['updated_at'],
            'isStale' => $rateData['is_stale'],
            'rateError' => $rateData['error'],
        ]);
    }

    /**
     * Membersihkan format nominal sebelum validasi.
     */
    private function normalizeAmount(
        string $amount,
        string $direction
    ): string {
        $amount = trim($amount);

        /*
         * Rupiah tidak menggunakan desimal pada halaman ini.
         *
         * Rp19.000 menjadi 19000.
         */
        if ($direction === 'idr_to_usd') {
            return preg_replace('/[^0-9]/', '', $amount) ?? '';
        }

        /*
         * Dollar dapat menggunakan desimal:
         *
         * 10
         * 10,5
         * 10.50
         * $10,50
         */
        $amount = preg_replace(
            '/[^0-9,.]/',
            '',
            $amount
        ) ?? '';

        /*
         * Format Indonesia:
         * 10,50 menjadi 10.50
         */
        if (
            str_contains($amount, ',') &&
            !str_contains($amount, '.')
        ) {
            return str_replace(',', '.', $amount);
        }

        /*
         * Format internasional:
         * 1,000.50 menjadi 1000.50
         */
        if (
            str_contains($amount, ',') &&
            str_contains($amount, '.')
        ) {
            return str_replace(',', '', $amount);
        }

        return $amount;
    }

    /**
     * Mengambil kurs dari cache atau API (ExchangeRate-API / Frankfurter / CurrencyFreaks).
     */
    private function resolveRate(): array
    {
        $freshCacheKey = 'currency.usd_idr.fresh';
        $backupCacheKey = 'currency.usd_idr.backup';

        $cachedRate = Cache::get($freshCacheKey);

        if (is_array($cachedRate)) {
            return [
                ...$cachedRate,
                'is_stale' => false,
                'error' => null,
            ];
        }

        try {
            $rateData = $this->requestLatestRate();

            Cache::put(
                $freshCacheKey,
                $rateData,
                now()->addMinutes(self::CACHE_MINUTES)
            );

            /*
             * Backup digunakan apabila seluruh API sedang gangguan.
             */
            Cache::put(
                $backupCacheKey,
                $rateData,
                now()->addDays(7)
            );

            return [
                ...$rateData,
                'is_stale' => false,
                'error' => null,
            ];
        } catch (Throwable $exception) {
            report($exception);

            $backupRate = Cache::get($backupCacheKey);

            if (is_array($backupRate)) {
                return [
                    ...$backupRate,
                    'is_stale' => true,
                    'error' =>
                        'Layanan API kurs sedang tidak dapat dihubungi. '
                        . 'Sistem menggunakan kurs terakhir yang tersimpan.',
                ];
            }

            // Standby estimated rate fallback agar fitur kalkulator tetap berfungsi
            return [
                'rate' => 16200.0,
                'updated_at' => now()->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') . ' WIB (Estimasi)',
                'is_stale' => true,
                'error' =>
                    'Tidak dapat terhubung ke API kurs secara langsung. '
                    . 'Sistem menggunakan nilai acuan kurs estimasi (1 USD = Rp16.200).',
            ];
        }
    }

    /**
     * Mengambil kurs USD ke IDR terbaru menggunakan beberapa penyedia API.
     */
    private function requestLatestRate(): array
    {
        // 1. Penyedia Utama: ExchangeRate-API (Gratis, tanpa API Key, sangat stabil)
        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                $rate = data_get($data, 'rates.IDR');

                if (is_numeric($rate) && (float) $rate > 0) {
                    $timestamp = $data['time_last_update_unix'] ?? null;
                    $dateStr = $timestamp
                        ? Carbon::createFromTimestamp($timestamp)->toIso8601String()
                        : null;

                    return [
                        'rate' => (float) $rate,
                        'updated_at' => $this->formatApiDate($dateStr),
                    ];
                }
            }
        } catch (Throwable $e) {
            // Lanjut ke penyedia cadangan berikutnya
        }

        // 2. Cadangan 1: Frankfurter API (Gratis, data ECB, tanpa API Key)
        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->get('https://api.frankfurter.dev/v1/latest?from=USD&to=IDR');

            if ($response->successful()) {
                $data = $response->json();
                $rate = data_get($data, 'rates.IDR');

                if (is_numeric($rate) && (float) $rate > 0) {
                    return [
                        'rate' => (float) $rate,
                        'updated_at' => $this->formatApiDate($data['date'] ?? null),
                    ];
                }
            }
        } catch (Throwable $e) {
            // Lanjut ke penyedia cadangan berikutnya
        }

        // 3. Cadangan 2: CurrencyFreaks (jika API Key diatur di .env)
        $apiKey = config('services.currencyfreaks.api_key');
        $baseUrl = rtrim(
            (string) config('services.currencyfreaks.base_url', 'https://api.currencyfreaks.com/v2.0'),
            '/'
        );

        if (!empty($apiKey)) {
            try {
                $response = Http::withoutVerifying()
                    ->acceptJson()
                    ->connectTimeout(5)
                    ->timeout(10)
                    ->get($baseUrl . '/rates/latest', [
                        'apikey' => $apiKey,
                        'symbols' => 'IDR',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rate = data_get($data, 'rates.IDR');

                    if (is_numeric($rate) && (float) $rate > 0) {
                        return [
                            'rate' => (float) $rate,
                            'updated_at' => $this->formatApiDate($data['date'] ?? null),
                        ];
                    }
                }
            } catch (Throwable $e) {
                // Lanjut ke exception
            }
        }

        throw new RuntimeException(
            'Gagal mengambil nilai kurs IDR dari seluruh penyedia layanan API.'
        );
    }

    /**
     * Mengubah waktu API ke WIB dengan locale bahasa Indonesia.
     * Mengubah waktu API ke WIB.
     */
    private function formatApiDate(?string $date): string
    {
        try {
            return Carbon::parse($date)
                ->setTimezone('Asia/Jakarta')
                ->locale('id')
                ->translatedFormat('d F Y, H:i') . ' WIB';
        } catch (Throwable $exception) {
            return now()
                ->setTimezone('Asia/Jakarta')
                ->locale('id')
                ->translatedFormat('d F Y, H:i') . ' WIB';
        }
    }
}