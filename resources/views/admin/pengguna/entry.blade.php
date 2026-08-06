@php
    $editScope = (!empty($hasScopesTable) && $isEdit) ? $editData->wilayahScopes->first() : null;
@endphp

<div x-show="isModalOpen" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95" @keydown.escape.window="closeModal()"
    class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm">

    <div class="my-6 w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl"
        @click.outside="closeModal()">

        <header class="flex items-start justify-between gap-5 border-b border-slate-100 bg-slate-50/50 p-6">
            <div>
                <div
                    class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fa-solid" :class="isEdit ? 'fa-user-pen' : 'fa-user-plus'"></i>
                </div>
                <h2 class="m-0 text-xl font-bold text-slate-800" x-text="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'">
                </h2>
                <p class="mb-0 mt-1 text-sm text-slate-500"
                    x-text="isEdit ? 'Perbarui informasi akun pengguna.' : 'Tambahkan akun pengguna baru ke dalam sistem.'">
                </p>
            </div>

            <button type="button" @click="closeModal()"
                class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <form :action="formAction" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
            <input type="hidden" name="userId" :value="userId">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">
                        Nama Pengguna
                        <span class="text-red-500">*</span>
                    </label>
                    <input id="name" type="text" name="name" x-model="userName" required autocomplete="name"
                        placeholder="Masukkan nama pengguna"
                        class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    @error('name')
                        <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                        Email
                        <span class="text-red-500">*</span>
                    </label>
                    <input id="email" type="email" name="email" x-model="userEmail" required autocomplete="email"
                        placeholder="nama@email.com"
                        class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    @error('email')
                        <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if ($hasRoleColumn)
                    <div>
                        <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">
                            Role
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="role" name="role" x-model="userRole" required
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                        </select>
                        @error('role')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($hasStatusColumn)
                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">
                            Status
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" x-model="userStatus" required
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="approved">Approved (Disetujui / Aktif)</option>
                            <option value="pending">Pending (Menunggu Verifikasi)</option>
                            <option value="rejected">Rejected (Ditolak)</option>
                            <option value="nonactive">Nonactive (Non-Aktif)</option>
                        </select>
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
                            <select name="scope_type" x-model="scopeType"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="none">-- Belum Dialokasikan --</option>
                                <option value="provinsi">Tingkat Provinsi (+ Sub-Kabupaten)</option>
                                <option value="kabupaten">Tingkat Kabupaten / Kota Spesifik</option>
                            </select>
                        </div>

                        <div x-show="scopeType === 'provinsi' || scopeType === 'kabupaten'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Provinsi Scope</label>
                            <select name="provinsi_id" x-model="provinsiId"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach($provinsis ?? [] as $prov)
                                    <option value="{{ $prov->provinsi_id }}">
                                        {{ $prov->nama_provinsi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="scopeType === 'kabupaten'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kabupaten / Kota
                                Scope</label>
                            <select name="kabupaten_id" x-model="kabupatenId"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">-- Pilih Kabupaten / Kota --</option>
                                @foreach($kabupatens ?? [] as $kab)
                                    <option value="{{ $kab->kab_id }}">
                                        {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                        <span x-text="isEdit ? 'Password Baru' : 'Password'"></span>
                        <span class="text-red-500" x-show="!isEdit">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" autocomplete="new-password"
                            :placeholder="isEdit ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter'"
                            :required="!isEdit"
                            class="h-11 w-full rounded-xl border border-slate-200 px-4 pr-11 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <button type="button" data-toggle-password="password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mb-0 mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">
                        Konfirmasi Password
                        <span class="text-red-500" x-show="!isEdit">*</span>
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                            autocomplete="new-password" placeholder="Ulangi password" :required="!isEdit"
                            class="h-11 w-full rounded-xl border border-slate-200 px-4 pr-11 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <button type="button" data-toggle-password="password_confirmation"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-emerald-600">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">
                <button type="button" @click="closeModal()"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Pengguna'"></span>
                </button>
            </div>
        </form>
    </div>
</div>