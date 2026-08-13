@extends('partials.layouts.operator')

@section('title', 'Detail Nilai PDRB ' . $kabupaten->nama_kabupaten . ' Tahun ' . $tahun)

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="pdrbDetailData()">

    {{-- Breadcrumb & Action Bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('operator.pdrb.index', ['tab' => 'all']) }}" class="ml-12 lg:ml-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-[#CFE3D5] text-xs sm:text-sm font-bold text-[#145239] hover:bg-[#EEF8F2] transition-colors shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Lihat PDRB Daerah</span>
        </a>
    </div>

    {{-- Banner Header --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-10 shadow-xl text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="space-y-2 sm:space-y-3 relative z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-800/60 border border-sky-700/60 text-sky-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-eye text-[#FFD54F]"></i>
                <span>Detail Nilai Sektor PDRB Kab/Kota (Read Only)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight">
                {{ $kabupaten->nama_kabupaten }} &bull; Tahun {{ $tahun }}
            </h1>
            <p class="text-emerald-100/90 text-xs sm:text-sm md:text-base max-w-2xl leading-relaxed">
                Melihat rincian nilai PDRB untuk 17 Sektor Lapangan Usaha BPS di Kabupaten/Kota ini.
            </p>
        </div>

        <div class="bg-white/15 border border-white/20 backdrop-blur-md p-4 sm:p-5 rounded-2xl text-left md:text-right shrink-0 relative z-10 shadow-inner w-full md:w-auto">
            <span class="block text-xs uppercase tracking-wider text-emerald-200 font-bold mb-1">Total PDRB</span>
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
                <span>Rincian Nilai 17 Sektor Lapangan Usaha BPS</span>
            </div>
            <span class="text-xs text-slate-600 bg-slate-100 px-3 py-1 rounded-full font-mono font-bold border border-slate-200 shrink-0">
                17 Sektor BPS
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
            @foreach($sektors as $index => $sek)
                @php
                    $val = $existingValues[$sek->sektor_id] ?? 0;
                @endphp
                <div class="p-4 rounded-xl border border-slate-200/80 hover:border-[#145239]/40 bg-slate-50/50 hover:bg-white transition-all space-y-2 group shadow-2xs">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-2.5">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-[#145239]/10 text-[#145239] text-xs font-bold shrink-0 mt-0.5">
                                {{ $sek->sektor_id }}
                            </span>
                            <h4 class="text-xs md:text-sm font-semibold text-slate-800 group-hover:text-[#145239] transition-colors leading-snug">
                                {{ $sek->nama_sektor }}
                            </h4>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-slate-400 font-medium">Nilai PDRB</span>
                        <div class="font-mono font-bold text-sm md:text-base text-slate-900 bg-white px-3 py-1 rounded-lg border border-slate-200 shadow-2xs">
                            Rp {{ number_format((float)$val, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Action Buttons Footer --}}
        <div class="pt-6 border-t border-slate-100 flex items-center justify-start">
            <a href="{{ route('operator.pdrb.index', ['tab' => 'all']) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-colors flex justify-center items-center gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali ke Lihat PDRB Daerah</span>
            </a>
        </div>
    </div>
</div>

<script>
function pdrbDetailData() {
    return {
        grandTotal: '0,00',
        init() {
            const initialData = @json($existingValues);
            let sum = 0;
            Object.values(initialData).forEach(val => {
                const num = parseFloat(val);
                if (!isNaN(num)) sum += num;
            });
            this.grandTotal = sum.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
@endsection
