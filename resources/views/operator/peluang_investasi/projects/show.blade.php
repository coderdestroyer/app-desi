@extends('partials.layouts.operator')

@section('title', $project->nama_proyek . ' - Detail Proyek IPRO')
@section('page_heading', 'Detail Proyek Investasi')

@section('content')
    <div x-data="{
        showModal: false,
        isSaving: false,
        form: {
            rasio_modal_sendiri: {{ $project->rasio_modal_sendiri ?: 60 }},
            rasio_pinjaman_kredit: {{ $project->rasio_pinjaman_kredit ?: 40 }},
            suku_bunga_kredit: {{ $project->suku_bunga_kredit ?: 8.05 }},
            tenor_kredit_tahun: {{ $project->tenor_kredit_tahun ?: 5 }}
        },
        toast: { show: false, message: '', isSuccess: true },
        saveFinancing() {
            this.isSaving = true;
            fetch('{{ route('operator.projects.cashflow.settings', $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                if(data.success) {
                    this.showModal = false;
                    this.toast.isSuccess = true;
                    this.toast.message = data.message;
                    this.toast.show = true;
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Gagal menyimpan parameter.');
                }
            })
            .catch(() => {
                this.isSaving = false;
                alert('Terjadi kesalahan koneksi.');
            });
        }
    }">

        <!-- Toast Alert -->
        <div x-show="toast.show" x-transition class="mb-6 p-4 rounded-xl shadow-sm flex items-center justify-between border bg-[#EEF8F2] border-[#CFE3D5] text-[#145239]">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mr-3"></i>
                <span class="text-sm font-semibold" x-text="toast.message"></span>
            </div>
            <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Breadcrumb & Navigasi Kembali -->
        <div class="mb-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <nav class="flex text-xs sm:text-sm text-slate-500 space-x-2">
                <a href="{{ route('operator.projects.index') }}" class="hover:text-[#145239] font-medium">Proyek Investasi</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold truncate max-w-[200px] sm:max-w-xs">{{ $project->nama_proyek }}</span>
            </nav>
            <a href="{{ route('operator.projects.index') }}" class="inline-flex items-center justify-center text-xs font-bold text-slate-700 hover:text-[#145239] bg-white px-4 py-2.5 rounded-xl border border-[#CFE3D5] shadow-xs transition-colors w-full sm:w-auto">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                <span>Kembali ke Daftar Proyek</span>
            </a>
        </div>

        <!-- Header Card Proyek -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-5 sm:p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-[#EEF8F2] pb-5 sm:pb-6 mb-5 sm:mb-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">{{ $project->nama_proyek }}</h1>
                        <span class="px-3 py-1 bg-[#EEF8F2] text-[#145239] font-bold text-xs rounded-full border border-[#CFE3D5] whitespace-nowrap">Proyek Aktif</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $project->deskripsi ?: 'Tidak ada deskripsi rincian proyek.' }}</p>
                </div>
            </div>

            <!-- Stats Metadata -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Tahun Awal</span>
                    <span class="text-base sm:text-lg font-bold text-slate-800 font-mono mt-1 block">{{ $project->tahun_awal }}</span>
                </div>
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Jangka Waktu</span>
                    <span class="text-base sm:text-lg font-bold text-[#145239] mt-1 block">{{ $project->jangka_waktu_tahun }} Tahun</span>
                </div>
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Rentang Proyeksi</span>
                    <span class="text-base sm:text-lg font-bold text-slate-800 font-mono mt-1 block">{{ $project->tahun_awal }} - {{ $project->tahun_awal + $project->jangka_waktu_tahun - 1 }}</span>
                </div>
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Kabupaten / Kota</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-700 mt-1 block truncate">{{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Wilayah Sumut' }}</span>
                </div>
            </div>
        </div>

        <!-- Parameter Skema Pembiayaan & Kredit -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-5 sm:p-6 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-scale-balanced text-[#145239]"></i>
                        <span>Parameter Pembiayaan & Kredit Bank</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Skema rasio modal awal dan perhitungan suku bunga pinjaman untuk proyeksi finansial IPRO.</p>
                </div>
                <button @click="showModal = true" class="w-full sm:w-auto px-4 py-2.5 bg-[#EEF8F2] hover:bg-[#E7F2EB] text-[#145239] text-xs font-bold rounded-xl border border-[#CFE3D5] transition-colors flex items-center justify-center gap-1.5 shadow-sm shrink-0">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Ubah Parameter</span>
                </button>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 pt-2">
                <div class="bg-[#EEF8F2] rounded-xl p-3.5 sm:p-4 border border-[#CFE3D5]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-[#145239] uppercase tracking-wider">Modal Sendiri (Equity)</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ number_format($project->rasio_modal_sendiri ?: 60, 0) }}%</span>
                </div>
                <div class="bg-[#EEF8F2] rounded-xl p-3.5 sm:p-4 border border-[#CFE3D5]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-[#145239] uppercase tracking-wider">Pinjaman Kredit (Debt)</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ number_format($project->rasio_pinjaman_kredit ?: 40, 0) }}%</span>
                </div>
                <div class="bg-amber-50 rounded-xl p-3.5 sm:p-4 border border-amber-200">
                    <span class="block text-[11px] sm:text-xs font-semibold text-[#D4A017] uppercase tracking-wider">Suku Bunga Kredit</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ number_format($project->suku_bunga_kredit ?: 8.05, 2) }}% / tahun</span>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3.5 sm:p-4 border border-emerald-200">
                    <span class="block text-[11px] sm:text-xs font-semibold text-emerald-800 uppercase tracking-wider">Tenor Kredit</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ $project->tenor_kredit_tahun ?: 5 }} Tahun</span>
                </div>
            </div>
        </div>

        <!-- Modul Navigasi Kalkulasi (CAPEX, Laba Rugi, Arus Kas) -->
        <div class="mb-6">
            <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-4">Modul Dokumen Finansial (IPRO Engine)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                
                <!-- Modul 1: CAPEX -->
                <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm flex flex-col justify-between hover:border-[#145239] transition-all duration-300 group">
                    <div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#EEF8F2] text-[#145239] rounded-xl flex items-center justify-center mb-3 sm:mb-4 font-black text-base sm:text-lg border border-[#CFE3D5]">
                            01
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2 group-hover:text-[#145239] transition-colors">Tabel Estimasi CAPEX</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Pencatatan & kalkulasi pengeluaran modal awal (biaya persiapan, pembersihan lahan, pembangunan fasilitas) dengan hirarki komponen dinamis.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#EEF8F2]">
                        <a href="{{ route('operator.projects.capex.index', $project->id) }}" class="w-full py-2.5 px-4 bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-md text-center gap-2">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Kelola Estimasi CAPEX</span>
                        </a>
                    </div>
                </div>

                <!-- Modul 2: Laba Rugi -->
                <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm flex flex-col justify-between hover:border-[#145239] transition-all duration-300 group">
                    <div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-50 text-[#D4A017] rounded-xl flex items-center justify-center mb-3 sm:mb-4 font-black text-base sm:text-lg border border-amber-200">
                            02
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2 group-hover:text-[#D4A017] transition-colors">Proyeksi Laba Rugi (P&L)</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Proyeksi pendapatan dan biaya operasional per tahun selama {{ $project->jangka_waktu_tahun }} tahun proyeksi operasi.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#EEF8F2]">
                        <a href="{{ route('operator.projects.pl.index', $project->id) }}" class="w-full py-2.5 px-4 bg-[#D4A017] hover:bg-[#b8890f] text-white text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-md text-center gap-2">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Kelola Laba Rugi (P&L)</span>
                        </a>
                    </div>
                </div>

                <!-- Modul 3: Arus Kas -->
                <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm flex flex-col justify-between hover:border-[#145239] transition-all duration-300 group">
                    <div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-50 text-emerald-800 rounded-xl flex items-center justify-center mb-3 sm:mb-4 font-black text-base sm:text-lg border border-emerald-200">
                            03
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2 group-hover:text-emerald-800 transition-colors">Tabel Arus Kas (Cash Flow)</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Pelacakan aliran kas masuk, kas keluar operasional, non-operasional, dan modal awal (Tahun 0 s/d Tahun {{ $project->jangka_waktu_tahun }}).
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#EEF8F2]">
                        <a href="{{ route('operator.projects.cashflow.index', $project->id) }}" class="w-full py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-md text-center gap-2">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Lihat Tabel Arus Kas</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Edit Parameter Pembiayaan -->
        <template x-teleport="body">
            <div x-show="showModal" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto overflow-x-hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-transition>
                <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-[#CFE3D5] relative my-auto max-h-[90vh] flex flex-col overflow-hidden box-border" @click.outside="showModal = false">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-[#EEF8F2] shrink-0">
                        <h3 class="font-bold text-slate-800 text-base">Ubah Parameter Pembiayaan</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 shrink-0">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-4 text-sm flex-1 overflow-y-auto pr-1">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Rasio Modal Sendiri (Equity %)</label>
                            <input type="number" min="0" max="100" x-model.number="form.rasio_modal_sendiri" @input="form.rasio_pinjaman_kredit = 100 - form.rasio_modal_sendiri" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm box-border">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Rasio Pinjaman Kredit (Debt %)</label>
                            <input type="number" min="0" max="100" x-model.number="form.rasio_pinjaman_kredit" @input="form.rasio_modal_sendiri = 100 - form.rasio_pinjaman_kredit" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm bg-slate-50 box-border">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Suku Bunga Kredit (% per Tahun)</label>
                            <input type="number" step="0.01" min="0" max="100" x-model.number="form.suku_bunga_kredit" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm box-border">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tenor Kredit (Tahun)</label>
                            <input type="number" min="1" max="50" x-model.number="form.tenor_kredit_tahun" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm box-border">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 pt-4 border-t border-[#EEF8F2] shrink-0">
                        <button @click="showModal = false" class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button @click="saveFinancing()" :disabled="isSaving" class="w-full sm:w-auto px-5 py-2.5 bg-[#145239] hover:bg-[#0B5D3D] text-white rounded-xl text-xs font-bold transition-colors flex items-center justify-center">
                            <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Parameter'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
