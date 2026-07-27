@extends('partials.layouts.operator')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#D4A017] via-[#FFD54F] to-[#001E6C] rounded-2xl p-8 text-white shadow-xl relative overflow-hidden mb-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold mb-3 backdrop-blur-sm">
                💼 Modul Peluang Investasi (IPRO Engine)
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight mb-2">Dokumen Kelayakan Proyek IPRO</h1>
            <p class="text-white/90 text-sm max-w-xl">
                Kelola pencatatan proyek *Ready to Offer*, hitung estimasi biaya modal CAPEX, proyeksi Laba Rugi (P&L), serta analisis Arus Kas secara otomatis.
            </p>
        </div>
        <div>
            <a href="{{ route('operator.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/30 text-xs font-semibold inline-flex items-center gap-2 transition-all">
                ← Kembali ke Pemilihan Dashboard
            </a>
        </div>
    </div>

    <!-- Quick Placeholder Banner -->
    <div class="bg-white rounded-2xl border border-[#252e27] p-8 text-center shadow-md">
        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-[#D4A017] flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-[#17201C] mb-2">Area Kerja Dokumen IPRO Siap Dikembangkan</h3>
        <p class="text-[#667069] text-sm max-w-md mx-auto mb-6">
            Di area ini Operator dapat menginput proyek investasi baru, menentukan estimasi CAPEX, serta memproyeksikan P&L & Cashflow.
        </p>
        <div class="inline-flex gap-3">
            <a href="{{ route('operator.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#145239] text-white text-sm font-semibold hover:bg-[#0B5D3D] transition-colors">
                Kembali ke Selection Screen
            </a>
        </div>
    </div>
</div>
@endsection
