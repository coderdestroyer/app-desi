@extends('layouts.admin')

@section('title', 'Profil Admin')

@section('content')
    @php
        $user = Auth::user();

        $avatarUrl = $user?->avatar
            ? asset('storage/' . $user->avatar)
            : 'https://ui-avatars.com/api/?name='
                . urlencode($user?->name ?? 'Admin')
                . '&background=E8F0FC&color=1E3A8A&size=256';
    @endphp

    <div class="min-h-screen bg-slate-50 px-6 py-8 md:px-8 lg:px-10">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">
                Profil Admin
            </h1>

            <p class="mt-1 text-slate-500">
                Kelola informasi profil dan foto Anda di sini.
            </p>
        </div>

        {{-- Alert berhasil --}}
        @if (session('success'))
            <div
                class="mb-6 flex items-center gap-3 rounded-lg border
                    border-green-200 bg-green-50 px-4 py-3 text-green-700"
            >
                <svg
                    class="h-5 w-5 flex-shrink-0 text-green-500"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <p class="text-sm font-medium">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        {{-- Alert error --}}
        @if (session('error'))
            <div
                class="mb-6 flex items-center gap-3 rounded-lg border
                    border-red-200 bg-red-50 px-4 py-3 text-red-700"
            >
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>

                <p class="text-sm font-medium">
                    {{ session('error') }}
                </p>
            </div>
        @endif

        {{-- Form profil --}}
        <form
            action="{{ route('admin.profile.update') }}"
            method="POST"
            enctype="multipart/form-data"
            class="overflow-hidden rounded-2xl border border-slate-100
                bg-white shadow-sm"
        >
            @csrf
            @method('PUT')

            <div class="p-6 md:p-8">
                <div class="flex flex-col gap-8 lg:flex-row">
                    {{-- Avatar --}}
                    <div
                        x-data="{ photoPreview: null }"
                        class="flex flex-shrink-0 flex-col items-center
                            lg:w-[180px]"
                    >
                        <input
                            type="file"
                            name="avatar"
                            id="avatar"
                            class="hidden"
                            x-ref="photo"
                            accept="image/png,image/jpeg,image/jpg,image/gif"
                            x-on:change="
                                const file = $refs.photo.files[0];

                                if (!file) {
                                    photoPreview = null;
                                    return;
                                }

                                const reader = new FileReader();

                                reader.onload = (event) => {
                                    photoPreview = event.target.result;
                                };

                                reader.readAsDataURL(file);
                            "
                        >

                        <button
                            type="button"
                            x-on:click="$refs.photo.click()"
                            class="group relative h-36 w-36 overflow-hidden
                                rounded-full border-4 border-white
                                bg-emerald-100 shadow-lg"
                        >
                            <img
                                x-show="!photoPreview"
                                src="{{ $avatarUrl }}"
                                alt="Foto profil"
                                class="h-full w-full object-cover"
                            >

                            <span
                                x-show="photoPreview"
                                x-cloak
                                class="block h-full w-full bg-cover
                                    bg-center bg-no-repeat"
                                x-bind:style="
                                    `background-image: url('${photoPreview}')`
                                "
                            ></span>

                            <span
                                class="absolute inset-0 flex items-center
                                    justify-center bg-black/40 opacity-0
                                    transition group-hover:opacity-100"
                            >
                                <i class="fa-solid fa-camera text-2xl text-white"></i>
                            </span>
                        </button>

                        <button
                            type="button"
                            x-on:click="$refs.photo.click()"
                            class="mt-5 rounded-lg bg-emerald-50 px-4 py-2
                                text-sm font-semibold text-emerald-600
                                transition hover:bg-emerald-100"
                        >
                            Ubah Foto
                        </button>

                        <p
                            class="mt-4 max-w-[165px] text-center text-xs
                                leading-relaxed text-slate-400"
                        >
                            Format JPG, PNG atau GIF. Maksimal 2MB.
                        </p>

                        @error('avatar')
                            <p class="mt-2 text-center text-xs font-semibold text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Form data --}}
                    <div class="min-w-0 flex-1">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                            <div>
                                <label
                                    for="name"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Nama Lengkap
                                </label>

                                <input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $user?->name) }}"
                                    required
                                    placeholder="Masukkan nama"
                                    class="w-full rounded-lg border border-slate-200
                                        px-4 py-2.5 text-slate-700 outline-none
                                        transition-all focus:border-emerald-500
                                        focus:ring-2 focus:ring-emerald-200"
                                >

                                @error('name')
                                    <p class="mt-1 text-xs font-semibold text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Email
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $user?->email) }}"
                                    required
                                    placeholder="Masukkan email"
                                    class="w-full rounded-lg border border-slate-200
                                        px-4 py-2.5 text-slate-700 outline-none
                                        transition-all focus:border-emerald-500
                                        focus:ring-2 focus:ring-emerald-200"
                                >

                                @error('email')
                                    <p class="mt-1 text-xs font-semibold text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="role"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Peran / Jabatan
                                </label>

                                <input
                                    id="role"
                                    type="text"
                                    value="{{ ucfirst($user?->role ?? 'Admin') }}"
                                    disabled
                                    class="w-full cursor-not-allowed rounded-lg
                                        border border-slate-200 bg-slate-50
                                        px-4 py-2.5 text-slate-500"
                                >
                            </div>

                            <div>
                                <label
                                    for="phone"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Nomor Telepon
                                </label>

                                <input
                                    id="phone"
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone', $user?->phone) }}"
                                    placeholder="Masukkan nomor"
                                    class="w-full rounded-lg border border-slate-200
                                        px-4 py-2.5 text-slate-700 outline-none
                                        transition-all focus:border-emerald-500
                                        focus:ring-2 focus:ring-emerald-200"
                                >

                                @error('phone')
                                    <p class="mt-1 text-xs font-semibold text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div
                            class="mt-6 flex justify-end gap-3 border-t
                                border-slate-100 pt-5"
                        >
                            <a
                                href="{{ route('admin.profile.index') }}"
                                class="rounded-lg bg-slate-100 px-5 py-2.5
                                    text-sm font-medium text-slate-600
                                    transition hover:bg-slate-200"
                            >
                                Batal
                            </a>

                            <button
                                type="submit"
                                class="rounded-lg bg-emerald-600 px-5 py-2.5
                                    text-sm font-medium text-white shadow-sm
                                    shadow-emerald-200 transition-all
                                    hover:bg-emerald-700"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection