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
        showPassword: false,
        showPasswordConfirmation: false,

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
            this.showPassword = false;
            this.showPasswordConfirmation = false;
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
            this.showPassword = false;
            this.showPasswordConfirmation = false;

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
            <form action="{{ route('admin.pengguna.index') }}" method="GET" data-live-filter data-no-loader class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-[190px_190px_minmax(260px,1fr)_auto]">
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
                            <option value="approved" @selected(strtolower(request('status', '')) === 'approved')>Approved</option>
                            <option value="pending" @selected(strtolower(request('status', '')) === 'pending')>Pending</option>
                            <option value="rejected" @selected(strtolower(request('status', '')) === 'rejected')>Rejected</option>
                            <option value="nonactive" @selected(strtolower(request('status', '')) === 'nonactive')>Nonactive</option>
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

                <div class="flex items-center justify-end">
                    <a href="{{ route('admin.pengguna.index') }}" title="Reset filter" class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </section>

        {{-- ========================================================= --}}
        {{-- TABEL --}}
        {{-- ========================================================= --}}

        <section id="tableContainer" class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-opacity duration-200">
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

        {{-- Dedicated Form Modal --}}
        <template x-teleport="body">
            <div x-show="isModalOpen" x-cloak x-transition @keydown.escape.window="closeModal()"
                class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">

                <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-emerald-100 relative" @click.outside="closeModal()">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold">
                                <i class="fa-solid" :class="isEdit ? 'fa-user-pen' : 'fa-user-plus'"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base" x-text="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'"></h3>
                                <p class="text-xs text-slate-500" x-text="isEdit ? 'Perbarui informasi akun pengguna.' : 'Tambahkan akun pengguna baru ke dalam sistem.'"></p>
                            </div>
                        </div>

                        <button type="button" @click="closeModal()"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="pt-6">
                        @csrf
                        <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
                        <input type="hidden" name="userId" :value="userId">

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                            <div>
                                <label for="name" class="mb-1 block text-sm font-semibold text-slate-700">
                                    Nama Pengguna
                                    <span class="text-red-500">*</span>
                                </label>
                                <input id="name" type="text" name="name" x-model="userName" required autocomplete="name"
                                    placeholder="Masukkan nama pengguna"
                                    class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">
                                @error('name')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="mb-1 block text-sm font-semibold text-slate-700">
                                    Email
                                    <span class="text-red-500">*</span>
                                </label>
                                <input id="email" type="email" name="email" x-model="userEmail" required autocomplete="email"
                                    placeholder="nama@email.com"
                                    class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">
                                @error('email')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($hasRoleColumn)
                                <div>
                                    <label for="role" class="mb-1 block text-sm font-semibold text-slate-700">
                                        Role
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div x-data="{ open: false }" class="relative" :class="open ? 'z-50' : 'z-10'">
                                        <input type="hidden" id="role" name="role" :value="userRole" required>
                                        <div @click="open = !open"
                                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-all shadow-2xs">
                                            <span x-text="userRole === 'admin' ? 'Admin' : 'Operator'" class="text-slate-800 font-medium"></span>
                                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                        </div>
                                        <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                            class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-1 space-y-0.5 text-xs">
                                            <div @click="userRole = 'admin'; open = false"
                                                :class="userRole === 'admin' ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span>Admin</span>
                                                <i x-show="userRole === 'admin'" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                            <div @click="userRole = 'operator'; open = false"
                                                :class="userRole === 'operator' ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span>Operator</span>
                                                <i x-show="userRole === 'operator'" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                        </div>
                                    </div>
                                    @error('role')
                                        <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            @if ($hasStatusColumn)
                                <div>
                                    <label for="status" class="mb-1 block text-sm font-semibold text-slate-700">
                                        Status
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div x-data="{
                                        open: false,
                                        get label() {
                                            switch (userStatus) {
                                                case 'approved': return 'Approved (Disetujui / Aktif)';
                                                case 'pending': return 'Pending (Menunggu Verifikasi)';
                                                case 'rejected': return 'Rejected (Ditolak)';
                                                case 'nonactive': return 'Nonactive (Non-Aktif)';
                                                default: return 'Pilih Status...';
                                            }
                                        }
                                    }" class="relative" :class="open ? 'z-50' : 'z-10'">
                                        <input type="hidden" id="status" name="status" :value="userStatus" required>
                                        <div @click="open = !open"
                                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-all shadow-2xs">
                                            <span x-text="label" class="text-slate-800 font-medium"></span>
                                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                        </div>
                                        <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                            class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-1 space-y-0.5 text-xs">
                                            <template x-for="opt in [
                                                {id: 'approved', name: 'Approved (Disetujui / Aktif)'},
                                                {id: 'pending', name: 'Pending (Menunggu Verifikasi)'},
                                                {id: 'rejected', name: 'Rejected (Ditolak)'},
                                                {id: 'nonactive', name: 'Nonactive (Non-Aktif)'}
                                            ]" :key="opt.id">
                                                <div @click="userStatus = opt.id; open = false"
                                                    :class="userStatus === opt.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                    class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                    <span x-text="opt.name"></span>
                                                    <i x-show="userStatus === opt.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    @error('status')
                                        <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            {{-- REGIONAL SCOPE ASSIGNMENT --}}
                            <div x-show="userRole === 'operator'"
                                class="md:col-span-2 p-4 rounded-2xl bg-[#EEF8F2] border border-[#CFE3D5] space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-[#1E5E3F] uppercase tracking-wider">
                                        Alokasi Wilayah Kerja Operator (Regional Scope Authorization)
                                    </label>
                                </div>

                                @if ($editScope)
                                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-center justify-between"
                                        x-show="isEdit">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-bell-concierge text-amber-600 text-sm"></i>
                                            <div>
                                                <span class="font-bold">Pengajuan Wilayah Saat Registrasi:</span>
                                                @if ($editScope->kabupaten)
                                                    <span
                                                        class="font-bold text-sky-900 bg-white px-2 py-0.5 rounded ml-1 border border-sky-200">
                                                        📍 Kab/Kota: {{ $editScope->kabupaten->nama_kabupaten }}
                                                    </span>
                                                @elseif ($editScope->provinsi)
                                                    <span
                                                        class="font-bold text-emerald-900 bg-white px-2 py-0.5 rounded ml-1 border border-emerald-200">
                                                        🗺️ Provinsi: {{ $editScope->provinsi->nama_provinsi }} (+ Sub-Kab)
                                                    </span>
                                                @else
                                                    <span class="text-slate-500 ml-1">(Belum Memilih Scope)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <p class="text-xs text-slate-600">
                                    Cek & sesuaikan alokasi wilayah kerja data PDRB yang diberikan kepada Operator ini:
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Wilayah Scope</label>
                                        <div x-data="{
                                            open: false,
                                            get label() {
                                                switch (scopeType) {
                                                    case 'none': return '-- Belum Dialokasikan --';
                                                    case 'provinsi': return 'Tingkat Provinsi (+ Sub-Kabupaten)';
                                                    case 'kabupaten': return 'Tingkat Kabupaten / Kota Spesifik';
                                                    default: return '-- Belum Dialokasikan --';
                                                }
                                            }
                                        }" class="relative" :class="open ? 'z-50' : 'z-10'">
                                            <input type="hidden" name="scope_type" :value="scopeType">
                                            <div @click="open = !open"
                                                class="w-full h-11 px-3 py-2.5 rounded-xl border border-[#CFE3D5] bg-white text-xs flex items-center justify-between cursor-pointer hover:border-[#145239] transition-all shadow-2xs">
                                                <span x-text="label" class="text-slate-800 font-medium"></span>
                                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                            </div>
                                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-1 space-y-0.5 text-xs">
                                                <template x-for="opt in [
                                                    {id: 'none', name: '-- Belum Dialokasikan --'},
                                                    {id: 'provinsi', name: 'Tingkat Provinsi (+ Sub-Kabupaten)'},
                                                    {id: 'kabupaten', name: 'Tingkat Kabupaten / Kota Spesifik'}
                                                ]" :key="opt.id">
                                                    <div @click="scopeType = opt.id; open = false"
                                                        :class="scopeType === opt.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                        <span x-text="opt.name"></span>
                                                        <i x-show="scopeType === opt.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="scopeType === 'provinsi' || scopeType === 'kabupaten'">
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Provinsi Scope</label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            items: [
                                                @foreach($provinsis ?? [] as $prov)
                                                    { id: '{{ $prov->provinsi_id }}', name: '{{ addslashes($prov->nama_provinsi) }}' },
                                                @endforeach
                                            ],
                                            get filteredItems() {
                                                if (!this.search) return this.items;
                                                const q = this.search.toLowerCase();
                                                return this.items.filter(i => i.name.toLowerCase().includes(q));
                                            },
                                            get selectedName() {
                                                const matched = this.items.find(i => i.id == provinsiId);
                                                return matched ? matched.name : '-- Pilih Provinsi --';
                                            }
                                        }" class="relative" :class="open ? 'z-50' : 'z-10'">
                                            <input type="hidden" name="provinsi_id" :value="provinsiId">
                                            <div @click="open = !open; if(open) $nextTick(() => $refs.provSearchInput.focus())"
                                                class="w-full h-11 px-3 py-2.5 rounded-xl border border-[#CFE3D5] bg-white text-xs flex items-center justify-between cursor-pointer hover:border-[#145239] transition-all shadow-2xs">
                                                <span x-text="selectedName" :class="provinsiId ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                            </div>
                                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2 text-xs">
                                                <div class="relative">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                                    <input type="text" x-model="search" x-ref="provSearchInput" placeholder="Cari provinsi..."
                                                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                                </div>
                                                <div class="max-h-48 overflow-y-auto space-y-0.5">
                                                    <div @click="provinsiId = ''; open = false; search = ''"
                                                        :class="!provinsiId ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                        <span>-- Pilih Provinsi --</span>
                                                        <i x-show="!provinsiId" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                    </div>
                                                    <template x-for="item in filteredItems" :key="item.id">
                                                        <div @click="provinsiId = item.id; open = false; search = ''"
                                                            :class="provinsiId == item.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                            class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                            <span x-text="item.name"></span>
                                                            <i x-show="provinsiId == item.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="scopeType === 'kabupaten'">
                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kabupaten / Kota Scope</label>
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            items: [
                                                @foreach($kabupatens ?? [] as $kab)
                                                    { id: '{{ $kab->kab_id }}', name: '{{ addslashes($kab->nama_kabupaten) }}' },
                                                @endforeach
                                            ],
                                            get filteredItems() {
                                                if (!this.search) return this.items;
                                                const q = this.search.toLowerCase();
                                                return this.items.filter(i => i.name.toLowerCase().includes(q));
                                            },
                                            get selectedName() {
                                                const matched = this.items.find(i => i.id == kabupatenId);
                                                return matched ? matched.name : '-- Pilih Kabupaten / Kota --';
                                            }
                                        }" class="relative" :class="open ? 'z-50' : 'z-10'">
                                            <input type="hidden" name="kabupaten_id" :value="kabupatenId">
                                            <div @click="open = !open; if(open) $nextTick(() => $refs.kabSearchInput.focus())"
                                                class="w-full h-11 px-3 py-2.5 rounded-xl border border-[#CFE3D5] bg-white text-xs flex items-center justify-between cursor-pointer hover:border-[#145239] transition-all shadow-2xs">
                                                <span x-text="selectedName" :class="kabupatenId ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                            </div>
                                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2 text-xs">
                                                <div class="relative">
                                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                                    <input type="text" x-model="search" x-ref="kabSearchInput" placeholder="Cari kabupaten/kota..."
                                                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                                </div>
                                                <div class="max-h-48 overflow-y-auto space-y-0.5">
                                                    <div @click="kabupatenId = ''; open = false; search = ''"
                                                        :class="!kabupatenId ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                        <span>-- Pilih Kabupaten / Kota --</span>
                                                        <i x-show="!kabupatenId" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                    </div>
                                                    <template x-for="item in filteredItems" :key="item.id">
                                                        <div @click="kabupatenId = item.id; open = false; search = ''"
                                                            :class="kabupatenId == item.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                            class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                            <span x-text="item.name"></span>
                                                            <i x-show="kabupatenId == item.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="password" class="mb-1 block text-sm font-semibold text-slate-700">
                                    <span x-text="isEdit ? 'Password Baru' : 'Password'"></span>
                                    <span class="text-red-500" x-show="!isEdit">*</span>
                                </label>
                                <div class="relative">
                                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" autocomplete="new-password"
                                        :placeholder="isEdit ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter'"
                                        :required="!isEdit"
                                        class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 pr-11 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 animate-none">
                                        <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-slate-700">
                                    Konfirmasi Password
                                    <span class="text-red-500" x-show="!isEdit">*</span>
                                </label>
                                <div class="relative">
                                    <input id="password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation"
                                        autocomplete="new-password" placeholder="Ulangi password" :required="!isEdit"
                                        class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 pr-11 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">
                                    <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600 animate-none">
                                        <i :class="showPasswordConfirmation ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-7 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">
                            <button type="button" @click="closeModal()"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Pengguna'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ============================================================= --}}
        {{-- MODAL HAPUS (AlpineJS-controlled) --}}
        {{-- ============================================================= --}}
        <template x-teleport="body">
            <div x-show="isDeleteModalOpen" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm" @click.self="isDeleteModalOpen = false" @keydown.escape.window="isDeleteModalOpen = false">
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
