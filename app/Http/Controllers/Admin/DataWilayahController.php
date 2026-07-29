<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DataWilayahController extends Controller
{
    private const TABLE_PROVINSI = 'provinsi';
    private const TABLE_KABUPATEN = 'kabupaten';
    private const TABLE_KECAMATAN = 'kecamatan';
    private const TABLE_DESA = 'kelurahan_desa';

    public function index(Request $request)
    {
        $tableExists = $this->allTablesExist();

        if (! $tableExists) {
            $dataWilayah = new LengthAwarePaginator([], 0, 10, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('admin.data-wilayah', [
                'tableExists' => false,
                'dataWilayah' => $dataWilayah,
                'stats' => $this->emptyStats(),
                'kabupatenOptions' => collect(),
                'kecamatanOptions' => collect(),
                'desaOptions' => collect(),
                'mode' => $request->query('mode'),
                'editData' => null,
            ]);
        }

        $query = $this->wilayahQuery();

        if ($request->filled('search')) {
            $search = '%' . strtolower(trim((string) $request->search)) . '%';

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->whereRaw('LOWER(CAST(p.nama_provinsi AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(p.provinsi_id AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(kab.nama_kabupaten AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(kab.kab_id AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(kec.nama_kecamatan AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(kec.id AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(d.nama_desa AS TEXT)) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(CAST(d.id AS TEXT)) LIKE ?', [$search]);

                if ($this->desaHasColumn('status')) {
                    $subQuery->orWhereRaw(
                        'LOWER(CAST(d.status AS TEXT)) LIKE ?',
                        [$search]
                    );
                }

                if ($this->desaHasColumn('keterangan')) {
                    $subQuery->orWhereRaw(
                        'LOWER(CAST(d.keterangan AS TEXT)) LIKE ?',
                        [$search]
                    );
                }
            });
        }

        if ($request->filled('kode_kabupaten')) {
            $query->where(
                'kab.kab_id',
                $this->digits((string) $request->kode_kabupaten)
            );
        }

        if ($request->filled('kode_kecamatan')) {
            $query->where(
                'kec.id',
                $this->digits((string) $request->kode_kecamatan)
            );
        }

        if ($request->filled('status')) {
            if ($this->desaHasColumn('status')) {
                $query->whereRaw(
                    'LOWER(TRIM(CAST(d.status AS TEXT))) = ?',
                    [strtolower(trim((string) $request->status))]
                );
            } elseif (strtolower((string) $request->status) === 'nonaktif') {
                $query->whereRaw('1 = 0');
            }
        }

        $dataWilayah = $query
            ->orderBy('p.nama_provinsi')
            ->orderBy('kab.nama_kabupaten')
            ->orderBy('kec.nama_kecamatan')
            ->orderBy('d.nama_desa')
            ->paginate(10)
            ->withQueryString();

        $dataWilayah->getCollection()->transform(
            fn ($item) => $this->decorateRow($item)
        );

        $stats = [
            [
                'label' => 'Total Wilayah',
                'value' => DB::table(self::TABLE_DESA)->count(),
                'color' => 'green',
                'icon' => 'fa-map-location-dot',
            ],
            [
                'label' => 'Kab/Kota',
                'value' => DB::table(self::TABLE_KABUPATEN)->count(),
                'color' => 'yellow',
                'icon' => 'fa-city',
            ],
            [
                'label' => 'Kecamatan',
                'value' => DB::table(self::TABLE_KECAMATAN)->count(),
                'color' => 'blue',
                'icon' => 'fa-map',
            ],
            [
                'label' => 'Desa/Kelurahan',
                'value' => DB::table(self::TABLE_DESA)->count(),
                'color' => 'red',
                'icon' => 'fa-location-dot',
            ],
        ];

        $kabupatenOptions = DB::table(self::TABLE_KABUPATEN)
            ->select('kab_id', 'nama_kabupaten')
            ->orderBy('nama_kabupaten')
            ->get()
            ->map(fn ($item) => (object) [
                'kode_kabupaten' => $this->formatCode($item->kab_id, [2, 2]),
                'nama_kabupaten' => $item->nama_kabupaten,
            ]);

        $kecamatanOptions = DB::table(self::TABLE_KECAMATAN)
            ->select('kabupaten_id as kab_id', 'id as kec_id', 'nama_kecamatan')
            ->when(
                $request->filled('kode_kabupaten'),
                fn (Builder $q) => $q->where(
                    'kabupaten_id',
                    $this->digits((string) $request->kode_kabupaten)
                )
            )
            ->orderBy('nama_kecamatan')
            ->get()
            ->map(fn ($item) => (object) [
                'kode_kabupaten' => $this->formatCode($item->kab_id, [2, 2]),
                'kode_kecamatan' => $this->formatCode($item->kec_id, [2, 2, 2]),
                'nama_kecamatan' => $item->nama_kecamatan,
            ]);

        $desaOptions = DB::table(self::TABLE_DESA)
            ->select('kecamatan_id as kec_id', 'id as desa_id', 'nama_desa as nama_kelurahan_desa')
            ->when(
                $request->filled('kode_kecamatan'),
                fn (Builder $q) => $q->where(
                    'kecamatan_id',
                    $this->digits((string) $request->kode_kecamatan)
                )
            )
            ->orderBy('nama_desa')
            ->get()
            ->map(fn ($item) => (object) [
                'kode_kecamatan' => $this->formatCode($item->kec_id, [2, 2, 2]),
                'kode_desa' => $this->formatCode($item->desa_id, [2, 2, 2, 4]),
                'nama_desa' => $item->nama_kelurahan_desa,
            ]);

        $mode = $request->query('mode');
        $editData = null;

        if ($request->filled('edit')) {
            $editData = $this->findWilayahOrFail((string) $request->edit);
            $mode = 'edit';
        }

        return view('admin.data-wilayah', compact(
            'tableExists',
            'dataWilayah',
            'stats',
            'kabupatenOptions',
            'kecamatanOptions',
            'desaOptions',
            'mode',
            'editData'
        ));
    }

    public function provinceOptions(): JsonResponse
    {
        $items = DB::table(self::TABLE_PROVINSI)
            ->select('provinsi_id', 'nama_provinsi')
            ->orderBy('nama_provinsi')
            ->get()
            ->map(fn ($item) => [
                'code' => $this->formatCode($item->provinsi_id, [2]),
                'name' => (string) $item->nama_provinsi,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function regencyOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'province_code' => ['required', 'string', 'max:20'],
        ]);

        $items = DB::table(self::TABLE_KABUPATEN)
            ->select('kab_id', 'nama_kabupaten')
            ->where('provinsi_id', $this->digits($validated['province_code']))
            ->orderBy('nama_kabupaten')
            ->get()
            ->map(fn ($item) => [
                'code' => $this->formatCode($item->kab_id, [2, 2]),
                'name' => (string) $item->nama_kabupaten,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function districtOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'regency_code' => ['required', 'string', 'max:30'],
        ]);

        $items = DB::table(self::TABLE_KECAMATAN)
            ->select('id as kec_id', 'nama_kecamatan')
            ->where('kabupaten_id', $this->digits($validated['regency_code']))
            ->orderBy('nama_kecamatan')
            ->get()
            ->map(fn ($item) => [
                'code' => $this->formatCode($item->kec_id, [2, 2, 2]),
                'name' => (string) $item->nama_kecamatan,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function villageOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district_code' => ['required', 'string', 'max:40'],
        ]);

        $items = DB::table(self::TABLE_DESA)
            ->select('id as desa_id', 'nama_desa')
            ->where('kecamatan_id', $this->digits($validated['district_code']))
            ->orderBy('nama_desa')
            ->get()
            ->map(fn ($item) => [
                'code' => $this->formatCode($item->desa_id, [2, 2, 2, 4]),
                'name' => (string) $item->nama_desa,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        if (! $this->allTablesExist()) {
            return redirect()
                ->route('admin.data-wilayah.index')
                ->with('error', 'Tabel wilayah belum tersedia lengkap di Supabase.');
        }

        $data = $request->validate($this->rules(), $this->messages());
        $ids = $this->validatedIds($data);

        if (
            DB::table(self::TABLE_DESA)
                ->where('id', $ids['desa_id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'kode_desa' => 'Kode desa/kelurahan ini sudah ada.',
            ]);
        }

        DB::transaction(function () use ($data, $ids) {
            $this->saveHierarchy($data, $ids);

            $payload = [
                'id' => $ids['desa_id'],
                'kecamatan_id' => $ids['kec_id'],
                'nama_desa' => trim($data['nama_desa']),
                'updated_at' => now(),
            ];

            if ($this->desaHasColumn('jenis')) {
                $payload['jenis'] = $data['jenis'] ?? null;
            }

            if ($this->desaHasColumn('status')) {
                $payload['status'] = $data['status'] ?? 'Aktif';
            }

            if ($this->desaHasColumn('keterangan')) {
                $payload['keterangan'] = filled($data['keterangan'] ?? null)
                    ? trim($data['keterangan'])
                    : null;
            }

            if ($this->desaHasColumn('created_at')) {
                $payload['created_at'] = now();
            }

            DB::table(self::TABLE_DESA)->insert($payload);
        });

        return redirect()
            ->route('admin.data-wilayah.index')
            ->with('success', 'Data wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, string $dataWilayah)
    {
        $oldDesaId = $this->digits($dataWilayah);
        $this->findWilayahOrFail($oldDesaId);

        $data = $request->validate(
            $this->rules($oldDesaId),
            $this->messages()
        );

        $ids = $this->validatedIds($data);

        if (
            (string) $ids['desa_id'] !== (string) $oldDesaId
            && DB::table(self::TABLE_DESA)
                ->where('id', $ids['desa_id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'kode_desa' => 'Kode desa/kelurahan ini sudah ada.',
            ]);
        }

        DB::transaction(function () use ($data, $ids, $oldDesaId) {
            $this->saveHierarchy($data, $ids);

            $payload = [
                'id' => $ids['desa_id'],
                'kecamatan_id' => $ids['kec_id'],
                'nama_desa' => trim($data['nama_desa']),
                'updated_at' => now(),
            ];

            if ($this->desaHasColumn('status')) {
                $payload['status'] = $data['status'] ?? 'Aktif';
            }

            if ($this->desaHasColumn('keterangan')) {
                $payload['keterangan'] = filled($data['keterangan'] ?? null)
                    ? trim($data['keterangan'])
                    : null;
            }

            DB::table(self::TABLE_DESA)
                ->where('id', $oldDesaId)
                ->update($payload);
        });

        return redirect()
            ->route('admin.data-wilayah.index')
            ->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(string $dataWilayah)
    {
        $desaId = $this->digits($dataWilayah);

        DB::table(self::TABLE_DESA)
            ->where('id', $desaId)
            ->delete();

        return redirect()
            ->route('admin.data-wilayah.index')
            ->with('success', 'Data wilayah berhasil dihapus.');
    }

    private function wilayahQuery(): Builder
    {
        $selects = [
            'd.id as id',
            'p.provinsi_id',
            'p.nama_provinsi',
            'kab.kab_id',
            'kab.nama_kabupaten',
            'kec.id as kec_id',
            'kec.nama_kecamatan',
            'd.id as desa_id',
            'd.nama_desa as nama_desa',
        ];

        if ($this->desaHasColumn('status')) {
            $selects[] = 'd.status';
        } else {
            $selects[] = DB::raw("'Aktif' as status");
        }

        if ($this->desaHasColumn('keterangan')) {
            $selects[] = 'd.keterangan';
        } else {
            $selects[] = DB::raw('NULL as keterangan');
        }

        return DB::table(self::TABLE_DESA . ' as d')
            ->join(self::TABLE_KECAMATAN . ' as kec', 'kec.id', '=', 'd.kecamatan_id')
            ->join(self::TABLE_KABUPATEN . ' as kab', 'kab.kab_id', '=', 'kec.kabupaten_id')
            ->join(self::TABLE_PROVINSI . ' as p', 'p.provinsi_id', '=', 'kab.provinsi_id')
            ->select($selects);
    }

    private function findWilayahOrFail(string $desaId): object
    {
        $item = $this->wilayahQuery()
            ->where('d.id', $this->digits($desaId))
            ->first();

        abort_if(! $item, 404);

        return $this->decorateRow($item);
    }

    private function decorateRow(object $item): object
    {
        $item->id = (string) $item->desa_id;
        $item->kode_provinsi = $this->formatCode($item->provinsi_id, [2]);
        $item->kode_kabupaten = $this->formatCode($item->kab_id, [2, 2]);
        $item->kode_kecamatan = $this->formatCode($item->kec_id, [2, 2, 2]);
        $item->kode_desa = $this->formatCode($item->desa_id, [2, 2, 2, 4]);
        $item->status = $item->status ?? 'Aktif';

        return $item;
    }

    private function saveHierarchy(array $data, array $ids): void
    {
        DB::table(self::TABLE_PROVINSI)->updateOrInsert(
            ['provinsi_id' => $ids['provinsi_id']],
            [
                'nama_provinsi' => trim($data['nama_provinsi']),
                'updated_at' => now(),
            ]
        );

        DB::table(self::TABLE_KABUPATEN)->updateOrInsert(
            ['kab_id' => $ids['kab_id']],
            [
                'provinsi_id' => $ids['provinsi_id'],
                'nama_kabupaten' => trim($data['nama_kabupaten']),
                'updated_at' => now(),
            ]
        );

        DB::table(self::TABLE_KECAMATAN)->updateOrInsert(
            ['id' => $ids['kec_id']],
            [
                'kabupaten_id' => $ids['kab_id'],
                'nama_kecamatan' => trim($data['nama_kecamatan']),
                'updated_at' => now(),
            ]
        );
    }

    private function rules(?string $ignoreDesaId = null): array
    {
        $rules = [
            'nama_provinsi' => ['required', 'string', 'max:255'],
            'kode_provinsi' => ['required', 'regex:/^\d{2}$/'],
            'nama_kabupaten' => ['required', 'string', 'max:255'],
            'kode_kabupaten' => ['required', 'regex:/^\d{2}\.\d{2}$/'],
            'nama_kecamatan' => ['required', 'string', 'max:255'],
            'kode_kecamatan' => ['required', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'],
            'nama_desa' => ['required', 'string', 'max:255'],
            'kode_desa' => [
                'required',
                'regex:/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/',
            ],
        ];

        if ($this->desaHasColumn('status')) {
            $rules['status'] = ['required', Rule::in(['Aktif', 'Nonaktif'])];
        }

        if ($this->desaHasColumn('keterangan')) {
            $rules['keterangan'] = ['nullable', 'string'];
        }

        return $rules;
    }

    private function validatedIds(array $data): array
    {
        $ids = [
            'provinsi_id' => $this->digits($data['kode_provinsi']),
            'kab_id' => $this->digits($data['kode_kabupaten']),
            'kec_id' => $this->digits($data['kode_kecamatan']),
            'desa_id' => $this->digits($data['kode_desa']),
        ];

        $errors = [];

        if (! str_starts_with($ids['kab_id'], $ids['provinsi_id'])) {
            $errors['kode_kabupaten'] =
                'Kode kabupaten/kota harus diawali kode provinsi '
                . $data['kode_provinsi'] . '.';
        }

        if (! str_starts_with($ids['kec_id'], $ids['kab_id'])) {
            $errors['kode_kecamatan'] =
                'Kode kecamatan harus diawali kode kabupaten/kota '
                . $data['kode_kabupaten'] . '.';
        }

        if (! str_starts_with($ids['desa_id'], $ids['kec_id'])) {
            $errors['kode_desa'] =
                'Kode desa/kelurahan harus diawali kode kecamatan '
                . $data['kode_kecamatan'] . '.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $ids;
    }

    private function messages(): array
    {
        return [
            'nama_provinsi.required' => 'Nama provinsi wajib dipilih atau diisi sendiri.',
            'kode_provinsi.required' => 'Kode provinsi wajib diisi.',
            'kode_provinsi.regex' => 'Kode provinsi wajib tepat 2 angka, contoh: 13.',
            'nama_kabupaten.required' => 'Nama kabupaten/kota wajib dipilih atau diisi sendiri.',
            'kode_kabupaten.required' => 'Kode kabupaten/kota wajib diisi.',
            'kode_kabupaten.regex' => 'Kode kabupaten/kota harus berformat 13.01.',
            'nama_kecamatan.required' => 'Nama kecamatan wajib dipilih atau diisi sendiri.',
            'kode_kecamatan.required' => 'Kode kecamatan wajib diisi.',
            'kode_kecamatan.regex' => 'Kode kecamatan harus berformat 13.01.01.',
            'nama_desa.required' => 'Nama desa/kelurahan wajib dipilih atau diisi sendiri.',
            'kode_desa.required' => 'Kode desa/kelurahan wajib diisi.',
            'kode_desa.regex' => 'Kode desa/kelurahan harus berformat 13.01.01.2001.',
            'kode_desa.unique' => 'Kode desa/kelurahan ini sudah ada.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ];
    }

    private function digits(string|int|null $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    private function formatCode(string|int|null $value, array $parts): string
    {
        $digits = $this->digits($value);
        $result = [];
        $offset = 0;

        foreach ($parts as $length) {
            $result[] = substr($digits, $offset, $length);
            $offset += $length;
        }

        return implode('.', array_filter($result, fn ($part) => $part !== ''));
    }

    private function allTablesExist(): bool
    {
        return Schema::hasTable(self::TABLE_PROVINSI)
            && Schema::hasTable(self::TABLE_KABUPATEN)
            && Schema::hasTable(self::TABLE_KECAMATAN)
            && Schema::hasTable(self::TABLE_DESA);
    }

    private function desaHasColumn(string $column): bool
    {
        return Schema::hasTable(self::TABLE_DESA)
            && Schema::hasColumn(self::TABLE_DESA, $column);
    }

    private function emptyStats(): array
    {
        return [
            ['label' => 'Total Wilayah', 'value' => 0, 'color' => 'green', 'icon' => 'fa-map-location-dot'],
            ['label' => 'Kab/Kota', 'value' => 0, 'color' => 'yellow', 'icon' => 'fa-city'],
            ['label' => 'Kecamatan', 'value' => 0, 'color' => 'blue', 'icon' => 'fa-map'],
            ['label' => 'Desa/Kelurahan', 'value' => 0, 'color' => 'red', 'icon' => 'fa-location-dot'],
        ];
    }
}