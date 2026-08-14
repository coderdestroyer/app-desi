@extends('partials.layouts.operator')

@section('title', 'Analisis Tipologi Sektor')

@section('content')
<div x-data="{ 
    activeTab: '{{ ($editItem || session('tab') === 'simulasi' || request('tab') === 'simulasi' || request('sim_page') || $errors->any()) ? 'simulasi' : 'real' }}',
    isDeleteModalOpen: false,
    deleteActionUrl: '',
    deleteTargetName: '',
    deleteTargetYear: '',
    openDeleteModal(url, name, year) {
        this.deleteActionUrl = url;
        this.deleteTargetName = name || 'Simulasi';
        this.deleteTargetYear = year || '-';
        this.isDeleteModalOpen = true;
    }
}" class="space-y-6">

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                <p class="text-sm font-semibold">{{ session('error') }}</p>
            </div>
            <button @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Header & Mode Pill Switcher Card (Exact 55:45 Ratio & Multi-Line Flexible Buttons) -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#CFE3D5] shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 sm:gap-6">
        <!-- Title & Subtitle Section (55% Width) -->
        <div class="w-full lg:w-[55%]">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EEF8F2] text-[#145239] text-xs font-bold mb-2 border border-[#CFE3D5]">
                <i class="fa-solid fa-chart-line text-[#D8A62A]"></i>
                <span>Analisis Kombinasi LQ & Shift-Share</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight">Analisis Tipologi Sektor</h1>
            <p class="text-slate-500 text-xs md:text-sm mt-0.5">Klasifikasi Sektor Wilayah Berdasarkan 4 Kuadran Pertumbuhan & Keunggulan Komparatif</p>
        </div>

        <!-- Pill Tabs Switcher (45% Width with Multi-Line Text Wrapping) -->
        <div class="w-full lg:w-[45%] flex justify-start lg:justify-end">
            <div class="inline-flex w-full p-1.5 bg-slate-100/90 rounded-xl border border-slate-200/90 shadow-inner">
                <button type="button" @click="activeTab = 'real'" 
                    :class="activeTab === 'real' ? 'bg-[#145239] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 inline-flex flex-wrap items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg font-bold text-xs text-center leading-tight transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-chart-pie text-[#FFD54F] shrink-0"></i>
                    <span>Data Real PDRB</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-700/80 text-emerald-100 font-bold shrink-0">Otomatis</span>
                </button>

                <button type="button" @click="activeTab = 'simulasi'" 
                    :class="activeTab === 'simulasi' ? 'bg-[#145239] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 inline-flex flex-wrap items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg font-bold text-xs text-center leading-tight transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-vials text-[#FFD54F] shrink-0"></i>
                    <span>Simulasi & Upload Excel</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-700/80 text-emerald-100 font-bold shrink-0">Custom</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: DATA REAL PDRB (Ringkasan Multi-Tahun Per Daerah) -->
    <div x-show="activeTab === 'real'" x-transition class="space-y-6">

        <!-- Component Filter Bar (Provinsi, Kabupaten, Tahun, Search) -->
        <x-analisa-filter-bar 
            :action="route('operator.tipologi.index')"
            :provinsis="$provinsis ?? []"
            :kabupatens="$kabupatens ?? []"
            :availableYears="$availableYears ?? []"
            searchPlaceholder="Cari Wilayah atau Tahun..."
        />

        <!-- Table Container -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-4 sm:p-5 md:p-6">
            <div class="mb-5 sm:mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 sm:gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800">Ringkasan Tipologi Sektor Per Wilayah & Tahun</h2>
                    <p class="text-slate-500 text-xs mt-0.5">Prioritas Wilayah Provinsi Ditampilkan Teratas untuk Setiap Tahun</p>
                </div>
            </div>

            <div id="tableContainer" class="transition-opacity duration-200">
                @include('operator.potensi_unggulan.tipologi.partials.table')
            </div>
        </div>
    </div>

    <!-- TAB 2: SIMULASI & UPLOAD EXCEL (Custom Mode) -->
    <div x-show="activeTab === 'simulasi'" x-transition class="space-y-6">
        <!-- Info Banner -->
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/90 border border-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-slate-700 text-xs md:text-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 font-bold mt-0.5">
                    <i class="fa-solid fa-vials text-[#FFD54F]"></i>
                </div>
                <div>
                    <span class="font-bold text-amber-900 block text-sm">Mode Simulasi & Upload Excel (Custom)</span>
                    Gunakan form di bawah ini atau unggah berkas Excel untuk melakukan pengujian skenario Tipologi Sektor custom tanpa mengubah data PDRB resmi.
                </div>
            </div>

            <button type="button" onclick="document.getElementById('importModal').style.display='flex'" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2.5 rounded-xl font-bold transition-all text-xs md:text-sm shadow-sm shrink-0">
                <i class="fa-solid fa-file-excel text-[#FFD54F]"></i>
                <span>Unggah Excel</span>
            </button>
        </div>

        <!-- Form Card Container -->
        <div id="formSimulasiCard" class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-4 sm:p-5 md:p-6">
            <div class="mb-5 sm:mb-6 border-b border-slate-100 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 id="formTitleText" class="text-lg sm:text-xl font-bold text-slate-800">{{ $editItem ? 'Edit Data Simulasi Tipologi Sektor' : 'Tambah Simulasi Tipologi Sektor Baru' }}</h2>
                    <p id="formSubTitleText" class="text-slate-500 text-xs mt-0.5">Masukkan variabel nilai LQ & SS custom untuk diuji</p>
                </div>
                <div id="editBadgeNotice" class="{{ $editItem ? '' : 'hidden' }}">
                    <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-pen text-amber-600"></i>
                        <span>Sedang Mengedit Log Terpilih</span>
                    </span>
                </div>
            </div>

            <form id="tipologiForm" action="{{ $editItem ? route('operator.tipologi.update', $editItem['id']) : route('operator.tipologi.store') }}" method="POST" class="space-y-6" x-data="{ 
                tingkat_wilayah: '{{ old('tingkat_wilayah', $editItem['tingkat_wilayah'] ?? 'Kabupaten/Kota') }}',
                provinsi: '{{ old('provinsi', $editItem['provinsi'] ?? '') }}',
                get listKabupaten() {
                    return window.daftarWilayah[this.provinsi] || [];
                }
            }">
                @csrf
                <div id="methodPutContainer">
                    @if($editItem)
                        @method('PUT')
                    @endif
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Tingkat Wilayah</label>
                        <div class="relative">
                            <select id="field_tingkat_wilayah" name="tingkat_wilayah" x-model="tingkat_wilayah" class="op-input op-input-icon op-select" required>
                                <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                                <option value="Provinsi">Provinsi</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Sektor</label>
                        <div class="relative">
                            <input id="field_sektor" list="sektor-list" name="sektor" value="{{ old('sektor', $editItem['sektor'] ?? '') }}" class="op-input op-input-icon op-datalist" placeholder="Pilih Sektor" required>
                            <datalist id="sektor-list">
                                <option value="PERTANIAN, KEHUTANAN, DAN PERIKANAN">
                                <option value="PERTAMBANGAN DAN PENGGALIAN">
                                <option value="INDUSTRI PENGOLAHAN">
                                <option value="PENGADAAN LISTRIK DAN GAS">
                                <option value="KONSTRUKSI">
                                <option value="PERDAGANGAN BESAR DAN ECERAN">
                                <option value="TRANSPORTASI DAN PERGUDANGAN">
                                <option value="INFORMASI DAN KOMUNIKASI">
                                <option value="JASA KEUANGAN DAN ASURANSI">
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Tahun</label>
                        <input id="field_tahun" type="number" name="tahun" value="{{ old('tahun', $editItem['tahun'] ?? '') }}" min="1900" max="2100" class="op-input" placeholder="Pilih Tahun" required>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Provinsi</label>
                        <div class="relative">
                            <input id="field_provinsi" list="provinsi-list" name="provinsi" x-model="provinsi" autocomplete="off" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Provinsi" required>
                            <datalist id="provinsi-list">
                                <template x-for="prov in Object.keys(window.daftarWilayah)" :key="prov">
                                    <option :value="prov"></option>
                                </template>
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 col-span-1" x-show="tingkat_wilayah === 'Kabupaten/Kota'">
                        <label class="op-label">Kabupaten / Kota</label>
                        <div class="relative">
                            <input id="field_kabupaten" list="kabupaten-list" name="kabupaten" value="{{ old('kabupaten', $editItem['kabupaten'] ?? '') }}" :required="tingkat_wilayah === 'Kabupaten/Kota'" autocomplete="off" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Kab/Kota">
                            <datalist id="kabupaten-list">
                                <template x-for="kab in listKabupaten" :key="kab">
                                    <option :value="kab"></option>
                                </template>
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Nilai LQ (Rasio Kontribusi)</label>
                        <input id="field_nilai_lq" type="text" name="nilai_lq" value="{{ old('nilai_lq', $editItem['nilai_lq'] ?? '') }}" class="op-input" placeholder="Contoh: 1.25" required>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Nilai SS (Dij / Kinerja Pertumbuhan)</label>
                        <input id="field_nilai_ss" type="text" name="shift_share_net" value="{{ old('shift_share_net', old('nilai_ss', $editItem['shift_share_net'] ?? $editItem['nilai_ss'] ?? '')) }}" class="op-input" placeholder="Contoh: 50.000" required>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk text-[#FFD54F]"></i>
                        <span id="submitBtnText">{{ $editItem ? 'Perbarui Data Simulasi' : 'Simpan Data Simulasi' }}</span>
                    </button>
                    <a id="cancelEditBtn" href="{{ route('operator.tipologi.index', ['tab' => 'simulasi']) }}" class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors {{ $editItem ? '' : 'hidden' }}">Batal Edit</a>
                </div>
            </form>
        </div>

        <!-- Tabel Log Hasil Simulasi Operator -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-4 sm:p-5 md:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-[#145239]"></i>
                        <span>Riwayat Log Hasil Simulasi Tipologi Sektor</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Klik pada salah satu baris di bawah untuk memuat data ke dalam form edit di atas</p>
                </div>
                <span class="px-3 py-1 bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5] rounded-xl text-xs font-bold">
                    Total: {{ count($simulasiList ?? []) }} Data Simulasi
                </span>
            </div>

            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table id="tipologiTable" class="w-full text-xs text-left text-slate-700">
                    <thead class="bg-[#145239] text-white uppercase text-[11px] font-semibold tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3">Wilayah Analisis</th>
                            <th class="px-4 py-3">Sektor</th>
                            <th class="px-4 py-3 text-center">Tahun</th>
                            <th class="px-4 py-3 text-right">Nilai LQ</th>
                            <th class="px-4 py-3 text-right">Nilai SS (Cij)</th>
                            <th class="px-4 py-3 text-center">Hasil Kuadran / Kategori</th>
                            <th class="px-4 py-3 text-center">Tanggal Log</th>
                            <th class="px-4 py-3 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($simulasiList ?? [] as $index => $sim)
                            <tr onclick="editTipologiItem({{ json_encode($sim) }})" class="hover:bg-emerald-50/70 cursor-pointer transition-colors group">
                                <td class="px-4 py-3 text-center font-bold text-slate-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800 group-hover:text-[#145239]">
                                    {{ $sim['daerah_analisis'] ?? ($sim['kabupaten'] ?? $sim['provinsi'] ?? '-') }}
                                    <span class="block text-[10px] text-slate-400 font-normal">Pembanding: {{ $sim['daerah_pembanding'] ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $sim['sektor'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-bold text-slate-600">{{ $sim['tahun'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($sim['nilai_lq'] ?? $sim['lq'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($sim['shift_share_net'] ?? $sim['cij'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $kd = $sim['kuadran'] ?? '';
                                        $badgeClass = match($kd) {
                                            'Kuadran I' => 'bg-[#EEF8F2] text-[#145239] border-[#CFE3D5]',
                                            'Kuadran II' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'Kuadran III' => 'bg-blue-50 text-blue-800 border-blue-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-bold border {{ $badgeClass }}">
                                        {{ $kd }} - {{ $sim['kategori_sektor'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-500 text-[11px]">
                                    {{ isset($sim['created_at']) ? \Carbon\Carbon::parse($sim['created_at'])->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                                    <button type="button" @click="openDeleteModal('{{ route('operator.tipologi.destroy', $sim['id']) }}', '{{ str_replace('\'', '\\\'', $sim['sektor'] ?? $sim['daerah_analisis'] ?? 'Simulasi') }}', '{{ $sim['tahun'] ?? '' }}')" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 flex items-center justify-center transition-colors" title="Hapus Simulasi">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-2xl mb-2 block text-slate-300"></i>
                                    <span>Belum ada log data simulasi yang tersimpan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$simulasiList" />
        </div>

        <!-- Saved Simulations Action Toolbar -->
        <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-3">
            <button type="button" @click="openDeleteModal('{{ route('operator.tipologi.empty') }}', 'Seluruh Data Simulasi', 'Semua Tahun')" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all shadow-xs">
                <i class="fa-solid fa-trash-can"></i>
                <span>Hapus Semua Simulasi</span>
            </button>

            <!-- Delete Confirmation Modal -->
            <x-confirm-delete-modal title="Konfirmasi Hapus Data Simulasi" warningMessage="Tindakan ini akan menghapus log data simulasi terpilih dan tidak dapat dibatalkan." />
        </div>

</div>

@push('modals')
<x-import-modal action="{{ route('operator.tipologi.import') }}" type="tipologi" />
@endpush

<script>
    function editTipologiItem(sim) {
        if(!sim) return;
        
        var form = document.getElementById('tipologiForm');
        if(!form) return;

        form.action = '/operator/analisis-tipologi/' + sim.id;

        document.getElementById('methodPutContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('field_sektor').value = sim.sektor || '';
        document.getElementById('field_tahun').value = sim.tahun || '';
        document.getElementById('field_nilai_lq').value = sim.nilai_lq ?? sim.lq ?? '';
        document.getElementById('field_nilai_ss').value = sim.shift_share_net ?? sim.cij ?? sim.nilai_ss ?? '';

        var provEl = document.getElementById('field_provinsi');
        if(provEl) {
            provEl.value = sim.provinsi || '';
            provEl.dispatchEvent(new Event('input', { bubbles: true }));
        }

        var twEl = document.getElementById('field_tingkat_wilayah');
        if(twEl) {
            twEl.value = sim.tingkat_wilayah || 'Kabupaten/Kota';
            twEl.dispatchEvent(new Event('change', { bubbles: true }));
        }

        setTimeout(function() {
            var kabEl = document.getElementById('field_kabupaten');
            if(kabEl) kabEl.value = sim.kabupaten || '';
        }, 50);

        document.getElementById('formTitleText').innerText = 'Edit Data Simulasi Tipologi Sektor';
        document.getElementById('formSubTitleText').innerText = 'Mengubah variabel log simulasi terpilih';
        document.getElementById('submitBtnText').innerText = 'Perbarui Data Simulasi';
        document.getElementById('editBadgeNotice').classList.remove('hidden');
        document.getElementById('cancelEditBtn').classList.remove('hidden');

        document.getElementById('formSimulasiCard').scrollIntoView({ behavior: 'smooth' });
    }

    function exportToExcel() {
        var table = document.getElementById("tipologiTable");
        var clone = table.cloneNode(true);
        
        var rows = clone.rows;
        for (var i = 0; i < rows.length; i++) {
            if(rows[i].cells.length > 0) {
                rows[i].deleteCell(-1); 
            }
        }
        
        var wb = XLSX.utils.table_to_book(clone, {sheet: "Ringkasan Tipologi Sektor"});
        XLSX.writeFile(wb, "Hasil_Ringkasan_Analisis_Tipologi_Sektor.xlsx");
    }
</script>

@include('partials.live-filter-script')
@endsection
