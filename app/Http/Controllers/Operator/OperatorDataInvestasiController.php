<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\DataInvestasi;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Http\Request;

class OperatorDataInvestasiController extends Controller
{
    public function index(Request $request)
    {
        $query = DataInvestasi::with(['provinsi', 'kabupaten']);

        // Provinsi Filter (Default to 12 / Sumatera Utara on first visit)
        $selectedProvId = $request->has('provinsi_id') ? (string) $request->get('provinsi_id') : '12';
        if ($selectedProvId !== '' && $selectedProvId !== 'all') {
            $query->where('provinsi_id', $selectedProvId);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('nama_sektor', 'like', "%{$search}%")
                  ->orWhere('id_laporan_lkpm', 'like', "%{$search}%")
                  ->orWhere('id_proyek_nku', 'like', "%{$search}%");
            });
        }

        // Tahun Filter
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Status Filter (PMDN/PMA)
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        // Kabupaten Filter
        if ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_id', $request->kabupaten_id);
        }

        // Stats calculation
        $totalDataCount = (clone $query)->count();
        $totalNilaiInvestasi = (clone $query)->sum('nilai_investasi');
        $totalPmdn = (clone $query)->where('status', 'PMDN')->count();
        $totalPma = (clone $query)->where('status', 'PMA')->count();

        $stats = [
            [
                'label' => 'Total Record Data',
                'value' => number_format($totalDataCount, 0, ',', '.'),
                'description' => 'Jumlah entri data investasi',
                'icon' => 'fa-database',
                'tone' => 'green',
            ],
            [
                'label' => 'Total Nilai Investasi',
                'value' => 'Rp ' . number_format($totalNilaiInvestasi / 1000000000000, 2, ',', '.') . ' T',
                'description' => 'Akumulasi realisasi nilai investasi',
                'icon' => 'fa-money-bill-wave',
                'tone' => 'blue',
            ],
            [
                'label' => 'Investasi PMDN',
                'value' => number_format($totalPmdn, 0, ',', '.'),
                'description' => 'Penanaman Modal Dalam Negeri',
                'icon' => 'fa-building-flag',
                'tone' => 'orange',
            ],
            [
                'label' => 'Investasi PMA',
                'value' => number_format($totalPma, 0, ',', '.'),
                'description' => 'Penanaman Modal Asing',
                'icon' => 'fa-globe',
                'tone' => 'violet',
            ],
        ];

        // Per Page & Pagination (Default 15)
        $perPage = (int) $request->get('per_page', 15);
        $dataInvestasi = $query->orderBy('tahun', 'desc')->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        // Option lists for filters
        $tahunList = DataInvestasi::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (empty($tahunList)) {
            $tahunList = [date('Y')];
        }

        $provinsiList = Provinsi::orderBy('nama_provinsi')->get();

        // Kabupaten list for filter bar (filtered by selectedProvId if specific province is chosen)
        $kabupatenFilterQuery = Kabupaten::orderBy('nama_kabupaten');
        if ($selectedProvId !== '' && $selectedProvId !== 'all') {
            $kabupatenFilterQuery->where('provinsi_id', $selectedProvId);
        }
        $kabupatenFilterList = $kabupatenFilterQuery->get();

        return view('operator.data-investasi', compact(
            'dataInvestasi',
            'stats',
            'tahunList',
            'provinsiList',
            'kabupatenFilterList',
            'selectedProvId'
        ));
    }
}
