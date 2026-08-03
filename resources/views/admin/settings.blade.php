@extends('layouts.admin')

@section('title', 'Pengaturan Akun')

@section('content')
    @php
        $defaultTab = $errors->hasAny([
            'current_password',
            'new_password',
            'new_password_confirmation',
        ])
            ? 'keamanan'
            : request('tab', 'keamanan');
    @endphp

    <div
        x-data="{
            tab: @js($defaultTab),
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmationPassword: false
        }"
        class="min-h-screen bg-slate-50 px-6 py-8 md:px-8 lg:px-10"
    >
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">
                Pengaturan Akun
            </h1>

            <p class="mt-1 text-slate-500">
                Kelola preferensi dan keamanan akun admin Anda.
            </p>
        </div>

        {{-- Alert --}}
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

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            {{-- Sidebar tab --}}
            <div class="col-span-1 space-y-2">
                <button
                    type="button"
                    x-on:click="tab = 'keamanan'"
                    x-bind:class="
                        tab === 'keamanan'
                            ? 'bg-white border-emerald-600 text-emerald-700 shadow-sm'
                            : 'border-transparent text-slate-600 hover:border-slate-300 hover:bg-white'
                    "
                    class="block w-full rounded-r-lg border-l-4
                        px-4 py-3 text-left font-medium transition-colors"
                >
                    Keamanan & Password
                </button>

                <button
                    type="button"
                    x-on:click="tab = 'aktivitas'"
                    x-bind:class="
                        tab === 'aktivitas'
                            ? 'bg-white border-emerald-600 text-emerald-700 shadow-sm'
                            : 'border-transparent text-slate-600 hover:border-slate-300 hover:bg-white'
                    "
                    class="block w-full rounded-r-lg border-l-4
                        px-4 py-3 text-left font-medium transition-colors"
                >
                    Log Aktivitas
                </button>
            </div>

            {{-- Isi tab --}}
            <div class="col-span-1 space-y-6 lg:col-span-2">
                {{-- Tab keamanan --}}
                <div
                    x-show="tab === 'keamanan'"
                    x-cloak
                    class="space-y-6"
                >
                    {{-- Ubah password --}}
                    <form
                        action="{{ route('admin.settings.password') }}"
                        method="POST"
                        class="overflow-hidden rounded-2xl border
                            border-slate-100 bg-white shadow-sm"
                    >
                        @csrf
                        @method('PUT')

                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="font-semibold text-slate-800">
                                Ubah Password
                            </h2>
                        </div>

                        <div class="space-y-5 p-6">
                            <div>
                                <label
                                    for="current_password"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Password Saat Ini
                                </label>

                                <div class="relative">
                                    <input
                                        id="current_password"
                                        x-bind:type="
                                            showCurrentPassword
                                                ? 'text'
                                                : 'password'
                                        "
                                        name="current_password"
                                        required
                                        placeholder="••••••••"
                                        class="w-full rounded-lg border border-slate-200
                                            px-4 py-2.5 pr-12 outline-none
                                            transition-all focus:border-emerald-500
                                            focus:ring-2 focus:ring-emerald-200"
                                    >

                                    <button
                                        type="button"
                                        x-on:click="
                                            showCurrentPassword =
                                                !showCurrentPassword
                                        "
                                        class="absolute right-3 top-1/2
                                            -translate-y-1/2 text-slate-400
                                            transition hover:text-emerald-600"
                                    >
                                        <i
                                            class="fa-regular"
                                            x-bind:class="
                                                showCurrentPassword
                                                    ? 'fa-eye-slash'
                                                    : 'fa-eye'
                                            "
                                        ></i>
                                    </button>
                                </div>

                                @error('current_password')
                                    <p class="mt-1 text-xs font-semibold text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        for="new_password"
                                        class="mb-2 block text-sm font-medium text-slate-700"
                                    >
                                        Password Baru
                                    </label>

                                    <div class="relative">
                                        <input
                                            id="new_password"
                                            x-bind:type="
                                                showNewPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            name="new_password"
                                            required
                                            placeholder="Minimal 8 karakter"
                                            class="w-full rounded-lg border
                                                border-slate-200 px-4 py-2.5
                                                pr-12 outline-none transition-all
                                                focus:border-emerald-500
                                                focus:ring-2 focus:ring-emerald-200"
                                        >

                                        <button
                                            type="button"
                                            x-on:click="
                                                showNewPassword =
                                                    !showNewPassword
                                            "
                                            class="absolute right-3 top-1/2
                                                -translate-y-1/2 text-slate-400
                                                transition hover:text-emerald-600"
                                        >
                                            <i
                                                class="fa-regular"
                                                x-bind:class="
                                                    showNewPassword
                                                        ? 'fa-eye-slash'
                                                        : 'fa-eye'
                                                "
                                            ></i>
                                        </button>
                                    </div>

                                    @error('new_password')
                                        <p class="mt-1 text-xs font-semibold text-red-500">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="new_password_confirmation"
                                        class="mb-2 block text-sm font-medium text-slate-700"
                                    >
                                        Konfirmasi Password Baru
                                    </label>

                                    <div class="relative">
                                        <input
                                            id="new_password_confirmation"
                                            x-bind:type="
                                                showConfirmationPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            name="new_password_confirmation"
                                            required
                                            placeholder="Ketik ulang password baru"
                                            class="w-full rounded-lg border
                                                border-slate-200 px-4 py-2.5
                                                pr-12 outline-none transition-all
                                                focus:border-emerald-500
                                                focus:ring-2 focus:ring-emerald-200"
                                        >

                                        <button
                                            type="button"
                                            x-on:click="
                                                showConfirmationPassword =
                                                    !showConfirmationPassword
                                            "
                                            class="absolute right-3 top-1/2
                                                -translate-y-1/2 text-slate-400
                                                transition hover:text-emerald-600"
                                        >
                                            <i
                                                class="fa-regular"
                                                x-bind:class="
                                                    showConfirmationPassword
                                                        ? 'fa-eye-slash'
                                                        : 'fa-eye'
                                                "
                                            ></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button
                                    type="submit"
                                    class="rounded-lg bg-emerald-600
                                        px-5 py-2.5 text-sm font-medium
                                        text-white shadow-sm shadow-emerald-200
                                        transition-all hover:bg-emerald-700"
                                >
                                    Perbarui Password
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- 2FA --}}
                    <div
                        class="overflow-hidden rounded-2xl border
                            border-slate-100 bg-white shadow-sm"
                    >
                        <div class="border-b border-slate-100 px-6 py-4">
                            <h2 class="font-semibold text-slate-800">
                                Autentikasi Dua Faktor (2FA)
                            </h2>
                        </div>

                        <div
                            class="flex flex-col items-start justify-between
                                gap-6 p-6 md:flex-row md:items-center"
                        >
                            <div>
                                <h3
                                    class="flex flex-wrap items-center gap-2
                                        font-medium text-slate-800"
                                >
                                    Tingkatkan Keamanan Akun

                                    @if (Auth::user()?->two_factor_enabled)
                                        <span
                                            class="inline-flex items-center gap-1.5
                                                rounded-full border
                                                border-emerald-200 bg-emerald-50
                                                px-2.5 py-1 text-xs font-semibold
                                                text-emerald-700"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full
                                                    bg-emerald-500"
                                            ></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5
                                                rounded-full border
                                                border-slate-200 bg-slate-50
                                                px-2.5 py-1 text-xs font-semibold
                                                text-slate-500"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full
                                                    bg-slate-400"
                                            ></span>
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </h3>

                                <p
                                    class="mt-1 max-w-md text-sm
                                        leading-relaxed text-slate-500"
                                >
                                    Aktifkan Autentikasi Dua Langkah untuk
                                    lapisan keamanan ekstra saat login ke
                                    sistem admin.
                                </p>
                            </div>

                            <form
                                action="{{ route('admin.settings.2fa') }}"
                                method="POST"
                            >
                                @csrf
                                @method('PUT')

                                @if (Auth::user()?->two_factor_enabled)
                                    <button
                                        type="submit"
                                        class="whitespace-nowrap rounded-lg
                                            border border-red-200 bg-red-50
                                            px-5 py-2.5 text-sm font-medium
                                            text-red-700 transition-colors
                                            hover:bg-red-100"
                                    >
                                        Nonaktifkan 2FA
                                    </button>
                                @else
                                    <button
                                        type="submit"
                                        class="whitespace-nowrap rounded-lg
                                            border border-emerald-200
                                            bg-emerald-50 px-5 py-2.5
                                            text-sm font-medium text-emerald-700
                                            transition-colors hover:bg-emerald-100"
                                    >
                                        Aktifkan 2FA
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tab aktivitas --}}
                <div
                    x-show="tab === 'aktivitas'"
                    x-cloak
                    class="overflow-hidden rounded-2xl border
                        border-slate-100 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between
                            border-b border-slate-100 px-6 py-4"
                    >
                        <h2 class="font-semibold text-slate-800">
                            Riwayat Login & Logout
                        </h2>
                    </div>

                    <div
                        class="max-h-[600px] divide-y divide-slate-100
                            overflow-y-auto"
                    >
                        @forelse ($authLogs ?? [] as $log)
                            @php
                                $isLogin = strtolower(
                                    $log->action ?? ''
                                ) === 'login';
                            @endphp

                            <div
                                class="flex items-center gap-4 px-6 py-4
                                    transition-colors hover:bg-slate-50"
                            >
                                <div
                                    class="flex h-10 w-10 flex-shrink-0
                                        items-center justify-center rounded-full
                                        {{ $isLogin
                                            ? 'bg-emerald-100 text-emerald-600'
                                            : 'bg-rose-100 text-rose-600' }}"
                                >
                                    @if ($isLogin)
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                    @else
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $log->desc ?? $log->action }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ optional($log->created_at)
                                            ->translatedFormat(
                                                'd F Y \P\u\k\u\l H:i'
                                            ) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">
                                Belum ada riwayat aktivitas autentikasi.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection