@extends('partials.layouts.operator')

@section('title', 'Detail PDB Nasional Tahun ' . $tahun)

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="pdbDetailData()">

    {{-- Breadcrumb & Action Bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('operator.pdb-nasional.index') }}" class="ml-12 lg:ml-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-[#CFE3D5] text-xs sm:text-sm font-bold text-[#145239] hover:bg-[#EEF8F2] transition-colors shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Data PDB Nasional</span>
        </a>
    </div>

    {{-- Banner Header --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-10 shadow-xl text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="space-y-2 sm:space-y-3 relative z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-800/60 border border-sky-700/60 text-sky-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-globe text-[#FFD54F]"></i>
                <span>Detail Referensi Makroekonomi Nasional (Read Only)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight">
                PDB Nasional &bull; Tahun {{ $tahun }}
            </h1>
            <p class="text-emerald-100/90 text-xs sm:text-sm md:text-base max-w-2xl leading-relaxed">
                Rincian nilai PDB Nasional per 17 Sektor Lapangan Usaha BPS (dalam <strong>Rp Juta</strong>) untuk acuan perhitungan Analisis Makroekonomi.
            </p>
        </div>

        <div class="bg-white/15 border border-white/20 backdrop-blur-md p-4 sm:p-5 rounded-2xl text-left md:text-right shrink-0 relative z-10 shadow-inner w-full md:w-auto">
            <span class="block text-xs uppercase tracking-wider text-emerald-200 font-bold mb-1">Total PDB Nasional</span>
            <span class="text-2xl sm:text-3xl font-mono font-black text-[#FFD54F]">
                Rp <span x-text="grandTotal">0,00</span>
            </span>
        </div>
    </section>

    {{-- Detail Grid Card --}}
    <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-5 sm:p-6 md:p-8 space-y-6">

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3 text-base md:text-lg font-bold text-slate-800">
                <div class="w-9 h-9 rounded-xl bg-[#EEF8F2] text-[#145239] flex items-center justify-center font-bold border border-[#CFE3D5] shrink-0">
                    <i class="fa-solid fa-list-ol"></i>
                </div>
                <span>Nilai PDB Nasional Menurut 17 Sektor Lapangan Usaha BPS</span>
            </div>
            <span class="text-xs text-slate-600 bg-slate-100 px-3 py-1 rounded-full font-mono font-bold border border-slate-200 shrink-0">
                17 Sektor BPS
            </span>
        </div>

        {{-- 17 Sectors Display Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
            @foreach($sektors as $sek)
                <div class="p-4 rounded-2xl border border-slate-200 bg-[#F8FAFC] flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3.5 shadow-2xs group">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <span class="w-8 h-8 rounded-xl bg-[#E7F2EB] text-[#145239] font-black text-sm flex items-center justify-center shrink-0 border border-[#CFE3D5]">
                            {{ $loop->iteration }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="block text-sm font-bold text-slate-800 leading-snug truncate" title="{{ $sek->nama_sektor }}">
                                {{ $sek->nama_sektor }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono">Kode Sektor: {{ $sek->sektor_id }}</span>
                        </div>
                    </div>

                    <div class="w-full sm:w-52 md:w-56 shrink-0 relative">
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 select-none">Rp</span>
                            <input type="text"
                                readonly
                                disabled
                                value="{{ isset($existingValues[$sek->sektor_id]) ? number_format($existingValues[$sek->sektor_id], 2, ',', '.') : '0,00' }}"
                                class="w-full h-11 rounded-xl border border-slate-200 bg-slate-100 text-slate-700 text-sm md:text-base font-mono text-right pl-9 pr-3 font-bold outline-none cursor-not-allowed select-all shadow-xs">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Action Buttons Footer --}}
        <div class="pt-6 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('operator.pdb-nasional.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-colors flex justify-center items-center gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali ke Data PDB Nasional</span>
            </a>
            <div class="text-xs text-slate-400 font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-lock text-slate-400"></i>
                <span>Mode Lihat Data (Read Only)</span>
            </div>
        </div>
    </div>
</div>

<script>
function pdbDetailData() {
    return {
        grandTotal: '0,00',
        init() {
            const values = @json($existingValues);
            let sum = 0;
            Object.values(values).forEach(val => {
                const num = parseFloat(val);
                if (!isNaN(num)) {
                    sum += num;
                }
            });
            this.grandTotal = new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(sum);
        }
    }
}
</script>
@endsection
