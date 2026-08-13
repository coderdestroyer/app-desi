<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataInvestasi;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use XMLReader;
use SimpleXMLElement;
use ZipArchive;

class AdminDataInvestasiController extends Controller
{
    public function index(Request $request)
    {
        $tableExists = true;
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

        // Option lists for filters & modals
        $tahunList = DataInvestasi::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (empty($tahunList)) {
            $tahunList = [date('Y')];
        }

        $provinsiList = Provinsi::orderBy('nama_provinsi')->get();
        $kabupatenList = Kabupaten::orderBy('nama_kabupaten')->get();

        // Kabupaten list for filter bar (filtered by selectedProvId if specific province is chosen)
        $kabupatenFilterQuery = Kabupaten::orderBy('nama_kabupaten');
        if ($selectedProvId !== '' && $selectedProvId !== 'all') {
            $kabupatenFilterQuery->where('provinsi_id', $selectedProvId);
        }
        $kabupatenFilterList = $kabupatenFilterQuery->get();

        $mode = $request->get('mode', 'list');
        $editData = null;
        if ($request->filled('edit')) {
            $editData = DataInvestasi::find($request->edit);
        }

        return view('admin.data-investasi', compact(
            'tableExists',
            'dataInvestasi',
            'stats',
            'tahunList',
            'provinsiList',
            'kabupatenList',
            'kabupatenFilterList',
            'selectedProvId',
            'mode',
            'editData'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'status'          => 'required|in:PMDN,PMA',
            'provinsi_id'     => 'required|exists:provinsi,provinsi_id',
            'kabupaten_id'    => 'nullable|exists:kabupaten,kab_id',
            'nama_sektor'     => 'nullable|string|max:255',
            'tahun'           => 'required|integer|min:2000|max:2100',
            'nilai_investasi' => 'required|numeric|min:0',
            'id_laporan_lkpm' => 'nullable|integer',
            'id_proyek_nku'   => 'nullable|integer',
        ]);

        DataInvestasi::create($validated);

        return redirect()->route('admin.data-investasi.index')
            ->with('success', 'Data investasi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $dataInvestasi = DataInvestasi::findOrFail($id);

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'status'          => 'required|in:PMDN,PMA',
            'provinsi_id'     => 'required|exists:provinsi,provinsi_id',
            'kabupaten_id'    => 'nullable|exists:kabupaten,kab_id',
            'nama_sektor'     => 'nullable|string|max:255',
            'tahun'           => 'required|integer|min:2000|max:2100',
            'nilai_investasi' => 'required|numeric|min:0',
            'id_laporan_lkpm' => 'nullable|integer',
            'id_proyek_nku'   => 'nullable|integer',
        ]);

        $dataInvestasi->update($validated);

        return redirect()->route('admin.data-investasi.index')
            ->with('success', 'Data investasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dataInvestasi = DataInvestasi::findOrFail($id);
        $dataInvestasi->delete();

        return redirect()->route('admin.data-investasi.index')
            ->with('success', 'Data investasi berhasil dihapus.');
    }

