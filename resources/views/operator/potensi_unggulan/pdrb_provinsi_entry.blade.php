@extends('partials.layouts.operator')

@section('title', 'Input Data PDRB Provinsi')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="pdrbEntryData()">

    {{-- Breadcrumb & Action Bar --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('operator.pdrb-provinsi.index') }}" class="ml-12 lg:ml-0 inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-[#CFE3D5] text-xs sm:text-sm font-bold text-[#145239] hover:bg-[#EEF8F2] transition-colors shadow-xs">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Daftar PDRB Provinsi</span>
        </a>
    </div>

    {{-- Banner Header --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-10 shadow-xl text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="space-y-2 sm:space-y-3 relative z-10">
            @if($isReadOnly ?? false)
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-800/60 border border-sky-700/60 text-sky-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-eye text-[#FFD54F]"></i>
                    <span>Mode Lihat Nilai Sektor PDRB Provinsi (Read Only)</span>
                </div>
            @else
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-building-columns text-[#FFD54F]"></i>
                    <span>Mode Input / Edit Nilai Sektor PDRB Provinsi</span>
                </div>
            @endif
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight">
                Provinsi {{ $provinsi->nama_provinsi }} &bull; Tahun {{ $tahun }}
            </h1>
            <p class="text-emerald-100/90 text-xs sm:text-sm md:text-base max-w-2xl leading-relaxed">
                @if($isReadOnly ?? false)
                    Melihat rincian nilai PDRB Provinsi (dalam <strong>Rp Juta</strong>) untuk 17 Sektor Lapangan Usaha BPS.
                @else
                    Silakan isikan atau perbarui nilai PDRB Provinsi (dalam <strong>Rp Juta</strong>) untuk 17 Sektor Lapangan Usaha BPS di bawah ini. Nilai otomatis diformat dengan pemisah ribuan (titik).
                @endif
            </p>
        </div>

        <div class="bg-white/15 border border-white/20 backdrop-blur-md p-4 sm:p-5 rounded-2xl text-left md:text-right shrink-0 relative z-10 shadow-inner w-full md:w-auto">
            <span class="block text-xs uppercase tracking-wider text-emerald-200 font-bold mb-1">Total PDRB Provinsi</span>
            <span class="text-2xl sm:text-3xl font-mono font-black text-[#FFD54F]">
                Rp <span x-text="grandTotal">0,00</span>
            </span>
        </div>
    </section>

    {{-- Main Form Card --}}
    <form action="{{ route('operator.pdrb-provinsi.save-entry') }}" method="POST" class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-5 sm:p-6 md:p-8 space-y-6">
        @csrf
        <input type="hidden" name="provinsi_id" value="{{ $provinsi->provinsi_id }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3 text-base md:text-lg font-bold text-slate-800">
                <div class="w-9 h-9 rounded-xl bg-[#EEF8F2] text-[#145239] flex items-center justify-center font-bold border border-[#CFE3D5] shrink-0">
                    <i class="fa-solid fa-list-ol"></i>
                </div>
                <span>Nilai PDRB Menurut 17 Sektor Lapangan Usaha Default (BPS)</span>
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
                        {{-- Hidden Input sent to backend (raw numeric float) --}}
                        <input type="hidden" name="sektor_values[{{ $sek->sektor_id }}]" :value="rawValues['{{ $sek->sektor_id }}']">

                        {{-- Visible Formatted Input (Thousand dots format) --}}
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 select-none">Rp</span>
                            <input type="text" id="sektor_{{ $sek->sektor_id }}"
                                x-model="formattedValues['{{ $sek->sektor_id }}']"
                                @input="formatInput('{{ $sek->sektor_id }}', $event.target.value)"
                                placeholder="0"
                                @if($isReadOnly ?? false) disabled @endif
                                class="w-full h-11 rounded-xl border border-slate-300 focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 text-sm md:text-base font-mono text-right pl-9 pr-3 text-slate-900 font-bold outline-none transition-all shadow-xs {{ ($isReadOnly ?? false) ? 'bg-slate-100 text-slate-500 cursor-not-allowed border-slate-200' : 'bg-white' }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Action Buttons Footer --}}
        <div class="pt-6 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ route('operator.pdrb-provinsi.index') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-colors flex justify-center items-center">
                Kembali ke Daftar PDRB Provinsi
            </a>
            @if(!($isReadOnly ?? false))
                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs sm:text-sm font-extrabold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2.5 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-floppy-disk text-base"></i>
                    <span>Simpan Seluruh Data PDRB Provinsi ({{ $tahun }})</span>
                </button>
            @endif
        </div>
    </form>
</div>

<script>
function pdrbEntryData() {
    return {
        rawValues: {},
        formattedValues: {},
        grandTotal: '0,00',

        init() {
            const initialData = @json($existingValues);
            @foreach($sektors as $sek)
                const val_{{ $sek->sektor_id }} = initialData[{{ $sek->sektor_id }}] ?? null;
                if (val_{{ $sek->sektor_id }} !== null && val_{{ $sek->sektor_id }} !== '') {
                    const num = parseFloat(val_{{ $sek->sektor_id }});
                    this.rawValues['{{ $sek->sektor_id }}'] = num;
                    this.formattedValues['{{ $sek->sektor_id }}'] = this.formatNumber(num);
                } else {
                    this.rawValues['{{ $sek->sektor_id }}'] = '';
                    this.formattedValues['{{ $sek->sektor_id }}'] = '';
                }
            @endforeach
            this.recalculateTotal();
        },

        formatInput(sektorId, inputVal) {
            let clean = inputVal.replace(/[^0-9,]/g, '');
            const parts = clean.split(',');
            if (parts.length > 2) {
                clean = parts[0] + ',' + parts.slice(1).join('');
            }

            let intPart = parts[0] || '';
            let decPart = parts.length > 1 ? ',' + parts[1].substring(0, 2) : '';

            if (intPart) {
                intPart = parseInt(intPart, 10).toLocaleString('id-ID');
            }

            this.formattedValues[sektorId] = intPart + decPart;

            let numericStr = clean.replace(/\./g, '').replace(',', '.');
            this.rawValues[sektorId] = numericStr !== '' ? parseFloat(numericStr) : '';

            this.recalculateTotal();
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(num);
        },

        recalculateTotal() {
            let sum = 0;
            Object.values(this.rawValues).forEach(v => {
                if (typeof v === 'number' && !isNaN(v)) {
                    sum += v;
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
