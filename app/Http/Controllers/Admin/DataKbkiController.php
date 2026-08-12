<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DataKbkiController extends Controller
{
    private string $table = 'data_kbki';

    private array $levels = [
        'Seksi' => ['level' => 1, 'digits' => 1],
        'Divisi' => ['level' => 2, 'digits' => 2],
        'Kelompok' => ['level' => 3, 'digits' => 3],
        'Kelas' => ['level' => 4, 'digits' => 4],
        'Subkelas' => ['level' => 5, 'digits' => 5],
        'Kelompok Komoditas' => ['level' => 6, 'digits' => 7],
        'Komoditas' => ['level' => 7, 'digits' => 10],
    ];

    public function index(Request $request)
    {
        $tableExists = Schema::hasTable($this->table);
        $columnsReady = $this->tableReady();
        $mode = $request->query('mode');
        $editData = null;
        $perPageOptions = [5, 10, 25, 50];
        $perPage = (int) $request->query('per_page', 10);

        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = 10;
        }

        if (! $columnsReady) {
            $paginator = new LengthAwarePaginator(
                [],
                0,
                $perPage,
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('admin.data-kbki', [
                'tableExists' => $tableExists,
                'columnsReady' => false,
                'dataKbki' => collect(),
                'paginator' => $paginator,
                'stats' => $this->emptyStats(),
                'sections' => collect(),
                'parentOptions' => collect(),
                'mode' => $mode,
                'editData' => null,
                'perPageOptions' => $perPageOptions,
                'hierarchyMode' => true,
                'totalData' => 0,
            ]);
        }

        if ($request->boolean('export')) {
            return $this->exportCsv($request);
        }

        $hasStruktur = Schema::hasColumn($this->table, 'struktur');
        $hasJudul = Schema::hasColumn($this->table, 'judul');

        $sectionsQuery = DB::table($this->table);
        if ($hasStruktur) {
            $sectionsQuery->where('struktur', 'Seksi');
        } else {
            $sectionsQuery->where('level', 1);
        }

        $sections = $sectionsQuery
            ->orderBy('kode')
            ->get([
                DB::raw('CAST(kode AS VARCHAR) AS kode'),
                $hasJudul ? 'judul' : DB::raw('nama AS judul'),
            ]);

        $isFormOpen = in_array($mode, ['create', 'edit'], true)
            || $request->filled('edit');

        $parentOptions = collect();

        $totalData = DB::table($this->table)->count();
        $hierarchyMode = ! $request->filled('search')
            && ! $request->filled('struktur')
            && ! $request->filled('status');

        $perPageOptions = $hierarchyMode ? [1, 2, 5, 10] : [10, 25, 50, 100];
        $perPageDefault = $hierarchyMode ? 1 : 10;
        $perPage = (int) $request->query('per_page', $perPageDefault);

        if (! in_array($perPage, $perPageOptions, true)) {
            $perPage = $perPageDefault;
        }

        if ($hierarchyMode) {
            $sectionQuery = DB::table($this->table);
            if ($hasStruktur) {
                $sectionQuery->where('struktur', 'Seksi');
            } else {
                $sectionQuery->where('level', 1);
            }

            if ($request->has('seksi') && $request->query('seksi') !== null && trim((string) $request->query('seksi')) !== '') {
                $sectionQuery->where(
                    'kode',
                    trim((string) $request->query('seksi'))
                );
            }

            $paginator = $sectionQuery
                ->orderBy('kode')
                ->paginate($perPage)
                ->withQueryString();

            $sectionCodes = collect($paginator->items())
                ->pluck('kode')
                ->map(fn ($k) => (string) $k)
                ->filter(fn ($k) => $k !== '')
                ->values();

            $hasSeksiKode = Schema::hasColumn($this->table, 'seksi_kode');
            $dataKbkiQuery = $this->hierarchyRowsQuery();

            if ($hasSeksiKode) {
                $dataKbkiQuery->whereIn('k.seksi_kode', $sectionCodes);
            } else {
                $dataKbkiQuery->whereIn(DB::raw('SUBSTRING(CAST(k.kode AS VARCHAR), 1, 1)'), $sectionCodes);
            }

            $dataKbki = $sectionCodes->isEmpty()
                ? collect()
                : $dataKbkiQuery
                    ->orderBy('k.kode')
                    ->get();
        } else {
            $paginator = $this->filteredQuery($request)
                ->select($this->selectColumns())
                ->selectSub(function ($subQuery) {
                    $subQuery
                        ->from($this->table . ' as child')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('child.kode_induk', 'k.kode');
                }, 'child_count')
                ->orderBy('k.kode')
                ->paginate($perPage)
                ->withQueryString();

            $dataKbki = collect($paginator->items());
        }

        $stats = $this->stats();

        if ($request->filled('edit')) {
            $editData = $this->findRow($request->query('edit'));
            $mode = 'edit';
        }

        return view('admin.data-kbki', compact(
            'tableExists',
            'columnsReady',
            'dataKbki',
            'paginator',
            'stats',
            'sections',
            'parentOptions',
            'mode',
            'editData',
            'perPageOptions',
            'hierarchyMode',
            'totalData'
        ));
    }

    public function parentOptions(Request $request)
    {
        if (! $this->tableReady()) {
            return response()->json([
                'data' => [],
            ]);
        }

        $validated = $request->validate([
            'level' => ['required', 'integer', 'between:1,6'],
            'exclude' => ['nullable', 'string', 'max:10'],
        ]);

        $query = DB::table($this->table)
            ->where('level', (int) $validated['level'])
            ->orderBy('kode');

        if (isset($validated['exclude']) && trim((string) $validated['exclude']) !== '') {
            $query->where('kode', '!=', trim((string) $validated['exclude']));
        }

        $hasStruktur = Schema::hasColumn($this->table, 'struktur');
        $hasJudul = Schema::hasColumn($this->table, 'judul');

        $selectFields = [
            'kode',
            'kode_induk',
            'level',
            $hasJudul ? 'judul' : DB::raw('nama AS judul'),
        ];
        if ($hasStruktur) {
            $selectFields[] = 'struktur';
        }

        $options = $query
            ->get($selectFields)
            ->map(function ($item) {
                $levelMap = [
                    1 => 'Seksi',
                    2 => 'Divisi',
                    3 => 'Kelompok',
                    4 => 'Kelas',
                    5 => 'Subkelas',
                    6 => 'Kelompok Komoditas',
                    7 => 'Komoditas',
                ];

                return [
                    'kode' => (string) $item->kode,
                    'kode_induk' => $item->kode_induk
                        ? (string) $item->kode_induk
                        : null,
                    'struktur' => $item->struktur ?? ($levelMap[$item->level] ?? 'Komoditas'),
                    'level' => (int) $item->level,
                    'judul' => (string) $item->judul,
                ];
            })
            ->values();

        return response()->json([
            'data' => $options,
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->tableReady()) {
            return redirect()
                ->route('admin.data-kbki.index')
                ->with('error', 'Struktur tabel data_kbki belum sesuai.');
        }

        $data = $this->validatedData($request);
        $payload = $this->buildPayload($data);

        if (Schema::hasColumn($this->table, 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn($this->table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table($this->table)->insert($payload);

        return redirect()
            ->route('admin.data-kbki.index')
            ->with('success', 'Data KBKI berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        if (! $this->tableReady()) {
            return redirect()
                ->route('admin.data-kbki.index')
                ->with('error', 'Struktur tabel data_kbki belum sesuai.');
        }

        $current = $this->findRow($id);
        $data = $this->validatedData($request, $current);
        $payload = $this->buildPayload($data);

        if (Schema::hasColumn($this->table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table($this->table)
            ->where('id', $id)
            ->update($payload);

        return redirect()
            ->route('admin.data-kbki.index')
            ->with('success', 'Data KBKI berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (! $this->tableReady()) {
            return redirect()
                ->route('admin.data-kbki.index')
                ->with('error', 'Struktur tabel data_kbki belum sesuai.');
        }

        $row = $this->findRow($id);

        $hasChildren = DB::table($this->table)
            ->where('kode_induk', $row->kode)
            ->exists();

        if ($hasChildren) {
            return redirect()
                ->route('admin.data-kbki.index')
                ->with('error', 'Data tidak dapat dihapus karena masih memiliki data turunan.');
        }

        DB::table($this->table)
            ->where('id', $id)
            ->delete();

        return redirect()
            ->route('admin.data-kbki.index')
            ->with('success', 'Data KBKI berhasil dihapus.');
    }

    private function hierarchyRowsQuery()
    {
        return DB::table($this->table . ' as k')
            ->select($this->selectColumns())
            ->selectSub(function ($subQuery) {
                $subQuery
                    ->from($this->table . ' as child')
                    ->selectRaw('COUNT(*)')
                    ->whereRaw('CAST(child.kode_induk AS VARCHAR) = CAST(k.kode AS VARCHAR)');
            }, 'child_count');
    }

    private function selectColumns(): array
    {
        $hasStruktur = Schema::hasColumn($this->table, 'struktur');
        $hasJudul = Schema::hasColumn($this->table, 'judul');
        $hasCatatan = Schema::hasColumn($this->table, 'catatan');
        $hasDeskripsi = Schema::hasColumn($this->table, 'deskripsi');
        $hasStatus = Schema::hasColumn($this->table, 'status');
        $hasSeksiKode = Schema::hasColumn($this->table, 'seksi_kode');
        $hasJumlahDigit = Schema::hasColumn($this->table, 'jumlah_digit');

        $cols = [
            'k.id',
            'k.level',
            DB::raw('CAST(k.kode AS VARCHAR) AS kode'),
            DB::raw('CAST(k.kode_induk AS VARCHAR) AS kode_induk'),
            'k.created_at',
            'k.updated_at',
        ];

        if ($hasStruktur) {
            $cols[] = 'k.struktur';
        } else {
            $cols[] = DB::raw("CASE k.level
                WHEN 1 THEN 'Seksi'
                WHEN 2 THEN 'Divisi'
                WHEN 3 THEN 'Kelompok'
                WHEN 4 THEN 'Kelas'
                WHEN 5 THEN 'Subkelas'
                WHEN 6 THEN 'Kelompok Komoditas'
                WHEN 7 THEN 'Komoditas'
                ELSE 'Komoditas'
            END AS struktur");
        }

        if ($hasJumlahDigit) {
            $cols[] = 'k.jumlah_digit';
        } else {
            $cols[] = DB::raw('LENGTH(k.kode) AS jumlah_digit');
        }

        if ($hasSeksiKode) {
            $cols[] = 'k.seksi_kode';
            $cols[] = 'k.divisi_kode';
            $cols[] = 'k.kelompok_kode';
            $cols[] = 'k.kelas_kode';
            $cols[] = 'k.subkelas_kode';
            $cols[] = 'k.kelompok_komoditas_kode';
            $cols[] = 'k.komoditas_kode';
        } else {
            $cols[] = DB::raw('SUBSTRING(k.kode, 1, 1) AS seksi_kode');
            $cols[] = DB::raw('CASE WHEN k.level >= 2 THEN SUBSTRING(k.kode, 1, 2) ELSE NULL END AS divisi_kode');
            $cols[] = DB::raw('CASE WHEN k.level >= 3 THEN SUBSTRING(k.kode, 1, 3) ELSE NULL END AS kelompok_kode');
            $cols[] = DB::raw('CASE WHEN k.level >= 4 THEN SUBSTRING(k.kode, 1, 4) ELSE NULL END AS kelas_kode');
            $cols[] = DB::raw('CASE WHEN k.level >= 5 THEN SUBSTRING(k.kode, 1, 5) ELSE NULL END AS subkelas_kode');
            $cols[] = DB::raw('CASE WHEN k.level >= 6 THEN SUBSTRING(k.kode, 1, 7) ELSE NULL END AS kelompok_komoditas_kode');
            $cols[] = DB::raw('CASE WHEN k.level >= 7 THEN k.kode ELSE NULL END AS komoditas_kode');
        }

        if ($hasJudul) {
            $cols[] = 'k.judul';
        } else {
            $cols[] = DB::raw('k.nama AS judul');
        }

        if ($hasCatatan) {
            $cols[] = 'k.catatan';
        } elseif ($hasDeskripsi) {
            $cols[] = DB::raw('k.deskripsi AS catatan');
        } else {
            $cols[] = DB::raw('NULL AS catatan');
        }

        if ($hasStatus) {
            $cols[] = 'k.status';
        } else {
            $cols[] = DB::raw("'Aktif' AS status");
        }

        if (Schema::hasColumn($this->table, 'halaman')) {
            $cols[] = 'k.halaman';
        } else {
            $cols[] = DB::raw('NULL AS halaman');
        }

        if (Schema::hasColumn($this->table, 'sumber_sheet')) {
            $cols[] = 'k.sumber_sheet';
        } else {
            $cols[] = DB::raw("'Dataset KBKI 2015' AS sumber_sheet");
        }

        if (Schema::hasColumn($this->table, 'baris_asli')) {
            $cols[] = 'k.baris_asli';
        } else {
            $cols[] = DB::raw('NULL AS baris_asli');
        }

        if (Schema::hasColumn($this->table, 'kode_asli')) {
            $cols[] = 'k.kode_asli';
        } else {
            $cols[] = DB::raw('k.kode AS kode_asli');
        }

        return $cols;
    }

    private function filteredQuery(Request $request)
    {
        $query = DB::table($this->table . ' as k');

        if ($request->filled('search')) {
            $search = '%' . mb_strtolower(trim((string) $request->query('search'))) . '%';
            $titleCol = Schema::hasColumn($this->table, 'judul') ? 'k.judul' : 'k.nama';

            $query->where(function ($subQuery) use ($search, $titleCol) {
                $subQuery
                    ->whereRaw('LOWER(CAST(k.kode AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(' . $titleCol . ' AS TEXT)) LIKE ?', [$search]);
            });
        }

        if ($request->filled('struktur')) {
            if (Schema::hasColumn($this->table, 'struktur')) {
                $query->where('k.struktur', $request->query('struktur'));
            } else {
                $levelConfig = $this->levels[$request->query('struktur')] ?? null;
                if ($levelConfig) {
                    $query->where('k.level', $levelConfig['level']);
                }
            }
        }

        if ($request->filled('seksi')) {
            if (Schema::hasColumn($this->table, 'seksi_kode')) {
                $query->where('k.seksi_kode', trim((string) $request->query('seksi')));
            } else {
                $query->whereRaw('SUBSTRING(CAST(k.kode AS VARCHAR), 1, 1) = ?', [trim((string) $request->query('seksi'))]);
            }
        }

        if ($request->filled('status') && Schema::hasColumn($this->table, 'status')) {
            $query->where('k.status', $request->query('status'));
        }

        return $query;
    }

    private function exportCsv(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->select($this->selectColumns())
            ->orderBy('k.kode');

        $filename = 'data-kbki-2015-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'id',
                'struktur',
                'level',
                'jumlah_digit',
                'kode',
                'kode_induk',
                'seksi_kode',
                'divisi_kode',
                'kelompok_kode',
                'kelas_kode',
                'subkelas_kode',
                'kelompok_komoditas_kode',
                'komoditas_kode',
                'judul',
                'halaman',
                'sumber_sheet',
                'baris_asli',
                'kode_asli',
                'catatan',
                'status',
            ]);

            foreach ($query->cursor() as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->struktur,
                    $row->level,
                    $row->jumlah_digit,
                    $row->kode,
                    $row->kode_induk,
                    $row->seksi_kode,
                    $row->divisi_kode,
                    $row->kelompok_kode,
                    $row->kelas_kode,
                    $row->subkelas_kode,
                    $row->kelompok_komoditas_kode,
                    $row->komoditas_kode,
                    $row->judul,
                    $row->halaman,
                    $row->sumber_sheet,
                    $row->baris_asli,
                    $row->kode_asli,
                    $row->catatan,
                    $row->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedData(Request $request, $current = null): array
    {
        $data = $request->validate([
            'struktur' => ['required', 'string', 'in:' . implode(',', array_keys($this->levels))],
            'kode_induk' => ['nullable', 'string', 'max:10'],
            'kode' => ['required', 'string', 'max:10'],
            'judul' => ['required', 'string', 'max:1000'],
            'halaman' => ['nullable', 'integer', 'min:1'],
            'sumber_sheet' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ], [
            'struktur.required' => 'Level struktur wajib dipilih.',
            'struktur.in' => 'Level struktur tidak valid.',
            'kode.required' => 'Kode KBKI wajib diisi.',
            'judul.required' => 'Judul KBKI wajib diisi.',
            'halaman.integer' => 'Halaman harus berupa angka.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $config = $this->levels[$data['struktur']];
        $data['struktur'] = trim($data['struktur']);
        $data['level'] = $config['level'];
        $data['jumlah_digit'] = $config['digits'];
        $data['kode'] = trim($data['kode']);
        $data['kode_induk'] = isset($data['kode_induk']) && trim($data['kode_induk']) !== ''
            ? trim($data['kode_induk'])
            : null;
        $data['judul'] = trim($data['judul']);
        $data['halaman'] = isset($data['halaman']) && $data['halaman'] !== ''
            ? (int) $data['halaman']
            : null;
        $data['sumber_sheet'] = $this->nullableText($data['sumber_sheet'] ?? null) ?? 'Input Web';
        $data['catatan'] = $this->nullableText($data['catatan'] ?? null);
        $data['status'] = trim($data['status']);

        $errors = [];

        if (! preg_match('/^\d{' . $data['jumlah_digit'] . '}$/', $data['kode'])) {
            $errors['kode'] = 'Kode ' . $data['struktur'] . ' harus terdiri dari ' . $data['jumlah_digit'] . ' digit angka.';
        }

        if ($data['level'] === 1) {
            $data['kode_induk'] = null;
        } elseif ($data['kode_induk'] === null || $data['kode_induk'] === '') {
            $errors['kode_induk'] = 'Data induk wajib dipilih.';
        }

        $parent = null;

        if ($data['kode_induk'] !== null && $data['kode_induk'] !== '') {
            $parent = DB::table($this->table)
                ->where('kode', (string) $data['kode_induk'])
                ->first();

            if (! $parent) {
                $errors['kode_induk'] = 'Data induk tidak ditemukan.';
            } elseif ((int) $parent->level !== $data['level'] - 1) {
                $errors['kode_induk'] = 'Level data induk tidak sesuai dengan struktur yang dipilih.';
            } elseif (! str_starts_with((string) $data['kode'], (string) $parent->kode)) {
                $errors['kode'] = 'Kode harus diawali dengan kode induk ' . $parent->kode . '.';
            }
        }

        $duplicateQuery = DB::table($this->table)
            ->whereRaw('TRIM(CAST(kode AS TEXT)) = ?', [$data['kode']]);

        if ($current) {
            $duplicateQuery->where('id', '!=', $current->id);
        }

        if ($duplicateQuery->exists()) {
            $errors['kode'] = 'Kode KBKI sudah terdaftar.';
        }

        if ($current) {
            $hasChildren = DB::table($this->table)
                ->where('kode_induk', $current->kode)
                ->exists();

            $hasStruktur = Schema::hasColumn($this->table, 'struktur');
            $structureChanged = $hasStruktur ? ($data['struktur'] !== ($current->struktur ?? null)) : false;
            $codeChanged = $data['kode'] !== $current->kode;
            $parentChanged = ($data['kode_induk'] ?? null) !== ($current->kode_induk ?? null);

            if ($hasChildren && ($structureChanged || $codeChanged || $parentChanged)) {
                $errors['struktur'] = 'Struktur, kode, dan induk tidak dapat diubah karena data ini memiliki turunan.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $data['parent'] = $parent;

        return $data;
    }

    private function buildPayload(array $data): array
    {
        $level = $data['level'];
        $kode = $data['kode'];
        $hasStruktur = Schema::hasColumn($this->table, 'struktur');

        if ($hasStruktur) {
            return [
                'struktur' => $data['struktur'],
                'level' => $level,
                'jumlah_digit' => $data['jumlah_digit'],
                'kode' => $kode,
                'kode_induk' => $level === 1 ? null : $data['kode_induk'],
                'seksi_kode' => substr($kode, 0, 1),
                'divisi_kode' => $level >= 2 ? substr($kode, 0, 2) : null,
                'kelompok_kode' => $level >= 3 ? substr($kode, 0, 3) : null,
                'kelas_kode' => $level >= 4 ? substr($kode, 0, 4) : null,
                'subkelas_kode' => $level >= 5 ? substr($kode, 0, 5) : null,
                'kelompok_komoditas_kode' => $level >= 6 ? substr($kode, 0, 7) : null,
                'komoditas_kode' => $level === 7 ? $kode : null,
                'judul' => $data['judul'],
                'halaman' => $data['halaman'],
                'sumber_sheet' => $data['sumber_sheet'],
                'kode_asli' => $kode,
                'catatan' => $data['catatan'],
                'status' => $data['status'],
            ];
        }

        $payload = [
            'level' => $level,
            'kode' => $kode,
            'kode_induk' => $level === 1 ? null : $data['kode_induk'],
        ];

        if (Schema::hasColumn($this->table, 'nama')) {
            $payload['nama'] = $data['judul'];
        } elseif (Schema::hasColumn($this->table, 'judul')) {
            $payload['judul'] = $data['judul'];
        }

        if (Schema::hasColumn($this->table, 'deskripsi')) {
            $payload['deskripsi'] = $data['catatan'];
        } elseif (Schema::hasColumn($this->table, 'catatan')) {
            $payload['catatan'] = $data['catatan'];
        }

        return $payload;
    }

    private function findRow($id)
    {
        $hasJudul = Schema::hasColumn($this->table, 'judul');
        $hasCatatan = Schema::hasColumn($this->table, 'catatan');
        $hasDeskripsi = Schema::hasColumn($this->table, 'deskripsi');

        $row = DB::table($this->table)
            ->where('id', $id)
            ->firstOrFail();

        $levelMap = [
            1 => 'Seksi',
            2 => 'Divisi',
            3 => 'Kelompok',
            4 => 'Kelas',
            5 => 'Subkelas',
            6 => 'Kelompok Komoditas',
            7 => 'Komoditas',
        ];

        $row->kode = (string) $row->kode;
        if (isset($row->kode_induk) && $row->kode_induk !== null) {
            $row->kode_induk = (string) $row->kode_induk;
        }

        if (! isset($row->struktur)) {
            $row->struktur = $levelMap[$row->level] ?? 'Komoditas';
        }
        if (! isset($row->judul)) {
            $row->judul = $row->nama ?? '';
        }
        if (! isset($row->halaman)) {
            $row->halaman = null;
        }
        if (! isset($row->sumber_sheet)) {
            $row->sumber_sheet = 'Input Web';
        }
        if (! isset($row->catatan)) {
            $row->catatan = $row->deskripsi ?? '';
        }
        if (! isset($row->status)) {
            $row->status = 'Aktif';
        }

        return $row;
    }

    private function stats(): array
    {
        $hasStruktur = Schema::hasColumn($this->table, 'struktur');

        if ($hasStruktur) {
            $counts = DB::table($this->table)
                ->select('struktur', DB::raw('COUNT(*) AS total'))
                ->groupBy('struktur')
                ->pluck('total', 'struktur');
        } else {
            $levelCounts = DB::table($this->table)
                ->select('level', DB::raw('COUNT(*) AS total'))
                ->groupBy('level')
                ->pluck('total', 'level');

            $levelMap = [
                1 => 'Seksi',
                2 => 'Divisi',
                3 => 'Kelompok',
                4 => 'Kelas',
                5 => 'Subkelas',
                6 => 'Kelompok Komoditas',
                7 => 'Komoditas',
            ];

            $counts = collect();
            foreach ($levelCounts as $lvl => $cnt) {
                $stName = $levelMap[$lvl] ?? 'Komoditas';
                $counts[$stName] = ($counts[$stName] ?? 0) + (int) $cnt;
            }
        }

        $total = (int) $counts->sum();

        return [
            ['label' => 'Seksi', 'value' => (int) ($counts['Seksi'] ?? 0), 'description' => '1 digit', 'icon' => 'fa-layer-group', 'tone' => 'green'],
            ['label' => 'Divisi', 'value' => (int) ($counts['Divisi'] ?? 0), 'description' => '2 digit', 'icon' => 'fa-building', 'tone' => 'orange'],
            ['label' => 'Kelompok', 'value' => (int) ($counts['Kelompok'] ?? 0), 'description' => '3 digit', 'icon' => 'fa-object-group', 'tone' => 'blue'],
            ['label' => 'Kelas', 'value' => (int) ($counts['Kelas'] ?? 0), 'description' => '4 digit', 'icon' => 'fa-folder-tree', 'tone' => 'red'],
            ['label' => 'Subkelas', 'value' => (int) ($counts['Subkelas'] ?? 0), 'description' => '5 digit', 'icon' => 'fa-sitemap', 'tone' => 'violet'],
            ['label' => 'Kelompok Komoditas', 'value' => (int) ($counts['Kelompok Komoditas'] ?? 0), 'description' => '7 digit', 'icon' => 'fa-boxes-stacked', 'tone' => 'pink'],
            ['label' => 'Komoditas', 'value' => (int) ($counts['Komoditas'] ?? 0), 'description' => '10 digit', 'icon' => 'fa-cubes', 'tone' => 'cyan'],
            ['label' => 'Total Data', 'value' => $total, 'description' => 'Seluruh struktur', 'icon' => 'fa-database', 'tone' => 'teal'],
        ];
    }

    private function emptyStats(): array
    {
        return [
            ['label' => 'Seksi', 'value' => 0, 'description' => '1 digit', 'icon' => 'fa-layer-group', 'tone' => 'green'],
            ['label' => 'Divisi', 'value' => 0, 'description' => '2 digit', 'icon' => 'fa-building', 'tone' => 'orange'],
            ['label' => 'Kelompok', 'value' => 0, 'description' => '3 digit', 'icon' => 'fa-object-group', 'tone' => 'blue'],
            ['label' => 'Kelas', 'value' => 0, 'description' => '4 digit', 'icon' => 'fa-folder-tree', 'tone' => 'red'],
            ['label' => 'Subkelas', 'value' => 0, 'description' => '5 digit', 'icon' => 'fa-sitemap', 'tone' => 'violet'],
            ['label' => 'Kelompok Komoditas', 'value' => 0, 'description' => '7 digit', 'icon' => 'fa-boxes-stacked', 'tone' => 'pink'],
            ['label' => 'Komoditas', 'value' => 0, 'description' => '10 digit', 'icon' => 'fa-cubes', 'tone' => 'cyan'],
            ['label' => 'Total Data', 'value' => 0, 'description' => 'Seluruh struktur', 'icon' => 'fa-database', 'tone' => 'teal'],
        ];
    }

    private function tableReady(): bool
    {
        return Schema::hasTable($this->table) && Schema::hasColumns($this->table, [
            'id',
            'kode',
            'level',
        ]);
    }

    private function nullableText($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