    /**
     * Import CSV / Excel file into data_investasi table
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $ext = strtolower($file->getClientOriginalExtension());

        $allRows = $this->parseFileToRows($path, $ext);

        if (empty($allRows) || count($allRows) < 2) {
            return redirect()->back()->with('error', 'File kosong atau tidak dapat dibaca.');
        }

        $header = array_shift($allRows);

        // Clean & normalize headers
        $normalizedHeaders = array_map(function ($h) {
            $clean = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $h); // Remove UTF-8 BOM
            $clean = strtolower(trim($clean));
            return preg_replace('/[^a-z0-9]+/', '_', trim($clean));
        }, $header);

        // Map column names to target DB keys
        $headerMap = [];
        foreach ($normalizedHeaders as $idx => $normKey) {
            if (in_array($normKey, ['id_laporan_lkpm', 'idlaporanlkpm', 'id_lkpm', 'lkpm'])) {
                $headerMap['id_laporan_lkpm'] = $idx;
            } elseif (in_array($normKey, ['id_proyek_nku', 'idproyeknku', 'id_nku', 'nku'])) {
                $headerMap['id_proyek_nku'] = $idx;
            } elseif (in_array($normKey, ['nama_perusahaan', 'perusahaan', 'nama_pt'])) {
                $headerMap['nama_perusahaan'] = $idx;
            } elseif (in_array($normKey, ['status', 'status_modal', 'jenis_investasi'])) {
                $headerMap['status'] = $idx;
            } elseif (in_array($normKey, ['tahun', 'thn', 'year'])) {
                $headerMap['tahun'] = $idx;
            } elseif (in_array($normKey, ['kab_kota_usaha', 'kab_kota', 'kabupaten', 'kabupaten_usaha', 'nama_kabupaten', 'kota'])) {
                $headerMap['kabupaten'] = $idx;
            } elseif (in_array($normKey, ['provinsi_usaha', 'provinsi', 'nama_provinsi'])) {
                $headerMap['provinsi'] = $idx;
            } elseif (in_array($normKey, ['nama_sektor', 'sektor', 'sektor_usaha'])) {
                $headerMap['nama_sektor'] = $idx;
            } elseif (in_array($normKey, ['nilai_investasi_rp', 'nilai_investasi_rp_', 'nilai_investasi', 'nilai_investasi_dalam_rp', 'nilai'])) {
                $headerMap['nilai_investasi'] = $idx;
            }
        }

        if (!isset($headerMap['nama_perusahaan'])) {
            return redirect()->back()->with('error', 'Kolom "Nama Perusahaan" tidak ditemukan di dalam file.');
        }

        // Helper functions for robust location string matching
        $cleanStr = function ($s) {
            $str = strtoupper(trim((string)$s));
            return trim(preg_replace('/[^A-Z0-9]+/', ' ', $str));
        };

        $getCoreName = function ($s) use ($cleanStr) {
            $norm = $cleanStr($s);
            return trim(preg_replace('/^(PROVINSI|KABUPATEN|KAB|KOTA)\s+/', '', $norm));
        };

        // Build Provinsi lookup map
        $provinsis = Provinsi::all();
        $provLookup = [];
        foreach ($provinsis as $p) {
            $norm = $cleanStr($p->nama_provinsi);
            $core = $getCoreName($p->nama_provinsi);
            $provLookup[$norm] = $p->provinsi_id;
            $provLookup[$core] = $p->provinsi_id;
            $provLookup[str_replace(' ', '', $norm)] = $p->provinsi_id;
        }

        // Build Kabupaten lookup & Kab->Prov mapping
        $kabupatens = Kabupaten::all();
        $kabLookup = [];
        $kabProvMap = [];
        foreach ($kabupatens as $k) {
            $norm = $cleanStr($k->nama_kabupaten);
            $core = $getCoreName($k->nama_kabupaten);
            $noSpace = str_replace(' ', '', $core);

            $kabLookup[$norm] = $k->kab_id;
            $kabLookup["KABUPATEN " . $core] = $k->kab_id;
            $kabLookup["KAB " . $core] = $k->kab_id;
            $kabLookup["KOTA " . $core] = $k->kab_id;
            $kabLookup[$core] = $k->kab_id;
            $kabLookup[$noSpace] = $k->kab_id;

            $kabProvMap[$k->kab_id] = $k->provinsi_id;
        }

        // Default Sumut provinsi_id (12 if present in database)
        $defaultProvId = 12;
        if (!Provinsi::where('provinsi_id', 12)->exists()) {
            $defaultProvId = $provinsis->first()?->provinsi_id ?? 12;
        }

        $importedCount = 0;
        $insertBatch = [];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($allRows as $row) {
                if (empty(array_filter($row))) {
                    continue;
                }

                $namaPerusahaan = isset($headerMap['nama_perusahaan']) ? trim($row[$headerMap['nama_perusahaan']] ?? '') : '';
                if (empty($namaPerusahaan)) {
                    continue;
                }

                $statusRaw = isset($headerMap['status']) ? strtoupper(trim($row[$headerMap['status']] ?? 'PMDN')) : 'PMDN';
                $status = in_array($statusRaw, ['PMA', 'PMDN']) ? $statusRaw : 'PMDN';

                $tahunRaw = isset($headerMap['tahun']) ? (int) preg_replace('/[^0-9]/', '', $row[$headerMap['tahun']] ?? date('Y')) : (int) date('Y');
                $tahun = ($tahunRaw >= 2000 && $tahunRaw <= 2100) ? $tahunRaw : (int) date('Y');

                // Parse Nilai Investasi
                $nilaiRaw = isset($headerMap['nilai_investasi']) ? trim($row[$headerMap['nilai_investasi']] ?? '0') : '0';
                $nilaiClean = preg_replace('/[^0-9\.,]/', '', $nilaiRaw);
                if (str_contains($nilaiClean, ',') && str_contains($nilaiClean, '.')) {
                    $nilaiClean = str_replace('.', '', $nilaiClean);
                    $nilaiClean = str_replace(',', '.', $nilaiClean);
                } elseif (str_contains($nilaiClean, ',')) {
                    $nilaiClean = str_replace(',', '.', $nilaiClean);
                }
                $nilaiInvestasi = (float) $nilaiClean;

                // Resolve Kabupaten
                $kabNameRaw = isset($headerMap['kabupaten']) ? trim($row[$headerMap['kabupaten']] ?? '') : '';
                $kabNorm = $cleanStr($kabNameRaw);
                $kabCore = $getCoreName($kabNameRaw);
                $kabNoSpace = str_replace(' ', '', $kabCore);
                $kabupatenId = $kabLookup[$kabNorm] ?? ($kabLookup[$kabCore] ?? ($kabLookup[$kabNoSpace] ?? null));

                // Resolve Provinsi (Prefer Kabupaten's provinsi_id if kabupaten is matched)
                $provinsiId = $kabupatenId ? ($kabProvMap[$kabupatenId] ?? null) : null;

                if (!$provinsiId) {
                    $provNameRaw = isset($headerMap['provinsi']) ? trim($row[$headerMap['provinsi']] ?? '') : '';
                    $provNorm = $cleanStr($provNameRaw);
                    $provCore = $getCoreName($provNameRaw);
                    $provNoSpace = str_replace(' ', '', $provNorm);
                    $provinsiId = $provLookup[$provNorm] ?? ($provLookup[$provCore] ?? ($provLookup[$provNoSpace] ?? $defaultProvId));
                }

                $idLkpm = isset($headerMap['id_laporan_lkpm']) ? (int) preg_replace('/[^0-9]/', '', $row[$headerMap['id_laporan_lkpm']] ?? 0) : null;
                $idNku = isset($headerMap['id_proyek_nku']) ? (int) preg_replace('/[^0-9]/', '', $row[$headerMap['id_proyek_nku']] ?? 0) : null;
                $namaSektor = isset($headerMap['nama_sektor']) ? trim($row[$headerMap['nama_sektor']] ?? '') : null;

                $insertBatch[] = [
                    'id_laporan_lkpm' => $idLkpm ?: null,
                    'id_proyek_nku'   => $idNku ?: null,
                    'nama_perusahaan' => $namaPerusahaan,
                    'status'          => $status,
                    'provinsi_id'     => $provinsiId,
                    'kabupaten_id'    => $kabupatenId,
                    'nama_sektor'     => $namaSektor,
                    'tahun'           => $tahun,
                    'nilai_investasi' => $nilaiInvestasi,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                $importedCount++;

                if (count($insertBatch) >= 500) {
                    DB::table('data_investasi')->insert($insertBatch);
                    $insertBatch = [];
                }
            }

            if (!empty($insertBatch)) {
                DB::table('data_investasi')->insert($insertBatch);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }

        if ($importedCount > 0) {
            return redirect()->route('admin.data-investasi.index')
                ->with('success', "Berhasil mengimpor " . number_format($importedCount, 0, ',', '.') . " data investasi baru.");
        }

        return redirect()->back()->with('error', 'Tidak ada data valid yang dapat diimpor dari file.');
    }

    /**
     * Download CSV template file for import
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Template_Import_Data_Investasi.csv"',
        ];

        $columns = [
            'id_laporan_lkpm',
            'id_proyek_nku',
            'Nama Perusahaan',
            'Status',
            'Tahun',
            'Kab / Kota usaha',
            'provinsi usaha',
            'Nama Sektor',
            'Nilai Investasi (Rp)',
        ];

        $sampleData = [
            [
                '727',
                '42836',
                'FIRST MUJUR PLANTATION & INDUSTRY',
                'PMDN',
                '2026',
                'Kabupaten Tapanuli Selatan',
                'Sumatera Utara',
                'Tanaman Pangan, Perkebunan, dan Peternakan',
                '15000000000',
            ],
            [
                '734',
                '45538',
                'HALIM SARIGANDUM PRIMA',
                'PMDN',
                '2026',
                'Kabupaten Deli Serdang',
                'Sumatera Utara',
                'Industri Makanan',
                '45500000000',
            ],
        ];

        $callback = function () use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper to parse CSV, TXT, or XLSX file into rows array
     */
    private function parseFileToRows(string $filePath, string $ext): array
    {
        if (in_array($ext, ['xlsx', 'xls'])) {
            return $this->parseXlsxRows($filePath);
        }

        // CSV / TXT parser
        $rows = [];
        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return [];
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Fast Native OpenXML (.xlsx) Reader
     */
    private function parseXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return [];
        }

