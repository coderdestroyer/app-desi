@extends('partials.layouts.operator')

@section('title', 'Pengaturan Akun')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Pengaturan Akun</h1>
        <p class="text-slate-500 mt-1">Kelola preferensi dan keamanan akun operator Anda.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Settings Form (Left) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ubah Password -->
            <form action="{{ route('operator.settings.password') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                @csrf
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-800">Ubah Password</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700">Password Saat Ini</label>
                        <input type="password" name="current_password" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none" placeholder="••••••••" required>
                        @error('current_password')
                            <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Password Baru</label>
                            <input type="password" name="new_password" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none" placeholder="Minimal 8 karakter" required>
                            @error('new_password')
                                <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none" placeholder="Ketik ulang password baru" required>
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-200 transition-all">Perbarui Password</button>
                    </div>
                </div>
            </form>

            <!-- Autentikasi Dua Faktor (2FA) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="font-semibold text-slate-800">Autentikasi Dua Faktor (2FA)</h2>
                </div>
                <div class="p-6 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                    <div>
                        <h3 class="font-medium text-slate-800 flex items-center gap-2">
                            Tingkatkan Keamanan Akun
                            @if(Auth::user()->two_factor_enabled)
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-500 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Tidak Aktif
                                </span>
                            @endif
                        </h3>
                        <p class="text-sm text-slate-500 mt-1 max-w-md">Aktifkan Autentikasi Dua Langkah untuk lapisan keamanan ekstra saat login ke sistem operator.</p>
                    </div>
                    <form action="{{ route('operator.settings.2fa') }}" method="POST">
                        @csrf
                        @if(Auth::user()->two_factor_enabled)
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors whitespace-nowrap">
                                Nonaktifkan 2FA
                            </button>
                        @else
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors whitespace-nowrap">
                                Aktifkan 2FA
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Log Aktivitas Sidebar (Right) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
                <div class="border-b border-slate-100 px-6 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-slate-800">Riwayat Login & Logout</h2>
                </div>
                <div class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">
                    @forelse($authLogs as $log)
                        <div class="p-4 px-6 hover:bg-slate-50 transition-colors flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $log->action === 'Login' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                @if($log->action === 'Login')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $log->desc }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $log->created_at->translatedFormat('d F Y \P\u\k\u\l H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            Belum ada riwayat aktivitas autentikasi.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
