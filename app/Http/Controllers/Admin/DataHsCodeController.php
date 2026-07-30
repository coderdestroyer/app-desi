<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class DataHsCodeController extends Controller
{
    private string $table;

    public function __construct()
    {
        $this->table = $this->resolveTableName();
    }

    private function resolveTableName(): string
    {
        foreach ([
            'data_hs_code',
            'hs_codes',
            'hs_code',
            'hscode',
        ] as $tableName) {
            if (Schema::hasTable($tableName)) {
                return $tableName;
            }
        }

        return 'data_hs_code';
    }

    public function index(Request $request)
    {
        $tableExists = Schema::hasTable($this->table);
        $columns = $this->columnMap();

        $hasIdColumn = $columns['id'] !== null;
        $hasStatusColumn = $columns['status'] !== null;

        $columnsReady = $tableExists
            && $columns['hs_code']
            && $columns['uraian_barang'];

        if (! $columnsReady) {
            $dataHsCode = new LengthAwarePaginator(
                [],
                0,
                10,
                1,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            return view('admin.hs-code', [
                'tableExists' => $tableExists,
                'columnsReady' => false,
                'hasIdColumn' => $hasIdColumn,
                'hasStatusColumn' => $hasStatusColumn,
                'dataHsCode' => $dataHsCode,
                'stats' => $this->emptyStats(),
                'mode' => $request->query('mode'),
                'editData' => null,
            ]);
        }

        $query = DB::table($this->table)
            ->select([
                $this->selectAlias($columns['id'], 'id'),
                $this->selectAlias($columns['excel_id'], 'excel_id'),
                $this->selectAlias(
                    $columns['kode_kategori'],
                    'kode_kategori'
                ),
                $this->selectAlias(
                    $columns['kode_kelompok'],
                    'kode_kelompok'
                ),
                $this->selectAlias(
                    $columns['uraian_kelompok'],
                    'uraian_kelompok'
                ),
                $this->selectAlias(
                    $columns['kode_subkelompok'],
                    'kode_subkelompok'
                ),
                $this->selectAlias(
                    $columns['uraian_subkelompok'],
                    'uraian_subkelompok'
                ),
                $this->selectAlias(
                    $columns['hs_code'],
                    'hs_code'
                ),
                $this->selectAlias(
                    $columns['uraian_barang'],
                    'uraian_barang'
                ),
                $this->selectAlias(
                    $columns['status'],
                    'status'
                ),
                $this->selectAlias(
                    $columns['keterangan'],
                    'keterangan'
                ),
                $this->selectAlias(
                    $columns['created_at'],
                    'created_at'
                ),
                $this->selectAlias(
                    $columns['updated_at'],
                    'updated_at'
                ),
            ]);

        if ($request->filled('search')) {
            $search = '%' . strtolower(
                trim((string) $request->search)
            ) . '%';

            $searchColumns = array_filter([
                $columns['kode_kategori'],
                $columns['kode_kelompok'],
                $columns['uraian_kelompok'],
                $columns['kode_subkelompok'],
                $columns['uraian_subkelompok'],
                $columns['hs_code'],
                $columns['uraian_barang'],
                $columns['status'],
                $columns['keterangan'],
            ]);

            $query->where(function ($subQuery) use (
                $searchColumns,
                $search
            ) {
                foreach ($searchColumns as $column) {
                    $subQuery->orWhereRaw(
                        'LOWER(CAST('
                        . $this->quotedColumn($column)
                        . ' AS TEXT)) LIKE ?',
                        [$search]
                    );
                }
            });
        }

        if (
            $request->filled('status')
            && $hasStatusColumn
        ) {
            $query->whereRaw(
                'LOWER(TRIM(CAST('
                . $this->quotedColumn($columns['status'])
                . ' AS TEXT))) = ?',
                [
                    strtolower(
                        trim((string) $request->status)
                    ),
                ]
            );
        }

        if ($request->filled('kategori')) {
            $this->applyExactFilter(
                $query,
                $columns['kode_kategori'],
                $request->kategori
            );
        }

        if ($request->filled('kelompok')) {
            $this->applyExactFilter(
                $query,
                $columns['kode_kelompok'],
                $request->kelompok
            );
        }

        if ($request->filled('subkelompok')) {
            $this->applyExactFilter(
                $query,
                $columns['kode_subkelompok'],
                $request->subkelompok
            );
        }

        if ($columns['hs_code']) {
            $query->orderByRaw(
                $this->quotedColumn(
                    $columns['hs_code']
                ) . ' ASC'
            );
        } elseif ($columns['id']) {
            $query->orderBy($columns['id']);
        }

        $dataHsCode = $query
            ->paginate(10)
            ->withQueryString();

        $stats = $this->stats($columns);

        $kategoriOptions = $this->distinctOptions(
            $columns['kode_kategori']
        );

        $kelompokOptions = $this->distinctOptions(
            $columns['kode_kelompok'],
            $columns['kode_kategori'],
            $request->kategori
        );

        $subkelompokOptions = $this->distinctOptions(
            $columns['kode_subkelompok'],
            $columns['kode_kelompok'],
            $request->kelompok
        );

        $mode = $request->query('mode');
        $editData = null;

        if (
            $request->filled('edit')
            && $hasIdColumn
        ) {
            $editData = $this->findRow(
                $request->edit,
                $columns
            );

            $mode = 'edit';
        }

        return view('admin.hs-code', compact(
            'tableExists',
            'columnsReady',
            'hasIdColumn',
            'hasStatusColumn',
            'dataHsCode',
            'stats',
            'kategoriOptions',
            'kelompokOptions',
            'subkelompokOptions',
            'mode',
            'editData'
        ));
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable($this->table)) {
            return redirect()
                ->route('admin.hs-code.index')
                ->with(
                    'error',
                    'Tabel ' . $this->table . ' belum tersedia di Supabase.'
                );
        }

        $columns = $this->columnMap();

        if (
            ! $columns['hs_code']
            || ! $columns['uraian_barang']
        ) {
            return redirect()
                ->route('admin.hs-code.index')
                ->with(
                    'error',
                    'Kolom HS Code dan Uraian Barang belum sesuai.'
                );
        }

        $data = $request->validate(
            $this->rules($columns),
            $this->messages()
        );

        $this->ensureUniqueHsCode(
            $data['hs_code'],
            $columns
        );

        $insert = $this->payload(
            $data,
            $columns
        );

        if ($columns['created_at']) {
            $insert[$columns['created_at']] = now();
        }

        if ($columns['updated_at']) {
            $insert[$columns['updated_at']] = now();
        }

        DB::table($this->table)->insert($insert);

        return redirect()
            ->route('admin.hs-code.index')
            ->with(
                'success',
                'Data HS Code berhasil ditambahkan.'
            );
    }

    public function update(Request $request, $id)
    {
        $columns = $this->columnMap();

        if (! $columns['id']) {
            return redirect()
                ->route('admin.hs-code.index')
                ->with(
                    'error',
                    'Kolom id belum tersedia.'
                );
        }

        $data = $request->validate(
            $this->rules($columns),
            $this->messages()
        );

        $this->ensureUniqueHsCode(
            $data['hs_code'],
            $columns,
            $id
        );

        $update = $this->payload(
            $data,
            $columns
        );

        if ($columns['updated_at']) {
            $update[$columns['updated_at']] = now();
        }

        DB::table($this->table)
            ->where($columns['id'], $id)
            ->update($update);

        return redirect()
            ->route('admin.hs-code.index')
            ->with(
                'success',
                'Data HS Code berhasil diperbarui.'
            );
    }

    public function destroy($id)
    {
        $columns = $this->columnMap();

        if (! $columns['id']) {
            return redirect()
                ->route('admin.hs-code.index')
                ->with(
                    'error',
                    'Kolom id belum tersedia.'
                );
        }

        DB::table($this->table)
            ->where($columns['id'], $id)
            ->delete();

        return redirect()
            ->route('admin.hs-code.index')
            ->with(
                'success',
                'Data HS Code berhasil dihapus.'
            );
    }

    private function rules(array $columns): array
    {
        $rules = [
            'excel_id' => [
                'nullable',
                'integer',
            ],
            'kode_kategori' => [
                'nullable',
                'string',
                'max:80',
            ],
            'kode_kelompok' => [
                'nullable',
                'string',
                'max:80',
            ],
            'uraian_kelompok' => [
                'nullable',
                'string',
            ],
            'kode_subkelompok' => [
                'nullable',
                'string',
                'max:80',
            ],
            'uraian_subkelompok' => [
                'nullable',
                'string',
            ],
            'hs_code' => [
                'required',
                'string',
                'max:80',
            ],
            'uraian_barang' => [
                'required',
                'string',
            ],
            'keterangan' => [
                'nullable',
                'string',
            ],
        ];

        if ($columns['status']) {
            $rules['status'] = [
                'required',
                'in:Aktif,Nonaktif',
            ];
        }

        return $rules;
    }

    private function messages(): array
    {
        return [
            'excel_id.integer' =>
                'ID Excel harus berupa angka.',
            'hs_code.required' =>
                'HS Code wajib diisi.',
            'hs_code.max' =>
                'HS Code maksimal 80 karakter.',
            'uraian_barang.required' =>
                'Uraian barang wajib diisi.',
            'status.required' =>
                'Status wajib dipilih.',
            'status.in' =>
                'Status yang dipilih tidak valid.',
        ];
    }

    private function payload(
        array $data,
        array $columns
    ): array {
        $payload = [];

        $mapping = [
            'excel_id' => 'excel_id',
            'kode_kategori' => 'kode_kategori',
            'kode_kelompok' => 'kode_kelompok',
            'uraian_kelompok' => 'uraian_kelompok',
            'kode_subkelompok' => 'kode_subkelompok',
            'uraian_subkelompok' => 'uraian_subkelompok',
            'hs_code' => 'hs_code',
            'uraian_barang' => 'uraian_barang',
            'status' => 'status',
            'keterangan' => 'keterangan',
        ];

        foreach ($mapping as $input => $mapKey) {
            if (! $columns[$mapKey]) {
                continue;
            }

            $value = $data[$input] ?? null;

            if (is_string($value)) {
                $value = trim($value);

                if ($value === '') {
                    $value = null;
                }
            }

            $payload[$columns[$mapKey]] = $value;
        }

        if (
            $columns['status']
            && empty($payload[$columns['status']])
        ) {
            $payload[$columns['status']] = 'Aktif';
        }

        return $payload;
    }

    private function ensureUniqueHsCode(
        string $hsCode,
        array $columns,
        $ignoreId = null
    ): void {
        if (! $columns['hs_code']) {
            return;
        }

        $query = DB::table($this->table)
            ->whereRaw(
                'LOWER(TRIM(CAST('
                . $this->quotedColumn(
                    $columns['hs_code']
                )
                . ' AS TEXT))) = ?',
                [
                    strtolower(
                        trim($hsCode)
                    ),
                ]
            );

        if (
            $ignoreId !== null
            && $columns['id']
        ) {
            $query->where(
                $columns['id'],
                '!=',
                $ignoreId
            );
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'hs_code' =>
                    'HS Code sudah terdaftar.',
            ]);
        }
    }

    private function findRow(
        $id,
        array $columns
    ) {
        return DB::table($this->table)
            ->select([
                $this->selectAlias($columns['id'], 'id'),
                $this->selectAlias(
                    $columns['excel_id'],
                    'excel_id'
                ),
                $this->selectAlias(
                    $columns['kode_kategori'],
                    'kode_kategori'
                ),
                $this->selectAlias(
                    $columns['kode_kelompok'],
                    'kode_kelompok'
                ),
                $this->selectAlias(
                    $columns['uraian_kelompok'],
                    'uraian_kelompok'
                ),
                $this->selectAlias(
                    $columns['kode_subkelompok'],
                    'kode_subkelompok'
                ),
                $this->selectAlias(
                    $columns['uraian_subkelompok'],
                    'uraian_subkelompok'
                ),
                $this->selectAlias(
                    $columns['hs_code'],
                    'hs_code'
                ),
                $this->selectAlias(
                    $columns['uraian_barang'],
                    'uraian_barang'
                ),
                $this->selectAlias(
                    $columns['status'],
                    'status'
                ),
                $this->selectAlias(
                    $columns['keterangan'],
                    'keterangan'
                ),
                $this->selectAlias(
                    $columns['created_at'],
                    'created_at'
                ),
                $this->selectAlias(
                    $columns['updated_at'],
                    'updated_at'
                ),
            ])
            ->where($columns['id'], $id)
            ->firstOrFail();
    }

    private function stats(array $columns): array
    {
        $total = DB::table(
            $this->table
        )->count();

        $kategori = $this->distinctCount(
            $columns['kode_kategori']
        );

        $kelompok = $this->distinctCount(
            $columns['kode_kelompok']
        );

        $subkelompok = $this->distinctCount(
            $columns['kode_subkelompok']
        );

        return [
            [
                'label' => 'Total HS Code',
                'value' => $total,
                'color' => 'green',
                'icon' => 'fa-tags',
            ],
            [
                'label' => 'Kategori',
                'value' => $kategori,
                'color' => 'yellow',
                'icon' => 'fa-layer-group',
            ],
            [
                'label' => 'Kelompok',
                'value' => $kelompok,
                'color' => 'blue',
                'icon' => 'fa-table-list',
            ],
            [
                'label' => 'Subkelompok',
                'value' => $subkelompok,
                'color' => 'red',
                'icon' => 'fa-sitemap',
            ],
        ];
    }

    private function distinctCount(
        ?string $column
    ): int {
        if (! $column) {
            return 0;
        }

        return DB::table($this->table)
            ->whereNotNull($column)
            ->whereRaw(
                'TRIM(CAST('
                . $this->quotedColumn($column)
                . ' AS TEXT)) != ?',
                ['']
            )
            ->distinct()
            ->count($column);
    }

    private function emptyStats(): array
    {
        return [
            [
                'label' => 'Total HS Code',
                'value' => 0,
                'color' => 'green',
                'icon' => 'fa-tags',
            ],
            [
                'label' => 'Kategori',
                'value' => 0,
                'color' => 'yellow',
                'icon' => 'fa-layer-group',
            ],
            [
                'label' => 'Kelompok',
                'value' => 0,
                'color' => 'blue',
                'icon' => 'fa-table-list',
            ],
            [
                'label' => 'Subkelompok',
                'value' => 0,
                'color' => 'red',
                'icon' => 'fa-sitemap',
            ],
        ];
    }

    private function distinctOptions(
        ?string $column,
        ?string $parentColumn = null,
        $parentValue = null
    ) {
        if (! $column) {
            return collect();
        }

        $query = DB::table($this->table)
            ->select($column)
            ->whereNotNull($column)
            ->whereRaw(
                'TRIM(CAST('
                . $this->quotedColumn($column)
                . ' AS TEXT)) != ?',
                ['']
            );

        if (
            $parentColumn
            && $parentValue !== null
            && $parentValue !== ''
        ) {
            $query->where(
                $parentColumn,
                $parentValue
            );
        }

        return $query
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    private function applyExactFilter(
        $query,
        ?string $column,
        $value
    ): void {
        if (! $column) {
            return;
        }

        $query->whereRaw(
            'TRIM(CAST('
            . $this->quotedColumn($column)
            . ' AS TEXT)) = ?',
            [trim((string) $value)]
        );
    }

    private function columnMap(): array
    {
        return [
            'id' => $this->firstExistingColumn([
                'id',
            ]),
            'excel_id' => $this->firstExistingColumn([
                'excel_id',
                'id_excel',
            ]),
            'kode_kategori' => $this->firstExistingColumn([
                'kode_kategori',
                'kategori_kode',
                'Kode Kategori',
            ]),
            'kode_kelompok' => $this->firstExistingColumn([
                'kode_kelompok',
                'kelompok_kode',
                'Kode Kelompok',
            ]),
            'uraian_kelompok' => $this->firstExistingColumn([
                'uraian_kelompok',
                'kelompok_uraian',
                'Uraian Kelompok',
            ]),
            'kode_subkelompok' =>
                $this->firstExistingColumn([
                    'kode_subkelompok',
                    'subkelompok_kode',
                    'Kode Subkelompok',
                ]),
            'uraian_subkelompok' =>
                $this->firstExistingColumn([
                    'uraian_subkelompok',
                    'subkelompok_uraian',
                    'Uraian Subkelompok',
                ]),
            'hs_code' => $this->firstExistingColumn([
                'hs_code',
                'HS Code',
                'kode_hs',
                'Kode HS',
            ]),
            'uraian_barang' =>
                $this->firstExistingColumn([
                    'uraian_barang',
                    'Uraian Barang',
                    'uraian',
                    'deskripsi',
                ]),
            'status' => $this->firstExistingColumn([
                'status',
                'Status',
            ]),
            'keterangan' =>
                $this->firstExistingColumn([
                    'keterangan',
                    'Keterangan',
                ]),
            'created_at' =>
                $this->firstExistingColumn([
                    'created_at',
                ]),
            'updated_at' =>
                $this->firstExistingColumn([
                    'updated_at',
                ]),
        ];
    }

    private function firstExistingColumn(
        array $columns
    ): ?string {
        if (! Schema::hasTable($this->table)) {
            return null;
        }

        $existingColumns = Schema::getColumnListing(
            $this->table
        );

        foreach ($columns as $targetColumn) {
            foreach ($existingColumns as $existingColumn) {
                if (
                    strtolower($existingColumn)
                    === strtolower($targetColumn)
                ) {
                    return $existingColumn;
                }
            }
        }

        return null;
    }

    private function selectAlias(
        ?string $column,
        string $alias
    ) {
        if (! $column) {
            return DB::raw(
                'NULL AS "' . $alias . '"'
            );
        }

        return DB::raw(
            $this->quotedColumn($column)
            . ' AS "'
            . $alias
            . '"'
        );
    }

    private function quotedColumn(
        string $column
    ): string {
        return '"'
            . str_replace('"', '""', $column)
            . '"';
    }
}