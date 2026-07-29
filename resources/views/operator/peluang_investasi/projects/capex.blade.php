@extends('partials.layouts.operator')

@section('title', 'Estimasi CAPEX - ' . $project->nama_proyek)
@section('page_heading', 'Estimasi CAPEX Proyek')

@section('content')
<div x-data="capexManager()" x-init="initData()" class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <nav class="flex text-sm text-slate-500 space-x-2">
            <a href="{{ route('operator.projects.index') }}" class="hover:text-[#145239] font-medium">Proyek</a>
            <span>/</span>
            <a href="{{ route('operator.projects.show', $project->id) }}" class="hover:text-[#145239] font-medium truncate max-w-xs">{{ $project->nama_proyek }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Estimasi CAPEX</span>
        </nav>

        <div class="flex items-center gap-2">
            <button @click="toggleMode()" class="px-4 py-2 border border-[#CFE3D5] bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-semibold shadow-xs transition-colors flex items-center gap-2">
                <i class="fa-solid" :class="isPreviewMode ? 'fa-pen-to-square' : 'fa-eye'"></i>
                <span x-text="isPreviewMode ? 'Edit Data' : 'Pratinjau (Preview)'"></span>
            </button>

            <button @click="saveData()" :disabled="isSaving" class="bg-[#145239] hover:bg-[#0B5D3D] text-white px-5 py-2 rounded-xl text-sm font-bold shadow-md transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed gap-2">
                <i x-show="isSaving" class="fa-solid fa-spinner animate-spin"></i>
                <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="toast.show" x-transition class="p-4 rounded-xl shadow-xs flex items-center justify-between border" :class="toast.isSuccess ? 'bg-[#E7F2EB] border-[#CFE3D5] text-[#145239]' : 'bg-rose-50 border-rose-200 text-rose-800'">
        <div class="flex items-center gap-3">
            <i class="fa-solid text-lg" :class="toast.isSuccess ? 'fa-circle-check text-[#145239]' : 'fa-triangle-exclamation text-rose-500'"></i>
            <span class="text-sm font-semibold" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Main Workspace -->
    <div class="bg-white rounded-2xl shadow-xs border border-[#CFE3D5] p-6 overflow-hidden">
        
        <!-- Table Title and Desc -->
        <div class="mb-6 border-b border-slate-100 pb-4">
            <h2 class="text-xl font-bold text-slate-800">Estimasi Biaya Investasi Awal (CAPEX)</h2>
            <p class="text-xs text-slate-500 mt-1">Gunakan formulir di bawah ini untuk mencatat seluruh biaya modal proyek (tanah, bangunan, alat, perizinan, dll).</p>
        </div>

        <!-- Tabel Responsive -->
        <div class="overflow-x-auto border border-[#CFE3D5] rounded-xl pb-6">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-[#145239] text-white border-b border-[#0B5D3D]">
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider min-w-[60px] border-r border-[#0B5D3D] text-white">No</th>
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider min-w-[380px] border-r border-[#0B5D3D] text-white">Nama Komponen</th>
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[120px] border-r border-[#0B5D3D] text-white">Volume</th>
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider text-center min-w-[120px] border-r border-[#0B5D3D] text-white">Satuan</th>
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[130px] border-r border-[#0B5D3D] text-white">Luas (m²)</th>
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[200px] border-r border-[#0B5D3D] text-white">Harga Satuan</th>
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[200px] text-white">Total Harga</th>
                    </tr>
                </thead>
                <template x-for="(parent, parentIndex) in getParents()" :key="parent.temp_id">
                    <tbody>
                        <!-- Parent Row -->
                        <tr class="border-b border-[#CFE3D5] font-bold bg-[#E7F2EB] text-[#145239]">
                            <td class="px-4 py-3 font-mono border-r border-[#CFE3D5]" x-text="parentIndex + 1"></td>
                            <td class="px-4 py-3 border-r border-[#CFE3D5]">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <input x-show="!isPreviewMode" type="text" x-model="parent.nama_komponen" :class="hasError(parent, 'nama_komponen') ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50' : 'border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] shadow-xs'" class="w-full bg-white rounded-xl px-3 py-1.5 text-sm font-bold text-slate-800 transition-all" placeholder="Nama Kategori Utama...">
                                        <span x-show="isPreviewMode" x-text="parent.nama_komponen" class="uppercase"></span>
                                    </div>
                                    <div x-show="!isPreviewMode" class="flex items-center gap-1.5 flex-shrink-0">
                                        <button @click="addChild(parent.temp_id)" title="Tambah Sub-komponen" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white text-[#145239] hover:bg-[#CFE3D5] border border-[#CFE3D5] rounded-xl text-xs font-bold transition-colors shadow-xs">
                                            <i class="fa-solid fa-plus mr-1 text-[10px]"></i>
                                            Sub
                                        </button>
                                        <button @click="removeRow(parent.temp_id)" title="Hapus Kategori" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-semibold transition-colors shadow-xs">
                                            <i class="fa-solid fa-trash-can mr-1 text-[10px]"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <!-- Parent fields read-only -->
                            <td class="px-4 py-3 text-right text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                            <td class="px-4 py-3 text-center text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                            <td class="px-4 py-3 text-right text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                            <td class="px-4 py-3 text-right text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                            <td class="px-4 py-3 text-right text-[#145239] font-bold font-mono text-base" x-text="formatRupiah(getParentTotal(parent.temp_id))"></td>
                        </tr>

                        <!-- Children Rows -->
                        <template x-for="(child, childIndex) in getChildren(parent.temp_id)" :key="child.temp_id">
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                <td class="px-8 py-3 text-slate-500 font-mono border-r border-slate-100 text-xs" x-text="(parentIndex + 1) + '.' + (childIndex + 1)"></td>
                                <td class="px-4 py-3 pl-8 border-r border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1">
                                            <input x-show="!isPreviewMode" type="text" x-model="child.nama_komponen" :class="hasError(child, 'nama_komponen') ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50' : 'border-slate-200 focus:border-[#145239] focus:ring-[#145239]'" class="w-full bg-white rounded-xl px-3 py-1.5 text-sm transition-all" placeholder="Nama Sub-komponen...">
                                            <span x-show="isPreviewMode" x-text="child.nama_komponen" class="text-slate-700 font-semibold"></span>
                                        </div>
                                        <button x-show="!isPreviewMode" @click="removeRow(child.temp_id)" title="Hapus Sub-komponen" class="flex-shrink-0 inline-flex items-center justify-center px-2.5 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-semibold transition-colors shadow-xs">
                                            <i class="fa-solid fa-trash-can mr-1 text-[10px]"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right border-r border-slate-100">
                                    <input x-show="!isPreviewMode" type="number" min="0" x-model.number="child.volume" @input="if(child.volume < 0) child.volume = 0" class="w-full bg-white rounded-xl border-slate-200 focus:border-[#145239] focus:ring-[#145239] px-3 py-1.5 text-sm text-right font-mono transition-all" placeholder="0">
                                    <span x-show="isPreviewMode" x-text="child.volume || 0" class="font-mono text-slate-700"></span>
                                </td>
                                <td class="px-4 py-3 text-center border-r border-slate-100">
                                    <input x-show="!isPreviewMode" type="text" x-model="child.satuan" class="w-full bg-white rounded-xl border-slate-200 focus:border-[#145239] focus:ring-[#145239] px-3 py-1.5 text-sm text-center font-mono transition-all" placeholder="unit/ls/set">
                                    <span x-show="isPreviewMode" x-text="child.satuan || '-'" class="font-mono text-slate-700"></span>
                                </td>
                                <td class="px-4 py-3 text-right border-r border-slate-100">
                                    <input x-show="!isPreviewMode" type="number" min="0" x-model.number="child.luas" @input="if(child.luas < 0) child.luas = 0" class="w-full bg-white rounded-xl border-slate-200 focus:border-[#145239] focus:ring-[#145239] px-3 py-1.5 text-sm text-right font-mono transition-all" placeholder="0">
                                    <span x-show="isPreviewMode" x-text="child.luas || 0" class="font-mono text-slate-700"></span>
                                </td>
                                <td class="px-4 py-3 text-right border-r border-slate-100">
                                    <input x-show="!isPreviewMode" type="number" min="0" x-model.number="child.harga_m2" @input="if(child.harga_m2 < 0) child.harga_m2 = 0" class="w-full bg-white rounded-xl border-slate-200 focus:border-[#145239] focus:ring-[#145239] px-3 py-1.5 text-sm text-right font-mono transition-all" placeholder="0">
                                    <span x-show="isPreviewMode" x-text="formatRupiah(child.harga_m2 || 0)" class="font-mono text-slate-700"></span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-medium text-slate-800 whitespace-nowrap" x-text="formatRupiah(getRowTotal(child))"></td>
                            </tr>
                        </template>
                    </tbody>
                </template>

                <!-- Empty State Table -->
                <template x-if="rows.length === 0">
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                Belum ada data komponen. Silakan tambah kategori utama untuk memulai.
                            </td>
                        </tr>
                    </tbody>
                </template>
            </table>
        </div>

        <!-- Add Category Button -->
        <div x-show="!isPreviewMode" class="mt-6 flex justify-start">
            <button @click="addParent()" class="px-4 py-2 bg-[#E7F2EB] hover:bg-[#CFE3D5] text-[#145239] border border-[#CFE3D5] rounded-xl text-xs font-bold transition-colors flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Kategori Utama</span>
            </button>
        </div>

        <!-- Grand Total CAPEX Section -->
        <div class="mt-8 border-t border-[#CFE3D5] pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h4 class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Total CAPEX Proyek</h4>
                <p class="text-xs text-slate-400 mt-0.5">Penjumlahan otomatis dari seluruh kategori utama di atas.</p>
            </div>
            <div class="bg-[#145239] rounded-2xl border border-[#0B5D3D] px-6 py-4 text-right w-full sm:w-auto shadow-md">
                <span class="block text-xs font-semibold text-white/80 uppercase tracking-wider">TOTAL ESTIMASI CAPEX</span>
                <span class="text-2xl font-black text-[#FFD54F] font-mono mt-1 block" x-text="formatRupiah(getGrandTotal())"></span>
            </div>
        </div>

    </div>
</div>

<script>
    function registerCapex() {
        Alpine.data('capexManager', () => ({
            isPreviewMode: false,
            isSaving: false,
            rows: [],
            errors: {},
            toast: { show: false, message: '', isSuccess: true },

            initData() {
                const dbComponents = @json($components);
                
                if (dbComponents.length > 0) {
                    this.rows = dbComponents.map(c => ({
                        id: c.id,
                        temp_id: 'db_' + c.id,
                        parent_temp_id: c.parent_id ? 'db_' + c.parent_id : null,
                        nama_komponen: c.nama_komponen,
                        volume: c.volume !== null ? parseFloat(c.volume) : null,
                        satuan: c.satuan || '',
                        luas: c.luas !== null ? parseFloat(c.luas) : null,
                        harga_m2: c.harga_m2 !== null ? parseFloat(c.harga_m2) : null
                    }));
                } else {
                    this.rows = [];
                }
            },

            generateTempId() {
                return 'tmp_' + Math.random().toString(36).substr(2, 9);
            },

            getParents() {
                return this.rows.filter(r => !r.parent_temp_id);
            },

            getChildren(parentTempId) {
                return this.rows.filter(r => r.parent_temp_id === parentTempId);
            },

            addParent() {
                this.rows.push({
                    id: null,
                    temp_id: this.generateTempId(),
                    parent_temp_id: null,
                    nama_komponen: '',
                    volume: null,
                    satuan: '',
                    luas: null,
                    harga_m2: null
                });
            },

            addChild(parentTempId) {
                this.rows.push({
                    id: null,
                    temp_id: this.generateTempId(),
                    parent_temp_id: parentTempId,
                    nama_komponen: '',
                    volume: null,
                    satuan: '',
                    luas: null,
                    harga_m2: null
                });
            },

            removeRow(tempId) {
                this.rows = this.rows.filter(r => r.temp_id !== tempId && r.parent_temp_id !== tempId);
            },

            getRowTotal(row) {
                const vol = (row.volume !== null && row.volume > 0) ? row.volume : 1;
                const luas = (row.luas !== null && row.luas > 0) ? row.luas : 0;
                const harga = (row.harga_m2 !== null) ? row.harga_m2 : 0;

                if (luas > 0) {
                    return vol * luas * harga;
                }
                return vol * harga;
            },

            getParentTotal(parentTempId) {
                const children = this.getChildren(parentTempId);
                return children.reduce((sum, child) => sum + this.getRowTotal(child), 0);
            },

            getGrandTotal() {
                const parents = this.getParents();
                return parents.reduce((sum, parent) => sum + this.getParentTotal(parent.temp_id), 0);
            },

            toggleMode() {
                this.isPreviewMode = !this.isPreviewMode;
            },

            formatRupiah(val) {
                if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
                return 'Rp ' + Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },

            hasError(row, field) {
                return this.errors[row.temp_id] && this.errors[row.temp_id].includes(field);
            },

            saveData() {
                this.isSaving = true;
                this.errors = {};

                fetch('{{ route('operator.projects.capex.store', $project->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ components: this.rows })
                })
                .then(res => res.json())
                .then(data => {
                    this.isSaving = false;
                    if(data.success) {
                        this.toast.isSuccess = true;
                        this.toast.message = data.message;
                        this.toast.show = true;
                    } else {
                        alert(data.message || 'Gagal menyimpan data CAPEX.');
                    }
                })
                .catch(() => {
                    this.isSaving = false;
                    alert('Terjadi kesalahan koneksi.');
                });
            }
        }));
    }

    if (window.Alpine) {
        registerCapex();
    } else {
        document.addEventListener('alpine:init', registerCapex);
    }
</script>
@endsection
