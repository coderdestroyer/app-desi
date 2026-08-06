@extends('layouts.admin')

@section('title', 'Data Pengguna')

@section('content')
    @php
        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $hasStatusColumn = Schema::hasColumn('users', 'status');
        $hasScopesTable = \App\Models\UserWilayahScope::ensureTableExists();

        $statStyles = [
            'green' => [
                'icon' => 'bg-emerald-600',
                'corner' => 'bg-emerald-50',
            ],
            'yellow' => [
                'icon' => 'bg-amber-500',
                'corner' => 'bg-amber-50',
            ],
            'blue' => [
                'icon' => 'bg-sky-600',
                'corner' => 'bg-sky-50',
            ],
            'red' => [
                'icon' => 'bg-red-500',
                'corner' => 'bg-red-50',
            ],
        ];

        $isModalOpen = in_array($mode, ['create', 'edit'], true);
        $isEdit = $mode === 'edit' && $editData;

        $editScope = (!empty($hasScopesTable) && $isEdit) ? $editData->wilayahScopes->first() : null;
        $initialScopeType = 'none';
        $initialProvId = '';
        $initialKabId = '';
        if ($editScope) {
            if ($editScope->kabupaten_id) {
                $initialScopeType = 'kabupaten';
                $initialKabId = $editScope->kabupaten_id;
                $initialProvId = $editScope->provinsi_id;
            } elseif ($editScope->provinsi_id) {
                $initialScopeType = 'provinsi';
                $initialProvId = $editScope->provinsi_id;
            }
        }
    @endphp

    <div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8 space-y-6" x-data="{
        isModalOpen: {{ (session('errors') || old('name') || $isModalOpen) ? 'true' : 'false' }},
        isEdit: {{ (old('_method') === 'PUT' || $isEdit) ? 'true' : 'false' }},
        userId: '{{ old('userId', $isEdit ? $editData->id : '') }}',
        userName: '{{ old('name', $isEdit ? $editData->name : '') }}',
        userEmail: '{{ old('email', $isEdit ? $editData->email : '') }}',
        userRole: '{{ old('role', $isEdit ? $editData->role : 'operator') }}',
        userStatus: '{{ old('status', $isEdit ? $editData->status : 'approved') }}',
        scopeType: '{{ old('scope_type', $initialScopeType) }}',
        provinsiId: '{{ old('provinsi_id', $initialProvId) }}',
        kabupatenId: '{{ old('kabupaten_id', $initialKabId) }}',
        formAction: '{{ old('_method') === 'PUT' || $isEdit ? route('admin.pengguna.update', old('userId', $isEdit ? $editData->id : 0)) : route('admin.pengguna.store') }}',
        isDeleteModalOpen: false,
        deleteActionUrl: '',
        deleteTargetName: '',

        openCreateModal() {
            this.isEdit = false;
            this.userId = '';
            this.userName = '';
            this.userEmail = '';
            this.userRole = 'operator';
            this.userStatus = 'approved';
            this.scopeType = 'none';
            this.provinsiId = '';
            this.kabupatenId = '';
            this.formAction = '{{ route('admin.pengguna.store') }}';
            this.isModalOpen = true;
        },

        openEditModal(user, scope) {
            this.isEdit = true;
            this.userId = user.id;
            this.userName = user.name;
            this.userEmail = user.email;
            this.userRole = user.role || 'operator';
            this.userStatus = user.status || 'approved';
            this.formAction = '{{ url('/admin/pengguna') }}/' + user.id;

            if (scope) {
                if (scope.kabupaten_id) {
                    this.scopeType = 'kabupaten';
                    this.kabupatenId = scope.kabupaten_id;
                    this.provinsiId = scope.provinsi_id || '';
                } else if (scope.provinsi_id) {
                    this.scopeType = 'provinsi';
                    this.provinsiId = scope.provinsi_id;
                    this.kabupatenId = '';
                } else {
                    this.scopeType = 'none';
                    this.provinsiId = '';
                    this.kabupatenId = '';
                }
            } else {
                this.scopeType = 'none';
                this.provinsiId = '';
                this.kabupatenId = '';
            }
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            if (window.location.search) {
                window.location.href = '{{ route('admin.pengguna.index') }}';
            }
        },

        openDeleteModal(url, name) {
            this.deleteActionUrl = url;
            this.deleteTargetName = name;
            this.isDeleteModalOpen = true;
        }
    }">

        {{-- Flash Notifications --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- HEADER HALAMAN --}}
        {{-- ========================================================= --}}

        <section
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-7 md:p-8 shadow-lg text-white flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="relative z-10 space-y-2">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-users text-[#FFD54F]"></i>
                    <span>Menu Admin</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Manajemen Pengguna
                </h1>
                <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                    Kelola akun yang terdaftar, role pengguna, status akun, dan akses pengguna dalam sistem.
                </p>
            </div>

            <div class="relative z-10">
                <button type="button" @click="openCreateModal()"
                    class="px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Tambah Pengguna</span>
                </button>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- STATISTIK --}}
        {{-- ========================================================= --}}

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    $style = $statStyles[$stat['color']] ?? $statStyles['green'];
                @endphp

                <article class="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                    <div class="absolute -mr-8 -mt-8 right-0 top-0 h-20 w-20 rounded-bl-full transition-transform duration-500 group-hover:scale-150 {{ $style['corner'] }}"></div>

                    <div class="relative z-10 flex items-center justify-between gap-4">
                        <div>
                            <p class="m-0 text-xs font-semibold text-slate-500">
                                {{ $stat['label'] }}
                            </p>
                            <p class="mb-0 mt-2 text-3xl font-black tracking-tight text-slate-800">
                                {{ number_format($stat['value'], 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl text-lg text-white shadow-sm {{ $style['icon'] }}">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- ========================================================= --}}
        {{-- FILTER --}}
        {{-- ========================================================= --}}

        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <form action="{{ route('admin.pengguna.index') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-[190px_190px_minmax(260px,1fr)_auto]">
                @if ($hasRoleColumn)
                    <div class="relative">
                        <select name="role" class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Semua Role</option>
                            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                            <option value="operator" @selected(request('role') === 'operator')>Operator</option>
                        </select>
                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                    </div>
                @endif

                @if ($hasStatusColumn)
                    <div class="relative">
                        <select name="status" class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Semua Status</option>
                            <option value="Aktif" @selected(strtolower(request('status', '')) === 'aktif')>Aktif</option>
                            <option value="Suspend" @selected(strtolower(request('status', '')) === 'suspend')>Suspend</option>
                        </select>
                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                    </div>
                @endif

                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, email, role, atau status..." class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <button type="submit" aria-label="Cari pengguna" class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        <i class="fa-solid fa-filter"></i>
                        Terapkan
                    </button>

                    <a href="{{ route('admin.pengguna.index') }}" title="Reset filter" class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </section>

        {{-- ========================================================= --}}
        {{-- TABEL --}}
        {{-- ========================================================= --}}

        <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm" style="opacity: 1 !important; transform: none !important;">
            <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Daftar Pengguna</h2>
                    <p class="mt-1 text-xs text-slate-500">Data akun yang terdaftar melalui sistem.</p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($pengguna->total(), 0, ',', '.') }} Data
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">No.</th>
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Wilayah Kerja (Scope)</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Terdaftar</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($pengguna as $item)
                            @php
                                $nomor = ($pengguna->currentPage() - 1) * $pengguna->perPage() + $loop->iteration;
                                $role = $hasRoleColumn ? strtolower(trim($item->role ?? 'user')) : 'user';
                                $status = $hasStatusColumn ? trim($item->status ?? 'Aktif') : 'Aktif';
                                $statusLower = strtolower($status);

                                $initials = collect(preg_split('/\s+/', trim($item->name)))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($word) => strtoupper(mb_substr($word, 0, 1)))
                                    ->implode('');

                                $roleBadge = match ($role) {
                                    'admin' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'operator' => 'border-sky-200 bg-sky-50 text-sky-700',
                                    default => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                };
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50/60">
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">
                                    {{ str_pad($nomor, 2, '0', STR_PAD_LEFT) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-400 text-sm font-bold text-white shadow-sm">
                                            {{ $initials ?: 'U' }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="m-0 truncate font-semibold text-slate-700">
                                                {{ $item->name }}
                                            </p>
                                            <p class="mb-0 mt-1 truncate text-xs text-slate-400">
                                                {{ $item->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $roleBadge }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $scope = (!empty($hasScopesTable) && $item->relationLoaded('wilayahScopes'))
                                            ? $item->wilayahScopes->first()
                                            : (!empty($hasScopesTable) ? $item->wilayahScopes->first() : null);
                                        $isPending = strtolower($item->status ?? '') === 'pending';
                                    @endphp
                                    @if ($role === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border border-amber-300 bg-amber-50 text-amber-800">
                                            <i class="fa-solid fa-earth-asia text-[10px]"></i> Global (Semua Wilayah)
                                        </span>
                                    @elseif ($scope)
                                        @if ($scope->kabupaten)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $isPending ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-sky-300 bg-sky-50 text-sky-800' }}" title="{{ $scope->kabupaten->nama_kabupaten }}">
                                                <i class="fa-solid {{ $isPending ? 'fa-hourglass-half' : 'fa-location-dot' }} text-[10px]"></i>
                                                {{ $isPending ? 'Pengajuan' : 'Scope' }} Kab: {{ $scope->kabupaten->nama_kabupaten }}
                                            </span>
                                        @elseif ($scope->provinsi)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $isPending ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-emerald-300 bg-emerald-50 text-emerald-800' }}" title="{{ $scope->provinsi->nama_provinsi }}">
                                                <i class="fa-solid {{ $isPending ? 'fa-hourglass-half' : 'fa-map' }} text-[10px]"></i>
                                                {{ $isPending ? 'Pengajuan' : 'Scope' }} Prov: {{ $scope->provinsi->nama_provinsi }} (+ Sub-Kab)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border border-slate-200 bg-slate-100 text-slate-500">
                                                Belum Set
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border border-slate-200 bg-slate-100 text-slate-500">
                                            Belum Set
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $badgeClass = match ($statusLower) {
                                            'approved', 'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'rejected' => 'border-red-200 bg-red-50 text-red-700',
                                            'nonactive', 'suspend' => 'border-slate-300 bg-slate-100 text-slate-700',
                                            default => 'border-slate-200 bg-slate-50 text-slate-600',
                                        };
                                        $dotClass = match ($statusLower) {
                                            'approved', 'aktif' => 'bg-emerald-500',
                                            'pending' => 'bg-amber-500',
                                            'rejected' => 'bg-red-500',
                                            'nonactive', 'suspend' => 'bg-slate-500',
                                            default => 'bg-slate-400',
                                        };
                                        $statusLabel = match ($statusLower) {
                                            'approved', 'aktif' => 'Approved',
                                            'pending' => 'Pending',
                                            'rejected' => 'Rejected',
                                            'nonactive', 'suspend' => 'Nonactive',
                                            default => ucfirst($statusLower),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">
                                    <div class="font-medium text-slate-600">
                                        {{ $item->created_at ? $item->created_at->translatedFormat('d M Y') : '-' }}
                                    </div>
                                    @if ($item->created_at)
                                        <div class="mt-1 text-xs text-slate-400">
                                            {{ $item->created_at->format('H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" title="Edit pengguna" @click="openEditModal({{ json_encode($item) }}, {{ json_encode($item->wilayahScopes->first()) }})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button type="button" title="Hapus pengguna" @click="openDeleteModal('{{ route('admin.pengguna.destroy', $item->id) }}', '{{ $item->name }}')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400">
                                            <i class="fa-solid fa-users-slash"></i>
                                        </div>
                                        <h3 class="mb-0 mt-4 text-sm font-semibold text-slate-700">Data pengguna tidak ditemukan</h3>
                                        <p class="mb-0 mt-1 text-xs leading-relaxed text-slate-400">Coba ubah filter atau kata pencarian yang digunakan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- PAGINATION --}}
        {{-- ========================================================= --}}

        @if ($pengguna->hasPages())
            <section class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <p class="m-0 text-sm text-slate-500">
                    Menampilkan
                    <span class="font-semibold text-slate-700">{{ $pengguna->firstItem() }}</span>
                    sampai
                    <span class="font-semibold text-slate-700">{{ $pengguna->lastItem() }}</span>
                    dari
                    <span class="font-semibold text-slate-700">{{ $pengguna->total() }}</span>
                    pengguna
                </p>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($pengguna->onFirstPage())
                        <span class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400">
                            <i class="fa-solid fa-chevron-left"></i> Prev
                        </span>
                    @else
                        <a href="{{ $pengguna->previousPageUrl() }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                            <i class="fa-solid fa-chevron-left"></i> Prev
                        </a>
                    @endif

                    @foreach ($pengguna->onEachSide(1)->links()->elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex h-9 min-w-9 items-center justify-center text-xs text-slate-400">...</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page === $pengguna->currentPage())
                                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-[#FFD54F] bg-[#FFD54F] px-3 text-xs font-bold text-emerald-900">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($pengguna->hasMorePages())
                        <a href="{{ $pengguna->nextPageUrl() }}" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </section>
        @endif

        <footer class="pb-1 pt-8 text-center text-xs text-slate-400">
            Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
        </footer>

        {{-- Include Dedicated entry.blade.php Form Modal --}}
        @include('admin.pengguna.entry')

        {{-- ============================================================= --}}
        {{-- MODAL HAPUS (AlpineJS-controlled) --}}
        {{-- ============================================================= --}}
        <template x-if="isDeleteModalOpen">
            <div class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @click.self="isDeleteModalOpen = false" @keydown.escape.window="isDeleteModalOpen = false">
                <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-2xl">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
                        <i class="fa-regular fa-trash-can"></i>
                    </div>

                    <h3 class="mb-0 mt-5 text-xl font-bold text-slate-800">Hapus pengguna?</h3>
                    <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
                        Pengguna <strong x-text="deleteTargetName" class="text-slate-700"></strong> akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan.
                    </p>

                    <form :action="deleteActionUrl" method="POST" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="isDeleteModalOpen = false" class="inline-flex h-11 min-w-32 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-700">
                            <i class="fa-regular fa-trash-can"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>
@endsection
