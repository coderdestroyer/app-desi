@extends('layouts.admin')

@section('title', "Input PDB Nasional Tahun {$tahun}")

@section('content')
<div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8 space-y-6" x-data="pdbNasionalEntryData()">

    {{-- Breadcrumb & Back Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.pdb-nasional.index') }}" class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-bold text-[#145239] hover:bg-emerald-50 transition-colors shadow-xs w-fit">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Kembali ke Daftar PDB Nasional</span>
        </a>

        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-xs w-fit">
            <i class="fa-solid fa-globe text-emerald-600"></i>
            <span>Skala PDB Nasional</span>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
            <span class="text-slate-800 font-bold">Tahun {{ $tahun }}</span>
        </div>
    </div>

    {{-- Header Banner --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-7 md:p-8 shadow-lg text-white flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-1 z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-pen-to-square text-[#FFD54F]"></i>
                <span>Form Entry / Perbarui PDB 17 Sektor</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">
                PDB Nasional Tahun {{ $tahun }}
            </h1>
            <p class="text-emerald-100/90 text-xs max-w-xl leading-relaxed">
                Silakan masukkan atau perbarui nilai PDB Nasional (Rp Juta) untuk 17 Sektor Ekonomi Lapangan Usaha berikut. Total nilai akan terhitung secara otomatis.
            </p>
        </div>

        <div class="z-10 bg-white/10 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/20 text-center min-w-[200px]">
            <p class="text-[10px] uppercase tracking-wider text-emerald-100 font-semibold">Estimasi Total PDB</p>
            <p class="text-xl font-black font-mono text-[#FFD54F] mt-0.5" x-text="'Rp ' + formatRibuan(totalPdb)"></p>
            <p class="text-[10px] text-emerald-200/80 mt-0.5" x-text="filledCount + ' dari 17 sektor terisi'"></p>
        </div>
    </section>

    <!-- Main Entry Form -->
    <form action="{{ route('admin.pdb-nasional.save-entry') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-[#145239]"></i>
                        Rincian Nilai 17 Sektor Lapangan Usaha PDB (Rp Juta)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Format nilai angka murni atau gunakan titik sebagai pemisah ribuan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="isiNilaiNol()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors">
                        <i class="fa-solid fa-calculator text-slate-400 mr-1"></i> Isi Sektor Kosong (0)
                    </button>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($sektors as $sektor)
                    @php
                        $val = old("sektor_values.{$sektor->sektor_id}", $existingValues[$sektor->sektor_id] ?? '');
                    @endphp
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-start gap-3 sm:max-w-xl">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#145239] font-mono font-bold text-xs flex items-center justify-center flex-shrink-0 border border-emerald-200 mt-0.5">
                                {{ $sektor->sektor_id }}
                            </span>
                            <div>
                                <h4 class="font-bold text-slate-800 text-xs md:text-sm">
                                    {{ $sektor->nama_sektor }}
                                </h4>
                                <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">
                                    Sektor Utama PDB {{ $sektor->sektor_id }} - {{ $sektor->nama_sektor }}
                                </p>
                            </div>
                        </div>

                        <div class="w-full sm:w-72 relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                x-model="values[{{ $sektor->sektor_id }}]"
                                @input="updateValue({{ $sektor->sektor_id }}, $event.target.value)"
                                placeholder="0"
                                class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-[#145239] focus:ring-[#145239] text-xs font-mono font-semibold text-slate-800 text-right transition-colors"
                            >
                            <input type="hidden" name="sektor_values[{{ $sektor->sektor_id }}]" :value="cleanValues[{{ $sektor->sektor_id }}]">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-[#145239] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-equals text-base"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-700">Total Akumulasi PDB Nasional</p>
                        <p class="text-xs text-slate-500 font-mono" x-text="filledCount + ' Sektor Terisi dari 17 Total Sektor'"></p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 block font-semibold">Total PDB (Rp Juta)</span>
                    <span class="text-2xl font-black font-mono text-[#145239]" x-text="'Rp ' + formatRibuan(totalPdb)"></span>
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.pdb-nasional.index') }}" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition-colors">
                Batal
            </a>
            <button type="submit" class="px-7 py-3 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-sm font-bold shadow-md transition-colors flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk text-sm"></i>
                <span>Simpan PDB Nasional Tahun {{ $tahun }}</span>
            </button>
        </div>
    </form>
</div>

<script>
    function pdbNasionalEntryData() {
        return {
            values: {
                @foreach($sektors as $sektor)
                    @php
                        $initVal = old("sektor_values.{$sektor->sektor_id}", $existingValues[$sektor->sektor_id] ?? '');
                        $formattedInit = $initVal !== '' ? number_format((float)$initVal, 0, ',', '.') : '';
                    @endphp
                    {{ $sektor->sektor_id }}: '{{ $formattedInit }}',
                @endforeach
            },
            cleanValues: {},

            init() {
                this.calculateTotal();
            },

            updateValue(sektorId, rawVal) {
                let clean = rawVal.replace(/[^0-9]/g, '');
                if (clean !== '') {
                    this.values[sektorId] = new Intl.NumberFormat('id-ID').format(clean);
                    this.cleanValues[sektorId] = clean;
                } else {
                    this.values[sektorId] = '';
                    this.cleanValues[sektorId] = '';
                }
            },

            get totalPdb() {
                let sum = 0;
                Object.keys(this.values).forEach(key => {
                    let raw = (this.values[key] || '').toString().replace(/[^0-9]/g, '');
                    if (raw) {
                        sum += parseFloat(raw);
                    }
                });
                return sum;
            },

            get filledCount() {
                let count = 0;
                Object.keys(this.values).forEach(key => {
                    let raw = (this.values[key] || '').toString().replace(/[^0-9]/g, '');
                    if (raw !== '') {
                        count++;
                    }
                });
                return count;
            },

            calculateTotal() {
                Object.keys(this.values).forEach(key => {
                    let raw = (this.values[key] || '').toString().replace(/[^0-9]/g, '');
                    this.cleanValues[key] = raw;
                });
            },

            isiNilaiNol() {
                Object.keys(this.values).forEach(key => {
                    if (!this.values[key] || this.values[key] === '') {
                        this.values[key] = '0';
                        this.cleanValues[key] = '0';
                    }
                });
            },

            formatRibuan(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            }
        }
    }
</script>
@endsection
