@extends('partials.layouts.operator')

@section('content')
<div>
    <!-- Alert Messages -->
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Form Container -->
    <div class="op-card">
        <div class="op-card-header">
            <!-- Header & Import Button -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Analisis Tipologi Sektor</h1>
                    <p class="text-slate-600 mt-1">Masukkan Data Tipologi Sektor</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" onclick="document.getElementById('syncModal').style.display='flex'" class="flex items-center gap-2 bg-[#D8A62A] hover:bg-[#B58A20] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Tarik Data Database
                    </button>
                    <button type="button" onclick="document.getElementById('importModal').style.display='flex'" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Unggah Excel
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ $editItem ? route('operator.tipologi.update', $editItem['id']) : route('operator.tipologi.store') }}" method="POST" class="space-y-6" x-data="{ 
                tingkat_wilayah: '{{ old('tingkat_wilayah', $editItem['tingkat_wilayah'] ?? 'Kabupaten/Kota') }}',
                provinsi: '{{ old('provinsi', $editItem['provinsi'] ?? '') }}',
                get listKabupaten() {
                    return window.daftarWilayah[this.provinsi] || [];
                }
            }">
        @csrf
        @if($editItem)
            @method('PUT')
        @endif
        
        <!-- Top Inputs Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div class="space-y-2 col-span-1">
                <label class="op-label">Tingkat Wilayah</label>
                <div class="relative">
                    <select name="tingkat_wilayah" x-model="tingkat_wilayah" class="op-input op-input-icon op-select" required>
                        <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                        <option value="Provinsi">Provinsi</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                        <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                    </div>
                </div>
            </div>

            <!-- Sektor -->
            <div class="space-y-2 col-span-1">
                <label class="op-label">Sektor</label>
                <div class="relative">
                    <input list="sektor-list" name="sektor" value="{{ old('sektor', $editItem['sektor'] ?? '') }}" class="op-input op-input-icon op-datalist" placeholder="Pilih Sektor" required>
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
                        <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Tahun -->
            <div class="space-y-2 col-span-1">
                <label class="op-label">Tahun</label>
                <input type="number" name="tahun" value="{{ old('tahun', $editItem['tahun'] ?? '') }}" min="1900" max="2100" class="op-input" placeholder="Pilih Tahun" required>
            </div>
            <!-- Provinsi -->
            <div class="space-y-2 col-span-1">
                <label class="op-label">Provinsi</label>
                <div class="relative">
                    <input list="provinsi-list" name="provinsi" x-model="provinsi" autocomplete="off" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Provinsi" required>
                    <datalist id="provinsi-list">
                        <template x-for="prov in Object.keys(window.daftarWilayah)" :key="prov">
                            <option :value="prov"></option>
                        </template>
                    </datalist>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                        <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Kabupaten / Kota -->
            <div class="space-y-2 col-span-1" x-show="tingkat_wilayah === 'Kabupaten/Kota'">
                <label class="op-label">Kabupaten / Kota</label>
                <div class="relative">
                    <input list="kabupaten-list" name="kabupaten" value="{{ old('kabupaten', $editItem['kabupaten'] ?? '') }}" :required="tingkat_wilayah === 'Kabupaten/Kota'" autocomplete="off" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Kab/Kota">
                    <datalist id="kabupaten-list">
                        <template x-for="kab in listKabupaten" :key="kab">
                            <option :value="kab"></option>
                        </template>
                    </datalist>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                        <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Nilai LQ -->
            <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('nilai_lq', $editItem['nilai_lq'] ?? '') }}' }">
                <label class="op-label">Nilai LQ (Rasio)</label>
                <input type="text" x-model="val" class="op-input" placeholder="Contoh: 1.25" required>
                <input type="hidden" name="nilai_lq" :value="val.replace(/,/g, '.')">
            </div>

            <!-- Nilai SS -->
            <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('nilai_ss', $editItem['nilai_ss'] ?? '') }}' }">
                <label class="op-label">Nilai SS (Dij)</label>
                <input type="text" x-model="val" class="op-input" placeholder="Contoh: 0.8" required>
                <input type="hidden" name="nilai_ss" :value="val.replace(/,/g, '.')">
            </div>
        </div>

        <div class="flex items-center gap-3 mt-6">
            <button type="submit" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                {{ $editItem ? 'Perbarui Data' : 'Hitung' }}
            </button>
            @if($editItem)
                <a href="{{ route('operator.tipologi.index') }}" class="px-6 py-2.5 rounded-xl font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">Batal</a>
            @endif
        </div>
            </form>
        </div>
    </div>

    <!-- Table Container -->
    <div class="op-card !mb-0">
        <div class="op-card-header">
            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 mb-2">Hasil Analisis Tipologi Sektor</h2>
                    <p class="text-slate-600 text-sm">Data Analisis Tipologi Sektor Tersimpan</p>
                </div>
                <form action="{{ route('operator.tipologi.index') }}" method="GET" class="relative w-full md:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Daerah atau Sektor..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:border-[#D8A62A] focus:ring-1 focus:ring-[#D8A62A] outline-none transition-all shadow-sm">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table id="tipologiTable" class="w-full text-left border-collapse min-w-[1200px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-12 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer" onclick="toggleSelectAll(this)">
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-16">No</th>
                            <th class="px-4 py-4 whitespace-nowrap">Daerah Analisis</th>
                            <th class="px-4 py-4 whitespace-nowrap">Provinsi</th>
                            <th class="px-4 py-4 min-w-[200px]">Sektor</th>
                            <th class="px-4 py-4 whitespace-nowrap">Tahun</th>
                            <th class="px-4 py-4 whitespace-nowrap">Nilai SS (Dij)</th>
                            <th class="px-4 py-4 whitespace-nowrap">Nilai LQ (Rasio Kontribusi)</th>
                            <th class="px-4 py-4 whitespace-nowrap text-center">Tipologi (Kuadran)</th>
                            <th class="px-4 py-4 whitespace-nowrap">Riwayat</th>
                            <th class="px-4 py-4 whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($tipologiData as $index => $data)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <input type="checkbox" class="row-checkbox rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 cursor-pointer" value="{{ $data['id'] }}">
                                </td>
                                <td class="px-4 py-4">{{ ($tipologiData->currentPage() - 1) * $tipologiData->perPage() + $loop->iteration }}</td>
                                <td class="px-4 py-4">{{ $data['kabupaten'] ?? $data['daerah_analisis'] ?? '-' }}</td>
                                <td class="px-4 py-4">{{ $data['provinsi'] ?? $data['daerah_pembanding'] ?? '-' }}</td>
                                <td class="px-4 py-4 min-w-[200px]">{{ $data['sektor'] }}</td>
                                <td class="px-4 py-4">{{ $data['tahun'] ?? '-' }}</td>
                                <td class="px-4 py-4">{{ number_format($data['nilai_ss'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-4">{{ number_format($data['nilai_lq'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($data['tipologi'] === 'I')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                            Kuadran I
                                        </span>
                                    @elseif($data['tipologi'] === 'II')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">
                                            Kuadran II
                                        </span>
                                    @elseif($data['tipologi'] === 'III')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200 shadow-sm">
                                            Kuadran III
                                        </span>
                                    @elseif($data['tipologi'] === 'IV')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 shadow-sm">
                                            Kuadran IV
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200 shadow-sm">
                                            {{ $data['tipologi'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $data['riwayat'] ?? '-' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('operator.tipologi.index', ['edit' => $data['id']]) }}" class="p-1.5 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('operator.tipologi.destroy', $data['id']) }}" method="POST" onsubmit="return confirmDelete(event, this);" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-4 py-8 text-center text-slate-500 font-medium">
                                    Belum ada data perhitungan Tipologi Sektor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-slate-500">
                    Menampilkan {{ $tipologiData->firstItem() ?? 0 }}-{{ $tipologiData->lastItem() ?? 0 }} data dari {{ $tipologiData->total() }} data
                </div>
                <div>
                    {{ $tipologiData->links('pagination::tailwind') }}
                </div>
            </div>

            <!-- Legend / Keterangan -->
            <div class="mt-8 border-t border-slate-200 pt-6 text-sm text-slate-800 w-full mt-2">
                <h4 class="font-bold mb-3 text-base">Keterangan :</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-2 gap-y-2 font-medium max-w-3xl">
                    <div class="flex"><span class="w-[85px]">Kuadran I</span> <span class="mr-2">:</span> <span>Sektor cepat maju dan cepat tumbuh</span></div>
                    <div class="flex"><span class="w-[85px]">Kuadran II</span> <span class="mr-2">:</span> <span>Sektor potensial</span></div>
                    <div class="flex"><span class="w-[85px]">Kuadran III</span> <span class="mr-2">:</span> <span>Sektor berkembang</span></div>
                    <div class="flex"><span class="w-[85px]">Kuadran IV</span> <span class="mr-2">:</span> <span>Sektor relatif tertinggal</span></div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3 w-full">
                <form id="bulkDeleteForm" action="{{ route('operator.tipologi.bulkDestroy') }}" method="POST" onsubmit="return confirmBulkDelete(event, this);" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="ids" id="selectedIds">
                    <button type="submit" id="bulkDeleteBtn" class="hidden w-full sm:w-auto flex items-center justify-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Terpilih (<span id="bulkDeleteCount">0</span>)
                    </button>
                </form>

                <button type="button" onclick="exportToExcel()" class="flex items-center justify-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Unduh (Excel)
                </button>

                <form action="{{ route('operator.tipologi.empty') }}" method="POST" onsubmit="return confirmDeleteAll(event, this);" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Semua Data
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-import-modal action="{{ route('operator.tipologi.import') }}" type="tipologi" />

    <!-- Sync Modal -->
    <div id="syncModal" class="fixed inset-0 z-[99] hidden items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all scale-100 opacity-100">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#D8A62A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Tarik Data Database
                </h3>
                <button type="button" onclick="document.getElementById('syncModal').style.display='none'" class="text-slate-400 hover:text-slate-600 transition-colors bg-white rounded-full p-1 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('operator.tipologi.sync') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-slate-600 mb-4">Sistem akan secara otomatis menyinkronkan (mengambil) hasil perhitungan LQ dan SS untuk menghasilkan Kuadran Tipologi.</p>
                <div class="space-y-2">
                    <label class="op-label">Pilih Daerah Analisis</label>
                    <div class="relative">
                        <input list="sync-daerah-list" name="daerah_analisis" class="op-input op-input-icon op-datalist" placeholder="Ketik atau pilih daerah" required>
                        <datalist id="sync-daerah-list">
                            <template x-for="kab in Object.keys(window.daftarWilayah).flatMap(p => window.daftarWilayah[p])" :key="kab">
                                <option :value="kab"></option>
                            </template>
                            <template x-for="prov in Object.keys(window.daftarWilayah)" :key="prov">
                                <option :value="prov"></option>
                            </template>
                        </datalist>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                            <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                                <path d="M7 10l5 5 5-5z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="op-label">Tahun Awal</label>
                        <input type="number" name="tahun_awal" class="op-input" placeholder="Contoh: 2021" required>
                    </div>
                    <div class="space-y-2">
                        <label class="op-label">Tahun Akhir</label>
                        <input type="number" name="tahun_akhir" class="op-input" placeholder="Contoh: 2025" required>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('syncModal').style.display='none'" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex items-center gap-2 bg-[#D8A62A] hover:bg-[#B58A20] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Mulai Sinkronisasi
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    function exportToExcel() {
        var table = document.getElementById("tipologiTable");
        var clone = table.cloneNode(true);
        
        var rows = clone.rows;
        for (var i = 0; i < rows.length; i++) {
            if(rows[i].cells.length > 0) {
                rows[i].deleteCell(-1); // Remove Aksi
                rows[i].deleteCell(0);  // Remove Checkbox
            }
        }
        
        var wb = XLSX.utils.table_to_book(clone, {sheet: "Tipologi Sektor"});
        XLSX.writeFile(wb, "Hasil_Analisis_Tipologi_Sektor.xlsx");
    }
</script>
@endsection
