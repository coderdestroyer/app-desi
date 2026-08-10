@extends('layouts.admin')

@section('title', "Input PDRB Provinsi Sumatera Utara Tahun {$tahun}")

@section('content')
<div class="min-h-screen bg-slate-50 p-4 sm:p-6 md:p-7 lg:p-8 space-y-6" x-data="pdrbProvinsiEntryData()">

    {{-- Breadcrumb & Action Bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.pdrb.index') }}" class="ml-12 lg:ml-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-xs sm:text-sm font-bold text-[#145239] hover:bg-[#EEF8F2] transition-colors shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Kelola PDRB Admin</span>
        </a>
    </div>

    {{-- Banner Header --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="space-y-2 sm:space-y-3 relative z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-shield-halved text-[#FFD54F]"></i>
                <span>Admin Input & Edit Mode (Provinsi Sumatera Utara)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight">
                PDRB PROVINSI &bull; Tahun {{ $tahun }}
            </h1>
            <p class="text-emerald-100/90 text-xs sm:text-sm md:text-base max-w-2xl leading-relaxed">
                Silakan isikan atau perbarui nilai PDRB Provinsi Sumatera Utara (dalam <strong>Rp Juta</strong>) untuk 17 Sektor Lapangan Usaha BPS di bawah ini.
            </p>
        </div>

        <div class="bg-white/15 border border-white/20 backdrop-blur-md p-4 sm:p-5 rounded-2xl text-left md:text-right shrink-0 relative z-10 shadow-inner w-full md:w-auto">
            <span class="block text-xs uppercase tracking-wider text-emerald-200 font-bold mb-1">Estimasi Total PDRB Provinsi</span>
            <span class="text-2xl sm:text-3xl font-mono font-black text-[#FFD54F]">
                Rp <span x-text="grandTotal">0,00</span>
            </span>
        </div>
    </section>

    {{-- Main Form Card --}}
    <form action="{{ route('admin.pdrb.save-entry-provinsi') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 md:p-8 space-y-6">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3 text-base md:text-lg font-bold text-slate-800">
                <div class="w-9 h-9 rounded-xl bg-[#EEF8F2] text-[#145239] flex items-center justify-center font-bold border border-emerald-200 shrink-0">
                    <i class="fa-solid fa-list-ol"></i>
                </div>
                <span>Isi Nilai PDRB Provinsi According 17 Sektor BPS</span>
            </div>
            <span class="text-xs text-slate-600 bg-slate-100 px-3 py-1 rounded-full font-mono font-bold border border-slate-200 shrink-0">
                17 Sektor BPS
            </span>
        </div>

        {{-- 17 Sectors Inputs Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
            @foreach($sektors as $sek)
                <div class="p-4 rounded-2xl border border-slate-200 bg-[#F8FAFC] hover:border-[#145239] hover:bg-white transition-all flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3.5 shadow-2xs group">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <span class="w-8 h-8 rounded-xl bg-[#E7F2EB] text-[#145239] font-black text-sm flex items-center justify-center shrink-0 border border-[#CFE3D5] group-hover:bg-[#145239] group-hover:text-white transition-colors">
                            {{ $loop->iteration }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <label for="sektor_{{ $sek->sektor_id }}" class="block text-sm font-bold text-slate-800 leading-snug group-hover:text-[#145239] transition-colors cursor-pointer" title="{{ $sek->nama_sektor }}">
                                {{ $sek->nama_sektor }}
                            </label>
                            <span class="text-xs text-slate-400 font-mono">Kode Sektor: {{ $sek->sektor_id }}</span>
                        </div>
                    </div>

                    <div class="w-full sm:w-52 md:w-56 shrink-0 relative">
                        <input type="hidden" name="sektor_values[{{ $sek->sektor_id }}]" :value="rawValues['{{ $sek->sektor_id }}']">

                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 select-none">Rp</span>
                            <input type="text" id="sektor_{{ $sek->sektor_id }}"
                                x-model="formattedValues['{{ $sek->sektor_id }}']"
                                @input="formatInput('{{ $sek->sektor_id }}', $event.target.value)"
                                placeholder="0"
                                class="w-full h-11 rounded-xl border border-slate-300 focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 text-sm md:text-base font-mono text-right pl-9 pr-3 text-slate-900 font-bold bg-white outline-none transition-all shadow-xs">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Action Buttons Footer --}}
        <div class="pt-6 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('admin.pdrb.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-colors flex justify-center items-center">
                Batal / Kembali
            </a>
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs sm:text-sm font-extrabold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2.5 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-floppy-disk text-base"></i>
                <span>Simpan PDRB Provinsi ({{ $tahun }})</span>
            </button>
        </div>
    </form>
</div>

<script>
function pdrbProvinsiEntryData() {
    return {
        formattedValues: {
            @foreach($sektors as $sek)
                '{{ $sek->sektor_id }}': '{{ isset($existingValues[$sek->sektor_id]) && $existingValues[$sek->sektor_id] !== '' ? number_format((float)$existingValues[$sek->sektor_id], 0, ',', '.') : '' }}',
            @endforeach
        },
        rawValues: {
            @foreach($sektors as $sek)
                '{{ $sek->sektor_id }}': '{{ $existingValues[$sek->sektor_id] ?? '' }}',
            @endforeach
        },
        formatInput(id, val) {
            if (!val) {
                this.formattedValues[id] = '';
                this.rawValues[id] = '';
                return;
            }
            let clean = val.replace(/[^0-9]/g, '');
            if (clean === '') {
                this.formattedValues[id] = '';
                this.rawValues[id] = '';
                return;
            }
            this.rawValues[id] = clean;
            this.formattedValues[id] = new Intl.NumberFormat('id-ID').format(clean);
        },
        get grandTotal() {
            let total = 0;
            Object.values(this.rawValues).forEach(val => {
                let num = parseFloat(val);
                if (!isNaN(num)) {
                    total += num;
                }
            });
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
        }
    }
}
</script>
@endsection
