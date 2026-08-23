@extends('partials.layouts.operator')

@section('title', 'Estimasi CAPEX - ' . $project->nama_proyek)
@section('page_heading', 'Estimasi CAPEX Proyek')

@section('content')
<div x-data="capexManager()" x-init="initData()" class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3.5 sm:gap-4">
        <nav class="flex flex-wrap text-xs sm:text-sm text-slate-500 gap-1.5 items-center">
            <a href="{{ route('operator.projects.index') }}" class="hover:text-[#145239] font-medium">Proyek</a>
            <span>/</span>
            <a href="{{ route('operator.projects.show', $project->id) }}" class="hover:text-[#145239] font-medium truncate max-w-[150px] sm:max-w-xs">{{ $project->nama_proyek }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Estimasi CAPEX</span>
        </nav>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <!-- Indicator Autosave & Countdown -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs" :class="{
                'bg-slate-100 text-slate-600 border border-slate-200': autoSaveStatus === 'saving',
                'bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5]': autoSaveStatus === 'saved',
                'bg-amber-50 text-amber-700 border border-amber-200': autoSaveStatus === 'draft',
                'bg-slate-50 text-slate-600 border border-slate-200': !autoSaveStatus
            }">
                <template x-if="autoSaveStatus === 'saving'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-spinner animate-spin text-[#145239]"></i>
                        <span>Menyimpan otomatis...</span>
                    </span>
                </template>
                <template x-if="autoSaveStatus === 'saved'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up text-[#145239]"></i>
                        <span x-text="'Tersimpan ' + (lastSavedTime ? lastSavedTime + ' • ' : '') + 'Autosave DB (' + formattedCountdown + ')'"></span>
                    </span>
                </template>
                <template x-if="autoSaveStatus === 'draft'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-amber-600"></i>
                        <span x-text="'Draft lokal • Autosave DB (' + formattedCountdown + ')'"></span>
                    </span>
                </template>
                <template x-if="!autoSaveStatus">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-clock text-slate-500"></i>
                        <span x-text="'Autosave DB (' + formattedCountdown + ')'"></span>
                    </span>
                </template>
            </div>

            <button @click="toggleMode()" class="flex-1 sm:flex-initial px-4 py-2 border border-[#CFE3D5] bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold shadow-xs transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid" :class="isPreviewMode ? 'fa-pen-to-square' : 'fa-eye'"></i>
                <span x-text="isPreviewMode ? 'Edit Data' : 'Pratinjau'"></span>
            </button>

            <button @click="exportToExcel()" class="flex-1 sm:flex-initial bg-[#1F497D] hover:bg-[#16355B] text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-md transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-file-excel text-emerald-400"></i>
                <span>Export Excel</span>
            </button>

            <button @click="saveData()" :disabled="isSaving" class="flex-1 sm:flex-initial bg-[#145239] hover:bg-[#0B5D3D] text-white px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-md transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed gap-2">
                <i x-show="isSaving" class="fa-solid fa-spinner animate-spin"></i>
                <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                <span>Simpan</span>
            </button>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="toast.show" x-transition class="p-4 rounded-xl shadow-xs flex items-center justify-between border" :class="toast.isSuccess ? 'bg-[#E7F2EB] border-[#CFE3D5] text-[#145239]' : 'bg-rose-50 border-rose-200 text-rose-800'">
        <div class="flex items-center gap-3">
            <i class="fa-solid text-lg" :class="toast.isSuccess ? 'fa-circle-check text-[#145239]' : 'fa-triangle-exclamation text-rose-500'"></i>
            <span class="text-xs sm:text-sm font-semibold" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Main Workspace -->
    <div class="bg-white rounded-2xl shadow-xs border border-[#CFE3D5] p-4 sm:p-5 md:p-6 overflow-hidden">

        <!-- Table Title and Desc -->
        <div class="mb-6 border-b border-slate-100 pb-4">
            <h2 class="text-lg sm:text-xl font-bold text-slate-800">Estimasi Biaya Investasi Awal (CAPEX)</h2>
            <p class="text-xs text-slate-500 mt-1">Gunakan formulir di bawah ini untuk mencatat seluruh biaya modal proyek (tanah, bangunan, alat, perizinan, dll).</p>
        </div>

        <!-- Tombol Tambah Dinamis -->
        <div x-show="!isPreviewMode" class="flex flex-wrap gap-3 mb-4">
            <button @click="addParent()" class="px-4 py-2 bg-[#E7F2EB] border border-[#CFE3D5] hover:bg-[#CFE3D5] text-[#145239] rounded-xl text-xs font-bold transition-colors shadow-xs flex items-center gap-2">
                <span>+ Kategori Utama</span>
            </button>
        </div>

        <!-- Tabel Responsive -->
        <div class="w-full overflow-x-auto border border-[#CFE3D5] rounded-xl pb-6">
            <table class="w-full min-w-[900px] text-xs sm:text-sm text-left border-collapse">
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
                                        <span x-show="parentIndex === 0 || parentIndex === getParents().length - 1" class="text-[10px] text-[#145239] bg-white/80 px-2 py-1 rounded-xl border border-[#CFE3D5] font-semibold shrink-0">Kategori Default</span>
                                        <button @click="addChild(parent.temp_id)" title="Tambah Sub-komponen" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white text-[#145239] hover:bg-[#CFE3D5] border border-[#CFE3D5] rounded-xl text-xs font-bold transition-colors shadow-xs">
                                            <i class="fa-solid fa-plus mr-1 text-[10px]"></i>
                                            Sub
                                        </button>
                                        <button x-show="parentIndex > 0 && parentIndex < getParents().length - 1" @click="removeRow(parent.temp_id)" title="Hapus Kategori" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-semibold transition-colors shadow-xs">
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

    <!-- MODAL KONFIRMASI PERUBAHAN BELUM DISIMPAN -->
    <x-confirm-unsaved-modal />
</div>
</div>

<script>
    function registerCapex() {
        Alpine.data('capexManager', () => ({
            isPreviewMode: false,
            isSaving: false,
            rows: [],
            errors: {},
            toast: {
                show: false,
                message: '',
                isSuccess: true
            },
            hasUnsavedChanges: false,
            showLeaveModal: false,
            pendingNavigationUrl: null,
            isGuardPushed: false,
            autoSaveStatus: '',
            lastSavedTime: '',
            countdownSeconds: 300,
            countdownInterval: null,
            localDraftTimeout: null,
            storageKey: 'capex_draft_' + {{ $project->id }},

            get formattedCountdown() {
                const m = Math.floor(this.countdownSeconds / 60);
                const s = this.countdownSeconds % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            },

            initData() {
                const dbComponents = @json($components);
                let loadedFromDraft = false;

                const localDraft = localStorage.getItem(this.storageKey);
                if (localDraft) {
                    try {
                        const parsed = JSON.parse(localDraft);
                        if (parsed && Array.isArray(parsed.rows) && parsed.rows.length > 0) {
                            this.rows = parsed.rows;
                            this.hasUnsavedChanges = true;
                            this.autoSaveStatus = 'draft';
                            this.lastSavedTime = parsed.time || '';
                            loadedFromDraft = true;
                        }
                    } catch (e) {}
                }

                if (!loadedFromDraft) {
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
                }

                // Enforce minimum 2 default parent categories
                let parents = this.getParents();
                if (parents.length < 1) {
                    this.rows.unshift({
                        id: null,
                        temp_id: 'temp_def_1',
                        parent_temp_id: null,
                        nama_komponen: 'Persiapan',
                        volume: null,
                        satuan: '',
                        luas: null,
                        harga_m2: null
                    });
                }
                parents = this.getParents();
                if (parents.length < 2) {
                    this.rows.push({
                        id: null,
                        temp_id: 'temp_def_2',
                        parent_temp_id: null,
                        nama_komponen: 'Fasilitas Service dan Pendukung',
                        volume: null,
                        satuan: '',
                        luas: null,
                        harga_m2: null
                    });
                }

                this.normalizeRowsOrder();

                if (this.hasUnsavedChanges) {
                    this.pushHistoryGuard();
                }

                this.$watch('rows', () => {
                    this.triggerAutoSave();
                });

                this.startCountdownTimer();
                this.setupNavigationInterception();
            },

            startCountdownTimer() {
                if (this.countdownInterval) clearInterval(this.countdownInterval);
                this.countdownSeconds = 300;
                this.countdownInterval = setInterval(() => {
                    if (this.countdownSeconds > 0) {
                        this.countdownSeconds--;
                    } else {
                        if (this.hasUnsavedChanges) {
                            this.autoSaveToServer();
                        }
                        this.countdownSeconds = 300;
                    }
                }, 1000);
            },

            pushHistoryGuard() {
                if (!this.isGuardPushed) {
                    try {
                        history.pushState({
                            unsavedGuard: true
                        }, '', window.location.href);
                        this.isGuardPushed = true;
                    } catch (e) {}
                }
            },

            setupNavigationInterception() {
                window.addEventListener('beforeunload', (e) => {
                    if (this.hasUnsavedChanges) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });

                window.addEventListener('popstate', (e) => {
                    if (this.hasUnsavedChanges) {
                        try {
                            history.pushState({
                                unsavedGuard: true
                            }, '', window.location.href);
                        } catch (err) {}
                        this.pendingNavigationUrl = 'BACK_NAVIGATION';
                        this.showLeaveModal = true;
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!this.hasUnsavedChanges) return;

                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');

                    if (!href ||
                        href.startsWith('#') ||
                        href.startsWith('javascript:') ||
                        href.startsWith('mailto:') ||
                        href.startsWith('tel:') ||
                        target === '_blank' ||
                        e.ctrlKey ||
                        e.metaKey ||
                        link.hasAttribute('download')) {
                        return;
                    }

                    if (link.href === window.location.href) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();
                    this.pendingNavigationUrl = link.href;
                    this.showLeaveModal = true;
                }, true);
            },

            triggerAutoSave() {
                this.hasUnsavedChanges = true;
                this.pushHistoryGuard();
                clearTimeout(this.localDraftTimeout);
                this.localDraftTimeout = setTimeout(() => {
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    localStorage.setItem(this.storageKey, JSON.stringify({
                        rows: this.rows,
                        time: timeStr
                    }));
                    if (this.autoSaveStatus !== 'saving') {
                        this.autoSaveStatus = 'draft';
                    }
                }, 400);
            },

            async autoSaveToServer(force = false) {
                if (!force && !this.hasUnsavedChanges) return true;
                this.autoSaveStatus = 'saving';

                try {
                    const res = await fetch('{{ route('operator.projects.capex.store', $project->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                components: this.rows
                            })
                        });
                    const data = await res.json();
                    if (data.success) {
                        const now = new Date();
                        this.lastSavedTime = now.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        this.autoSaveStatus = 'saved';
                        this.hasUnsavedChanges = false;
                        this.isGuardPushed = false;
                        this.countdownSeconds = 300;
                        localStorage.removeItem(this.storageKey);
                        return true;
                    } else {
                        this.autoSaveStatus = 'draft';
                        return false;
                    }
                } catch (e) {
                    this.autoSaveStatus = 'draft';
                    return false;
                }
            },

            async saveAndLeave() {
                const isBackNav = (this.pendingNavigationUrl === 'BACK_NAVIGATION');
                const targetUrl = this.pendingNavigationUrl;

                const success = await this.autoSaveToServer(true);
                if (success) {
                    this.hasUnsavedChanges = false;
                    this.isGuardPushed = false;
                    localStorage.removeItem(this.storageKey);
                    this.showLeaveModal = false;

                    if (isBackNav) {
                        window.history.go(-2);
                    } else if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                } else {
                    alert('Gagal menyimpan data ke database. Silakan coba lagi.');
                }
            },

            discardAndLeave() {
                const isBackNav = (this.pendingNavigationUrl === 'BACK_NAVIGATION');
                const targetUrl = this.pendingNavigationUrl;

                this.hasUnsavedChanges = false;
                this.isGuardPushed = false;
                localStorage.removeItem(this.storageKey);
                this.showLeaveModal = false;

                if (isBackNav) {
                    window.history.go(-2);
                } else if (targetUrl) {
                    window.location.href = targetUrl;
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

            normalizeRowsOrder() {
                const parents = this.getParents();
                if (parents.length === 0) return;

                const firstParent = parents[0];
                let lastParent = null;

                let lastParentIdx = parents.findIndex(p =>
                    p.temp_id === 'temp_def_2' ||
                    (p.nama_komponen && p.nama_komponen.toLowerCase().includes('fasilitas service'))
                );

                if (lastParentIdx === -1 && parents.length > 1) {
                    lastParentIdx = parents.length - 1;
                }

                if (lastParentIdx !== -1 && parents.length > 1) {
                    lastParent = parents[lastParentIdx];
                }

                const middleParents = parents.filter(p => p !== firstParent && p !== lastParent);

                let orderedParents = [firstParent];
                middleParents.forEach(p => orderedParents.push(p));
                if (lastParent) {
                    orderedParents.push(lastParent);
                }

                let newRows = [];
                orderedParents.forEach(p => {
                    newRows.push(p);
                    const children = this.rows.filter(r => r.parent_temp_id === p.temp_id);
                    children.forEach(c => newRows.push(c));
                });

                this.rows = newRows;
            },

            addParent() {
                const parents = this.getParents();
                const newParent = {
                    id: null,
                    temp_id: this.generateTempId(),
                    parent_temp_id: null,
                    nama_komponen: '',
                    volume: null,
                    satuan: '',
                    luas: null,
                    harga_m2: null
                };

                if (parents.length >= 2) {
                    const lastParent = parents[parents.length - 1];
                    const lastParentIdx = this.rows.findIndex(r => r.temp_id === lastParent.temp_id);
                    this.rows.splice(lastParentIdx, 0, newParent);
                } else {
                    this.rows.push(newParent);
                }
                this.normalizeRowsOrder();
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

            async saveData() {
                this.isSaving = true;
                this.errors = {};
                const success = await this.autoSaveToServer(true);
                this.isSaving = false;
                if (success) {
                    this.toast.isSuccess = true;
                    this.toast.message = 'Data CAPEX berhasil disimpan ke database.';
                    this.toast.show = true;
                } else {
                    alert('Gagal menyimpan data CAPEX.');
                }
            },

            async exportToExcel() {
                if (typeof ExcelJS === 'undefined') {
                    alert('Library ExcelJS belum siap. Silakan periksa koneksi internet Anda.');
                    return;
                }

                const wb = new ExcelJS.Workbook();
                wb.creator = 'DPMPTSP';
                const ws = wb.addWorksheet('Estimasi CAPEX');

                const COLOR_HEADER_BG = 'FF1F497D'; // Biru Tua Header
                const COLOR_HEADER_TEXT = 'FFFFFFFF'; // Putih
                const COLOR_PARENT_BG = 'FFD9E1F2'; // Biru Muda Kategori Utama
                const COLOR_PARENT_TEXT = 'FF0F2C59'; // Biru Tua Teks
                const COLOR_TOTAL_BG = 'FF1F497D'; // Biru Tua Total
                const COLOR_TOTAL_TEXT = 'FFFFD54F'; // Kuning Emas
                const COLOR_BORDER = 'FFD9D9D9';

                ws.columns = [{
                        header: '',
                        key: 'colA',
                        width: 10
                    },
                    {
                        header: '',
                        key: 'colB',
                        width: 45
                    },
                    {
                        header: '',
                        key: 'colC',
                        width: 16
                    },
                    {
                        header: '',
                        key: 'colD',
                        width: 14
                    },
                    {
                        header: '',
                        key: 'colE',
                        width: 18
                    },
                    {
                        header: '',
                        key: 'colF',
                        width: 24
                    },
                    {
                        header: '',
                        key: 'colG',
                        width: 28
                    },
                ];

                ws.mergeCells('A1:G1');
                const titleCell = ws.getCell('A1');
                titleCell.value = 'ESTIMASI BIAYA INVESTASI AWAL (CAPEX)';
                titleCell.font = {
                    name: 'Calibri',
                    size: 14,
                    bold: true,
                    color: {
                        argb: 'FF1F497D'
                    }
                };
                titleCell.alignment = {
                    vertical: 'middle',
                    horizontal: 'left'
                };

                ws.mergeCells('A2:G2');
                const subTitleCell = ws.getCell('A2');
                subTitleCell.value = `Nama Proyek: ${@json($project->nama_proyek)}`;
                subTitleCell.font = {
                    name: 'Calibri',
                    size: 11,
                    italic: true,
                    color: {
                        argb: 'FF475569'
                    }
                };
                subTitleCell.alignment = {
                    vertical: 'middle',
                    horizontal: 'left'
                };

                ws.addRow([]);

                const headerTitles = ['No', 'Nama Komponen', 'Volume', 'Satuan', 'Luas (m²)', 'Harga Satuan (Rp)', 'Total Harga (Rp)'];
                const headerRow = ws.addRow(headerTitles);
                headerRow.height = 28;

                headerRow.eachCell((cell, colNumber) => {
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: COLOR_HEADER_BG
                        }
                    };
                    cell.font = {
                        name: 'Calibri',
                        size: 11,
                        bold: true,
                        color: {
                            argb: COLOR_HEADER_TEXT
                        }
                    };
                    cell.alignment = {
                        vertical: 'middle',
                        horizontal: (colNumber === 1 || colNumber === 4) ? 'center' : (colNumber === 2 ? 'left' : 'right'),
                        wrapText: true
                    };
                    cell.border = {
                        top: {
                            style: 'thin',
                            color: {
                                argb: COLOR_BORDER
                            }
                        },
                        left: {
                            style: 'thin',
                            color: {
                                argb: COLOR_BORDER
                            }
                        },
                        bottom: {
                            style: 'medium',
                            color: {
                                argb: COLOR_HEADER_BG
                            }
                        },
                        right: {
                            style: 'thin',
                            color: {
                                argb: COLOR_BORDER
                            }
                        }
                    };
                });

                const parents = this.getParents();
                parents.forEach((parent, parentIdx) => {
                    const parentTotal = this.getParentTotal(parent.temp_id);
                    const parentRow = ws.addRow([
                        parentIdx + 1,
                        (parent.nama_komponen || '').toUpperCase(),
                        '-',
                        '-',
                        '-',
                        '-',
                        parentTotal
                    ]);
                    parentRow.height = 24;

                    parentRow.eachCell((cell, colNumber) => {
                        cell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: {
                                argb: COLOR_PARENT_BG
                            }
                        };
                        cell.font = {
                            name: 'Calibri',
                            size: 11,
                            bold: true,
                            color: {
                                argb: COLOR_PARENT_TEXT
                            }
                        };
                        cell.alignment = {
                            vertical: 'middle',
                            horizontal: (colNumber === 1 || colNumber === 4) ? 'center' : (colNumber === 2 ? 'left' : 'right')
                        };
                        cell.border = {
                            top: {
                                style: 'thin',
                                color: {
                                    argb: COLOR_BORDER
                                }
                            },
                            left: {
                                style: 'thin',
                                color: {
                                    argb: COLOR_BORDER
                                }
                            },
                            bottom: {
                                style: 'thin',
                                color: {
                                    argb: COLOR_BORDER
                                }
                            },
                            right: {
                                style: 'thin',
                                color: {
                                    argb: COLOR_BORDER
                                }
                            }
                        };

                        if (colNumber === 7) {
                            cell.numFmt = '#,##0;(#,##0);"-"';
                        }
                    });

                    const children = this.getChildren(parent.temp_id);
                    children.forEach((child, childIdx) => {
                        const childTotal = this.getRowTotal(child);
                        const childRow = ws.addRow([
                            `${parentIdx + 1}.${childIdx + 1}`,
                            child.nama_komponen || '',
                            child.volume !== null && child.volume !== undefined ? Number(child.volume) : 0,
                            child.satuan || '-',
                            child.luas !== null && child.luas !== undefined ? Number(child.luas) : 0,
                            child.harga_m2 !== null && child.harga_m2 !== undefined ? Number(child.harga_m2) : 0,
                            childTotal
                        ]);
                        childRow.height = 20;

                        childRow.eachCell((cell, colNumber) => {
                            cell.font = {
                                name: 'Calibri',
                                size: 10,
                                color: {
                                    argb: 'FF1E293B'
                                }
                            };
                            cell.alignment = {
                                vertical: 'middle',
                                horizontal: (colNumber === 1 || colNumber === 4) ? 'center' : (colNumber === 2 ? 'left' : 'right')
                            };
                            cell.border = {
                                top: {
                                    style: 'thin',
                                    color: {
                                        argb: COLOR_BORDER
                                    }
                                },
                                left: {
                                    style: 'thin',
                                    color: {
                                        argb: COLOR_BORDER
                                    }
                                },
                                bottom: {
                                    style: 'thin',
                                    color: {
                                        argb: COLOR_BORDER
                                    }
                                },
                                right: {
                                    style: 'thin',
                                    color: {
                                        argb: COLOR_BORDER
                                    }
                                }
                            };

                            if (colNumber === 3 || colNumber === 5 || colNumber === 6 || colNumber === 7) {
                                cell.numFmt = '#,##0;(#,##0);"-"';
                            }
                        });
                    });
                });

                const grandTotal = this.getGrandTotal();
                const totalRow = ws.addRow([
                    '',
                    'TOTAL ESTIMASI CAPEX',
                    '',
                    '',
                    '',
                    '',
                    grandTotal
                ]);
                totalRow.height = 28;

                ws.mergeCells(`A${totalRow.number}:F${totalRow.number}`);

                totalRow.eachCell((cell, colNumber) => {
                    cell.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: COLOR_TOTAL_BG
                        }
                    };
                    cell.font = {
                        name: 'Calibri',
                        size: 12,
                        bold: true,
                        color: {
                            argb: COLOR_TOTAL_TEXT
                        }
                    };
                    cell.alignment = {
                        vertical: 'middle',
                        horizontal: colNumber === 7 ? 'right' : 'center'
                    };
                    cell.border = {
                        top: {
                            style: 'medium',
                            color: {
                                argb: COLOR_TOTAL_BG
                            }
                        },
                        left: {
                            style: 'thin',
                            color: {
                                argb: COLOR_BORDER
                            }
                        },
                        bottom: {
                            style: 'double',
                            color: {
                                argb: COLOR_TOTAL_BG
                            }
                        },
                        right: {
                            style: 'thin',
                            color: {
                                argb: COLOR_BORDER
                            }
                        }
                    };

                    if (colNumber === 7) {
                        cell.numFmt = '#,##0;(#,##0);"-"';
                    }
                });

                const buffer = await wb.xlsx.writeBuffer();
                const blob = new Blob([buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                const sanitizeName = @json(Str::slug($project->nama_proyek));
                link.download = `Estimasi_CAPEX_${sanitizeName}.xlsx`;
                link.click();
                URL.revokeObjectURL(link.href);
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