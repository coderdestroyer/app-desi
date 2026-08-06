@extends('partials.layouts.operator')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Log Aktivitas Sistem</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau seluruh riwayat penambahan, perubahan, dan penghapusan data secara kronologis.</p>
    </div>
    <div>
        <a href="{{ route('operator.dashboard') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Semua Riwayat Aktivitas
        </h3>
        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 w-full md:w-auto">
            <form action="{{ route('operator.aktivitas') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <select name="month" class="text-sm border-slate-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 py-1.5 flex-1 md:w-auto">
                    <option value="">Semua Bulan</option>
                    @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $num => $name)
                        <option value="{{ $num }}" {{ (isset($filterMonth) && $filterMonth == $num) ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year" class="text-sm border-slate-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 py-1.5 flex-1 md:w-auto">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ (isset($filterYear) && $filterYear == $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors whitespace-nowrap">
                    Filter
                </button>
                @if(isset($filterMonth) || isset($filterYear))
                    <a href="{{ route('operator.aktivitas') }}" class="text-slate-500 hover:text-red-500 transition-colors p-1.5 rounded-lg bg-slate-100 hover:bg-red-50" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </form>
            <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full border border-emerald-200 whitespace-nowrap self-start md:self-auto">
                Total: {{ $paginatedLogs->total() }} Log
            </span>
        </div>
    </div>

    <div class="p-6 space-y-6">
        @forelse($paginatedLogs as $log)
            @php
                $iconClass = match(strtolower($log->action)) {
                    'login' => 'bg-green-100 text-green-600',
                    'logout' => 'bg-red-100 text-red-600',
                    'ditambah' => 'bg-green-100 text-green-600',
                    'diperbarui', 'diubah' => 'bg-emerald-100 text-emerald-600',
                    'diimpor' => 'bg-purple-100 text-purple-600',
                    'dihapus' => 'bg-red-100 text-red-600',
                    default => 'bg-slate-100 text-slate-600'
                };
                $iconSvg = match(strtolower($log->action)) {
                    'login' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />',
                    'logout' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />',
                    'ditambah' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />',
                    'diperbarui', 'diubah' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
                    'diimpor' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
                    'dihapus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />',
                    default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                };
            @endphp
            <!-- Activity Item -->
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 {{ $iconClass }} border border-white shadow-sm ring-4 ring-slate-50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $iconSvg !!}</svg>
                </div>
                <div class="flex-1 bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-sm font-semibold text-slate-800">{{ $log->desc }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-500">
                        <span class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $log->created_at->format('d M Y H:i') }}
                        </span>
                        <span class="flex items-center gap-1.5 bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm text-emerald-600">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Modul: {{ $log->module }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-500">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <p class="text-lg font-medium text-slate-700">Tidak ada riwayat aktivitas</p>
                <p class="text-sm mt-1">Sistem belum mencatat adanya perubahan data.</p>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination Component -->
    <x-pagination :paginator="$paginatedLogs" />
</div>
@endsection