        // Extract shared strings
        $sharedStrings = [];
        if ($sharedStringsXml) {
            $xmlReader = new XMLReader();
            $xmlReader->xml($sharedStringsXml);
            while ($xmlReader->read()) {
                if ($xmlReader->nodeType === XMLReader::ELEMENT && $xmlReader->name === 't') {
                    $sharedStrings[] = $xmlReader->readString();
                }
            }
            $xmlReader->close();
        }

        // Read sheet rows
        $rows = [];
        $xmlReader = new XMLReader();
        $xmlReader->xml($sheetXml);

        $currentRow = [];
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType === XMLReader::ELEMENT && $xmlReader->name === 'row') {
                $currentRow = [];
            } elseif ($xmlReader->nodeType === XMLReader::ELEMENT && $xmlReader->name === 'c') {
                $type = $xmlReader->getAttribute('t');
                $val = '';
                $cellNode = new SimpleXMLElement($xmlReader->readOuterXml());
                if (isset($cellNode->v)) {
                    $v = (string)$cellNode->v;
                    if ($type === 's' && isset($sharedStrings[(int)$v])) {
                        $val = $sharedStrings[(int)$v];
                    } else {
                        $val = $v;
                    }
                }
                $currentRow[] = $val;
            } elseif ($xmlReader->nodeType === XMLReader::END_ELEMENT && $xmlReader->name === 'row') {
                $rows[] = $currentRow;
            }
        }
        $xmlReader->close();

        return $rows;
    }
}
