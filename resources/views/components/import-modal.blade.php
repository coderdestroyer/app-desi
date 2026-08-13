@props(['action', 'type' => 'master'])

<div id="importModal" class="fixed inset-0 w-screen h-screen z-[99999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4" style="display: none;">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Unggah Data Analisis</h3>
            <button type="button" onclick="document.getElementById('importModal').style.display='none'" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-slate-600">Pastikan format kolom tabel Excel yang Anda unggah sesuai dengan ketentuan agar sistem dapat memproses datanya.</p>
            <button type="button" onclick="downloadTemplate('{{ $type }}')" class="op-btn-outline">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                1. Unduh Template Excel
            </button>
            
            <div class="border-t border-slate-200 pt-4 mt-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2">2. Pilih File Excel (.xlsx)</label>
                <input type="file" id="excelFileInput" accept=".xlsx, .xls" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
            </div>
            <div id="importStatus" class="text-sm font-medium mt-2 hidden"></div>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('importModal').style.display='none'" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">Batal</button>
            <button type="button" onclick="processImport()" id="processBtn" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">Mulai Unggah</button>
        </div>
    </div>
</div>

<!-- Load SheetJS only if it hasn't been loaded -->
@once
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script>
    function downloadTemplate(type) {
        let wsData = [];
        let filename = "Template_Analisis.xlsx";
        let colCount = 13;

        if (type === 'lq') {
            wsData = [
                [
                    "Provinsi", "Kabupaten/Kota", "Sektor", "Tahun", 
                    "PDRB Sektor Analisis", "Total PDRB Analisis", 
                    "PDRB Sektor Pembanding", "Total PDRB Pembanding"
                ],
                [
                    "Sumatera Utara", "Medan", "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "2022", 
                    "16000", "110000", "52000", "210000"
                ]
            ];
            filename = "Template_Analisis_LQ.xlsx";
            colCount = 8;
        } else if (type === 'ss') {
            wsData = [
                [
                    "Provinsi", "Kabupaten/Kota", "Sektor", "Tahun Awal", "Tahun Akhir", 
                    "PDRB Sektor Analisis Awal", "PDRB Sektor Analisis Akhir", 
                    "PDRB Sektor Pembanding Awal", "PDRB Sektor Pembanding Akhir", 
                    "Total PDRB Pembanding Awal", "Total PDRB Pembanding Akhir"
                ],
                [
                    "Sumatera Utara", "Medan", "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "2021", "2022", 
                    "15000", "16000", "50000", "52000", "200000", "210000"
                ]
            ];
            filename = "Template_Analisis_SS.xlsx";
            colCount = 11;
        } else if (type === 'tipologi') {
            wsData = [
                [
                    "Provinsi", "Kabupaten/Kota", "Sektor", "Tahun", 
                    "Nilai LQ", "Nilai SS"
                ],
                [
                    "Sumatera Utara", "Medan", "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "2022", 
                    "1.25", "0.80"
                ]
            ];
            filename = "Template_Analisis_Tipologi.xlsx";
            colCount = 6;
        } else if (type === 'klassen') {
            wsData = [
                [
                    "Provinsi", "Kabupaten/Kota", "Sektor", "Tahun", 
                    "PDRB Sektor", "Total PDRB", 
                    "PDRB Sektor Pembanding", "Total PDRB Pembanding"
                ],
                [
                    "Sumatera Utara", "Medan", "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "2021", 
                    "15000", "100000", "50000", "200000"
                ],
                [
                    "Sumatera Utara", "Medan", "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "2022", 
                    "16000", "110000", "52000", "210000"
                ]
            ];
            filename = "Template_Analisis_Klassen.xlsx";
            colCount = 8;
        } else {
            // Master
            wsData = [
                [
                    "Provinsi", "Kabupaten/Kota", "Sektor", "Tahun Awal", "Tahun Akhir", 
                    "PDRB Sektor Analisis Awal", "PDRB Sektor Analisis Akhir", 
                    "Total PDRB Analisis Awal", "Total PDRB Analisis Akhir", 
                    "PDRB Sektor Pembanding Awal", "PDRB Sektor Pembanding Akhir", 
                    "Total PDRB Pembanding Awal", "Total PDRB Pembanding Akhir"
                ],
                [
                    "Sumatera Utara", "Medan", "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "2021", "2022", 
                    "15000", "16000", "100000", "110000", "50000", "52000", "200000", "210000"
                ]
            ];
            filename = "Template_Master_Analisis.xlsx";
            colCount = 13;
        }

        const ws = XLSX.utils.aoa_to_sheet(wsData);
        // Set column widths
        ws['!cols'] = Array(colCount).fill({wch: 25});
        
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Template");
        XLSX.writeFile(wb, filename);
    }

    async function processImport() {
        const type = '{{ $type }}';
        const fileInput = document.getElementById('excelFileInput');
        const statusEl = document.getElementById('importStatus');
        const processBtn = document.getElementById('processBtn');
        
        if (!fileInput.files.length) {
            statusEl.textContent = 'Silakan pilih file terlebih dahulu!';
            statusEl.className = 'text-sm font-medium mt-2 text-red-600 block';
            return;
        }

        processBtn.disabled = true;
        processBtn.textContent = 'Memproses...';
        statusEl.textContent = 'Membaca file Excel...';
        statusEl.className = 'text-sm font-medium mt-2 text-emerald-600 block';

        const file = fileInput.files[0];
        const reader = new FileReader();

        reader.onload = async function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                let jsonData = [];
                
                // Temukan nama sheet secara case-insensitive & mengabaikan spasi/pemisah
                const findSheetName = (target) => {
                    const cleanTarget = target.toLowerCase().replace(/[^a-z0-9]/g, '');
                    return workbook.SheetNames.find(s => s.toLowerCase().replace(/[^a-z0-9]/g, '') === cleanTarget);
                };
                
                const sKab = findSheetName('kabupaten');
                const sProv = findSheetName('provinsi');
                const sSektor = findSheetName('sektor');
                const sPdrbKab = findSheetName('pdrb_kabupaten') || findSheetName('pdrbkabupaten');
                const sPdrbSumut = findSheetName('pdrb_sumut') || findSheetName('pdrbsumut') || findSheetName('pdrbprovinsi') || findSheetName('pdrb_provinsi');
                
                if (sPdrbKab && sPdrbSumut) {
                    statusEl.textContent = 'Membaca sheet relational database...';
                    
                    const rawKab = XLSX.utils.sheet_to_json(workbook.Sheets[sKab] || {});
                    const rawProv = XLSX.utils.sheet_to_json(workbook.Sheets[sProv] || {});
                    const rawSektor = XLSX.utils.sheet_to_json(workbook.Sheets[sSektor] || {});
                    const rawPdrbKab = XLSX.utils.sheet_to_json(workbook.Sheets[sPdrbKab] || {});
                    const rawPdrbSumut = XLSX.utils.sheet_to_json(workbook.Sheets[sPdrbSumut] || {});
                    
                    if (!rawKab.length || !rawSektor.length || !rawPdrbKab.length || !rawPdrbSumut.length) {
                        throw new Error('Format relational multi-sheet tidak lengkap atau kosong.');
                    }
                    
                    // Buat map pencarian
                    const kabMap = {};
                    rawKab.forEach(k => {
                        const id = String(k.kab_id || '').trim();
                        kabMap[id] = String(k.nama_kabupaten || '').trim();
                    });
                    
                    const provMap = {};
                    rawProv.forEach(p => {
                        const id = String(p.provinsi_id || '').trim();
                        provMap[id] = String(p.nama_provinsi || '').trim();
                    });
                    
                    const sektorMap = {};
                    rawSektor.forEach(s => {
                        const id = String(s.sektor_id || '').trim();
                        sektorMap[id] = String(s.nama_sektor || '').trim();
                    });
                    
                    // Cari daftar tahun yang tersedia
                    const tahunList = [...new Set(rawPdrbKab.map(d => parseInt(d.tahun)))].filter(t => !isNaN(t)).sort((a,b) => a-b);
                    if (tahunList.length < 2) {
                        throw new Error('Data PDRB harus memiliki minimal 2 tahun data untuk analisis awal & akhir.');
                    }
                    const tahunAwal = tahunList[0];
                    const tahunAkhir = tahunList[tahunList.length - 1];
                    
                    statusEl.textContent = `Menghitung total PDRB & menggabungkan data (${tahunAwal} & ${tahunAkhir})...`;
                    
                    // Hitung total PDRB per kabupaten per tahun
                    const totalKab = {};
                    rawPdrbKab.forEach(d => {
                        const kid = String(d.kab_id || '').trim();
                        const th = parseInt(d.tahun);
                        if (!totalKab[kid]) totalKab[kid] = {};
                        if (!totalKab[kid][th]) totalKab[kid][th] = 0;
                        
                        // Parse nilai (hilangkan titik pemisah ribuan jika ada)
                        let valStr = String(d.nilai || '0').replace(/\./g, '').replace(/,/g, '.');
                        totalKab[kid][th] += parseFloat(valStr || 0);
                    });
                    
                    // Hitung total PDRB per provinsi per tahun
                    const totalProv = {};
                    rawPdrbSumut.forEach(d => {
                        const pid = String(d.provinsi_id || '').trim();
                        const th = parseInt(d.tahun);
                        if (!totalProv[pid]) totalProv[pid] = {};
                        if (!totalProv[pid][th]) totalProv[pid][th] = 0;
                        
                        let valStr = String(d.nilai || '0').replace(/\./g, '').replace(/,/g, '.');
                        totalProv[pid][th] += parseFloat(valStr || 0);
                    });
                    
                    // Gabungkan data
                    const combinedRows = {};
                    const klassenRows = [];
                    
                    rawPdrbKab.forEach(d => {
                        const kid = String(d.kab_id || '').trim();
                        const sid = String(d.sektor_id || '').trim();
                        const th = parseInt(d.tahun);
                        const provId = kid.split('.')[0];
                        
                        let valStr = String(d.nilai || '0').replace(/\./g, '').replace(/,/g, '.');
                        const nilaiKab = parseFloat(valStr || 0);
                        
                        // Populate Klassen specific array (all years)
                        if (type === 'klassen' && tahunList.includes(th)) {
                            klassenRows.push({
                                'Provinsi': provMap[provId] || 'SUMATERA UTARA',
                                'Kabupaten/Kota': kabMap[kid] || '-',
                                'Sektor': sektorMap[sid] || '-',
                                'Tahun': th,
                                'PDRB Sektor': nilaiKab,
                                'Total PDRB': totalKab[kid]?.[th] || 0,
                                'PDRB Sektor Pembanding': 0, // filled later
                                'Total PDRB Pembanding': totalProv[provId]?.[th] || 0
                            });
                        }
                        
                        if (th !== tahunAwal && th !== tahunAkhir) return; // Hanya ambil awal & akhir untuk yang lain
                        
                        const key = `${kid}_${sid}`;
                        if (!combinedRows[key]) {
                            combinedRows[key] = {
                                'Provinsi': provMap[provId] || 'SUMATERA UTARA',
                                'Kabupaten/Kota': kabMap[kid] || '-',
                                'Sektor': sektorMap[sid] || '-',
                                'Tahun Awal': tahunAwal,
                                'Tahun Akhir': tahunAkhir,
                                'PDRB Sektor Analisis Awal': 0,
                                'PDRB Sektor Analisis Akhir': 0,
                                'Total PDRB Analisis Awal': totalKab[kid]?.[tahunAwal] || 0,
                                'Total PDRB Analisis Akhir': totalKab[kid]?.[tahunAkhir] || 0,
                                'PDRB Sektor Pembanding Awal': 0,
                                'PDRB Sektor Pembanding Akhir': 0,
                                'Total PDRB Pembanding Awal': totalProv[provId]?.[tahunAwal] || 0,
                                'Total PDRB Pembanding Akhir': totalProv[provId]?.[tahunAkhir] || 0
                            };
                        }
                        
                        if (th === tahunAwal) {
                            combinedRows[key]['PDRB Sektor Analisis Awal'] = nilaiKab;
                        } else if (th === tahunAkhir) {
                            combinedRows[key]['PDRB Sektor Analisis Akhir'] = nilaiKab;
                        }
                    });
                    
                    // Isi nilai pembanding (Provinsi)
                    rawPdrbSumut.forEach(d => {
                        const pid = String(d.provinsi_id || '').trim();
                        const sid = String(d.sektor_id || '').trim();
                        const th = parseInt(d.tahun);
                        let valStr = String(d.nilai || '0').replace(/\./g, '').replace(/,/g, '.');
                        const nilaiProv = parseFloat(valStr || 0);
                        
                        if (type === 'klassen' && tahunList.includes(th)) {
                            // Find matching klassenRow
                            klassenRows.forEach(row => {
                                const provId = Object.keys(provMap).find(key => provMap[key] === row['Provinsi']) || pid;
                                const rowSid = Object.keys(sektorMap).find(key => sektorMap[key] === row['Sektor']) || sid;
                                
                                if (row.Tahun === th && rowSid === sid && provId === pid) {
                                    row['PDRB Sektor Pembanding'] = nilaiProv;
                                }
                            });
                        }
                        
                        if (th !== tahunAwal && th !== tahunAkhir) return;
                        
                        // Cari baris combined yang cocok
                        Object.keys(combinedRows).forEach(key => {
                            const [kid, combinedSid] = key.split('_');
                            const provId = kid.split('.')[0];
                            
                            if (combinedSid === sid && provId === pid) {
                                if (th === tahunAwal) {
                                    combinedRows[key]['PDRB Sektor Pembanding Awal'] = parseFloat(valStr || 0);
                                } else if (th === tahunAkhir) {
                                    combinedRows[key]['PDRB Sektor Pembanding Akhir'] = parseFloat(valStr || 0);
                                }
                            }
                        });
                    });
                    
                    jsonData = type === 'klassen' ? klassenRows : Object.values(combinedRows);
                } else {
                    const firstSheet = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheet];
                    
                    const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    if (rows.length < 2) {
                        throw new Error('Data Excel kosong atau format tidak sesuai.');
                    }
                    
                    const headers = rows[0].map(h => String(h || '').trim().toLowerCase());
                    const cleanHeaders = headers.map(h => h.replace(/[^a-z0-9]/g, ''));
                    
                    const hasTahunCol = cleanHeaders.includes('tahun');
                    const hasTahunAwalCol = cleanHeaders.includes('tahunawal');
                    const hasTahunAkhirCol = cleanHeaders.includes('tahunakhir');
                    
                    const hasLqFormatCol = cleanHeaders.includes('pdrbsektoranalisis') || cleanHeaders.includes('totalpdrbanalisis');
                    const hasTipologiFormatCol = cleanHeaders.includes('nilailq') && cleanHeaders.includes('nilaiss');
                    
                    if (hasTahunCol && !hasTahunAwalCol && !hasTahunAkhirCol && !hasLqFormatCol && !hasTipologiFormatCol) {
                        statusEl.textContent = 'Mendeteksi format data mentah (transactional)...';
                        
                        // Map the column indices
                        let provIdx = -1;
                        let kabIdx = -1;
                        let sektorIdx = -1;
                        let tahunIdx = -1;
                        let nilaiProvIdx = -1;
                        let nilaiKabIdx = -1;
                        
                        headers.forEach((h, idx) => {
                            const cleanHeader = h.replace(/[^a-z0-9]/g, '');
                            
                            // Find Provinsi Column (Prefer Name over ID)
                            if (provIdx === -1 && (cleanHeader === 'namaprovinsi' || cleanHeader === 'provinsi')) {
                                provIdx = idx;
                            }
                            
                            // Find Kabupaten Column (Prefer Name over ID)
                            if (kabIdx === -1 && (cleanHeader === 'namakabupaten' || cleanHeader === 'kabupaten' || cleanHeader === 'kabupatenkota')) {
                                kabIdx = idx;
                            }
                            
                            // Find Sektor Column (Prefer Name over ID)
                            if (sektorIdx === -1 && (cleanHeader === 'namasektor' || cleanHeader === 'sektor' || cleanHeader === 'namakategori')) {
                                sektorIdx = idx;
                            }
                            
                            // Find Tahun Column
                            if (tahunIdx === -1 && cleanHeader === 'tahun') {
                                tahunIdx = idx;
                            }
                        });
                        
                        // Fallbacks for IDs if names not found
                        if (provIdx === -1) {
                            headers.forEach((h, idx) => {
                                const cleanHeader = h.replace(/[^a-z0-9]/g, '');
                                if (provIdx === -1 && (cleanHeader.includes('provinsiid') || cleanHeader.includes('kodeprovinsi') || cleanHeader.includes('kodewilayah'))) {
                                    provIdx = idx;
                                }
                            });
                        }
                        if (kabIdx === -1) {
                            headers.forEach((h, idx) => {
                                const cleanHeader = h.replace(/[^a-z0-9]/g, '');
                                if (kabIdx === -1 && (cleanHeader.includes('kabid') || cleanHeader.includes('kodekabupaten'))) {
                                    kabIdx = idx;
                                }
                            });
                        }
                        if (sektorIdx === -1) {
                            headers.forEach((h, idx) => {
                                const cleanHeader = h.replace(/[^a-z0-9]/g, '');
                                if (sektorIdx === -1 && (cleanHeader.includes('idsektor') || cleanHeader.includes('sektorid') || cleanHeader.includes('kodesektor'))) {
                                    sektorIdx = idx;
                                }
                            });
                        }
                        
                        // Explicit search for Nilai Provinsi and Nilai Kabupaten
                        headers.forEach((h, idx) => {
                            const cleanHeader = h.replace(/[^a-z0-9]/g, '');
                            if (nilaiProvIdx === -1 && (cleanHeader.includes('nilaiprov') || cleanHeader.includes('nilaiprdbprov') || cleanHeader.includes('nilaipdrbprov') || cleanHeader.includes('pdrbprov') || cleanHeader === 'nilaiprovinsi')) {
                                nilaiProvIdx = idx;
                            }
                            if (nilaiKabIdx === -1 && (cleanHeader.includes('nilaikab') || cleanHeader.includes('nilaipdrbkab') || cleanHeader.includes('pdrbkab') || cleanHeader === 'nilaikabupaten')) {
                                nilaiKabIdx = idx;
                            }
                        });
                        
                        // Fallback search for duplicate/unspecified 'nilai' or 'pdrb' columns
                        const nilaiIndices = [];
                        headers.forEach((h, idx) => {
                            if (h.includes('nilai') || h.includes('pdrb')) {
                                nilaiIndices.push(idx);
                            }
                        });
                        
                        // Detect if this is a side-by-side raw transactional layout (e.g. test_import.xlsx)
                        let isSideBySide = false;
                        if (provIdx !== -1 && kabIdx !== -1 && sektorIdx !== -1 && tahunIdx !== -1) {
                            let emptyKabCount = 0;
                            let totalRowsToCheck = Math.min(rows.length, 100);
                            for (let r = 1; r < totalRowsToCheck; r++) {
                                if (!rows[r] || rows[r][kabIdx] === undefined || rows[r][kabIdx] === null || String(rows[r][kabIdx]).trim() === '') {
                                    emptyKabCount++;
                                }
                            }
                            if (emptyKabCount > (totalRowsToCheck - 1) * 0.5) {
                                isSideBySide = true;
                            }
                        }
                        
                        if (isSideBySide && nilaiIndices.length >= 2) {
                            // The one with fewer non-empty cells is province, the other is kabupaten
                            let count0 = 0;
                            let count1 = 0;
                            for (let r = 1; r < rows.length; r++) {
                                if (rows[r]) {
                                    if (rows[r][nilaiIndices[0]] !== undefined && rows[r][nilaiIndices[0]] !== null && String(rows[r][nilaiIndices[0]]).trim() !== '') count0++;
                                    if (rows[r][nilaiIndices[1]] !== undefined && rows[r][nilaiIndices[1]] !== null && String(rows[r][nilaiIndices[1]]).trim() !== '') count1++;
                                }
                            }
                            if (count0 < count1) {
                                nilaiProvIdx = nilaiIndices[0];
                                nilaiKabIdx = nilaiIndices[1];
                            } else {
                                nilaiProvIdx = nilaiIndices[1];
                                nilaiKabIdx = nilaiIndices[0];
                            }
                        } else {
                            if (nilaiProvIdx === -1 || nilaiKabIdx === -1) {
                                if (nilaiIndices.length >= 2) {
                                    if (nilaiProvIdx === -1) nilaiProvIdx = nilaiIndices[0];
                                    if (nilaiKabIdx === -1) nilaiKabIdx = nilaiIndices[1];
                                } else if (nilaiIndices.length === 1) {
                                    if (nilaiKabIdx === -1) nilaiKabIdx = nilaiIndices[0];
                                    if (nilaiProvIdx === -1) nilaiProvIdx = nilaiIndices[0];
                                }
                            }
                        }
                        
                        const parseNumberVal = (val) => {
                            if (val === null || val === undefined) return 0;
                            if (typeof val === 'number') return val;
                            let str = String(val).trim();
                            if (!str) return 0;
                            str = str.replace(/\./g, '').replace(/,/g, '.');
                            const parsed = parseFloat(str);
                            return isNaN(parsed) ? 0 : parsed;
                        };
                        
                        // Parse data rows
                        const dataRows = [];
                        if (isSideBySide) {
                            statusEl.textContent = 'Mendekompresi format side-by-side relational...';
                            
                            // Extract distinct kabupaten list
                            const kabList = [];
                            for (let i = 1; i < rows.length; i++) {
                                const val = rows[i] && rows[i][kabIdx] ? String(rows[i][kabIdx]).trim() : '';
                                if (val) kabList.push(val);
                            }
                            
                            // Extract distinct sektor list
                            const sektorList = [];
                            for (let i = 1; i < rows.length; i++) {
                                const val = rows[i] && rows[i][sektorIdx] ? String(rows[i][sektorIdx]).trim() : '';
                                if (val) sektorList.push(val);
                            }
                            
                            // Extract distinct years list
                            const allYears = [];
                            for (let i = 1; i < rows.length; i++) {
                                if (rows[i] && !isNaN(parseInt(rows[i][tahunIdx]))) {
                                    allYears.push(parseInt(rows[i][tahunIdx]));
                                }
                            }
                            const years = [...new Set(allYears)].sort((a,b) => a-b);
                            
                            if (kabList.length === 0 || sektorList.length === 0 || years.length === 0) {
                                throw new Error('Format side-by-side tidak lengkap. Pastikan terdapat list kabupaten, sektor, dan tahun.');
                            }
                            
                            // Populate province PDRB map: key = `${sektor_name}_${tahun}` -> value
                            const provPdrbMap = {};
                            const numProvRows = sektorList.length * years.length;
                            for (let i = 1; i <= Math.min(numProvRows, rows.length - 1); i++) {
                                const row = rows[i];
                                if (!row) continue;
                                const sektorName = sektorList[(i - 1) % sektorList.length];
                                const tahun = parseInt(row[tahunIdx]);
                                const val = parseNumberVal(row[nilaiProvIdx]);
                                if (sektorName && !isNaN(tahun)) {
                                    provPdrbMap[`${sektorName}_${tahun}`] = val;
                                }
                            }
                            
                            // Reconstruct rows
                            const rowsPerKab = sektorList.length * years.length;
                            for (let i = 1; i < rows.length; i++) {
                                const row = rows[i];
                                if (!row || row.length === 0) continue;
                                
                                const rowIdx0 = i - 1;
                                const kabIdxVal = Math.floor(rowIdx0 / rowsPerKab);
                                if (kabIdxVal >= kabList.length) continue;
                                
                                const kabName = kabList[kabIdxVal];
                                const sektorName = sektorList[rowIdx0 % sektorList.length];
                                const tahun = parseInt(row[tahunIdx]);
                                if (isNaN(tahun)) continue;
                                
                                const nilaiProv = provPdrbMap[`${sektorName}_${tahun}`] || 0;
                                const nilaiKab = parseNumberVal(row[nilaiKabIdx]);
                                
                                dataRows.push({
                                    provinsi: 'SUMATERA UTARA',
                                    kabupaten: kabName,
                                    sektor: sektorName,
                                    tahun: tahun,
                                    nilaiProv: nilaiProv,
                                    nilaiKab: nilaiKab
                                });
                            }
                        } else {
                            // Parse standard flat transactional data rows
                            for (let i = 1; i < rows.length; i++) {
                                const row = rows[i];
                                if (!row || row.length === 0) continue;
                                
                                const provVal = provIdx !== -1 ? String(row[provIdx] || '').trim() : '';
                                const kabVal = kabIdx !== -1 ? String(row[kabIdx] || '').trim() : '';
                                const sektorVal = sektorIdx !== -1 ? String(row[sektorIdx] || '').trim() : '';
                                const tahunVal = tahunIdx !== -1 ? parseInt(row[tahunIdx]) : NaN;
                                const nilaiProvVal = nilaiProvIdx !== -1 ? parseNumberVal(row[nilaiProvIdx]) : 0;
                                const nilaiKabVal = nilaiKabIdx !== -1 ? parseNumberVal(row[nilaiKabIdx]) : 0;
                                
                                if (isNaN(tahunVal)) continue;
                                
                                dataRows.push({
                                    provinsi: provVal,
                                    kabupaten: kabVal,
                                    sektor: sektorVal,
                                    tahun: tahunVal,
                                    nilaiProv: nilaiProvVal,
                                    nilaiKab: nilaiKabVal
                                });
                            }
                        }
                        
                        const years = [...new Set(dataRows.map(r => r.tahun))].filter(y => !isNaN(y)).sort((a, b) => a - b);
                        if (years.length < 2) {
                            throw new Error("Data PDRB harus memiliki minimal 2 tahun data untuk analisis.");
                        }
                        
                        if (type === 'klassen') {
                            // Output all data rows for Klassen
                            statusEl.textContent = `Mengonversi ${dataRows.length} baris data tahunan untuk Klassen...`;
                            
                            // Calculate Total PDRB per region per year
                            const totalKab = {};
                            const totalProv = {};
                            
                            dataRows.forEach(r => {
                                const kabKey = `${r.kabupaten}_${r.tahun}`;
                                const provKey = `${r.provinsi}_${r.tahun}`;
                                
                                if (!totalKab[kabKey]) totalKab[kabKey] = 0;
                                totalKab[kabKey] += r.nilaiKab;
                                
                                if (!totalProv[provKey]) totalProv[provKey] = 0;
                                totalProv[provKey] += r.nilaiProv;
                            });
                            
                            const klassenArr = [];
                            dataRows.forEach(r => {
                                klassenArr.push({
                                    'Provinsi': r.provinsi || 'Sumatera Utara',
                                    'Kabupaten/Kota': r.kabupaten || '-',
                                    'Sektor': r.sektor,
                                    'Tahun': r.tahun,
                                    'PDRB Sektor': r.nilaiKab,
                                    'Total PDRB': totalKab[`${r.kabupaten}_${r.tahun}`] || 0,
                                    'PDRB Sektor Pembanding': r.nilaiProv,
                                    'Total PDRB Pembanding': totalProv[`${r.provinsi}_${r.tahun}`] || 0
                                });
                            });
                            
                            jsonData = klassenArr;
                        } else {
                            const tahunAwal = years[0];
                            const tahunAkhir = years[years.length - 1];
                            
                            statusEl.textContent = `Menghitung total & mengonversi data (${tahunAwal} ke ${tahunAkhir})...`;
                        
                        // Calculate Total PDRB per region per year
                        const totalKab = {};
                        const totalProv = {};
                        
                        dataRows.forEach(r => {
                            const kabKey = `${r.kabupaten}_${r.tahun}`;
                            const provKey = `${r.provinsi}_${r.tahun}`;
                            
                            if (!totalKab[kabKey]) totalKab[kabKey] = 0;
                            totalKab[kabKey] += r.nilaiKab;
                            
                            if (!totalProv[provKey]) totalProv[provKey] = 0;
                            totalProv[provKey] += r.nilaiProv;
                        });
                        
                        const combined = {};
                        dataRows.forEach(r => {
                            const groupKey = `${r.provinsi}_${r.kabupaten}_${r.sektor}`;
                            if (!combined[groupKey]) {
                                combined[groupKey] = {
                                    'Provinsi': r.provinsi || 'Sumatera Utara',
                                    'Kabupaten/Kota': r.kabupaten || '-',
                                    'Sektor': r.sektor,
                                    'Tahun Awal': tahunAwal,
                                    'Tahun Akhir': tahunAkhir,
                                    'PDRB Sektor Analisis Awal': 0,
                                    'PDRB Sektor Analisis Akhir': 0,
                                    'Total PDRB Analisis Awal': totalKab[`${r.kabupaten}_${tahunAwal}`] || 0,
                                    'Total PDRB Analisis Akhir': totalKab[`${r.kabupaten}_${tahunAkhir}`] || 0,
                                    'PDRB Sektor Pembanding Awal': 0,
                                    'PDRB Sektor Pembanding Akhir': 0,
                                    'Total PDRB Pembanding Awal': totalProv[`${r.provinsi}_${tahunAwal}`] || 0,
                                    'Total PDRB Pembanding Akhir': totalProv[`${r.provinsi}_${tahunAkhir}`] || 0
                                };
                            }
                            
                            if (r.tahun === tahunAwal) {
                                combined[groupKey]['PDRB Sektor Analisis Awal'] = r.nilaiKab;
                                combined[groupKey]['PDRB Sektor Pembanding Awal'] = r.nilaiProv;
                            } else if (r.tahun === tahunAkhir) {
                                combined[groupKey]['PDRB Sektor Analisis Akhir'] = r.nilaiKab;
                                combined[groupKey]['PDRB Sektor Pembanding Akhir'] = r.nilaiProv;
                            }
                        });
                        
                        jsonData = Object.values(combined);
                        } // Closes else for if (type === 'klassen')
                    } else {
                        jsonData = XLSX.utils.sheet_to_json(worksheet);
                    }
                }
                
                if (jsonData.length === 0) {
                    throw new Error('Data Excel kosong atau format tidak sesuai.');
                }
                
                statusEl.textContent = 'Menyimpan ' + jsonData.length + ' baris data ke sistem...';

                const response = await fetch("{{ $action }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(jsonData)
                });

                const result = await response.json();
                
                if (result.success) {
                    statusEl.textContent = result.message || 'Berhasil! Memuat ulang halaman...';
                    statusEl.className = 'text-sm font-medium mt-2 text-emerald-600 block';
                    setTimeout(() => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', 'simulasi');
                        window.location.href = url.toString();
                    }, 1000);
                } else {
                    throw new Error(result.message || 'Terjadi kesalahan saat menyimpan data.');
                }
            } catch (err) {
                statusEl.textContent = 'Gagal: ' + err.message;
                statusEl.className = 'text-sm font-medium mt-2 text-red-600 block';
                processBtn.disabled = false;
                processBtn.textContent = 'Mulai Unggah';
            }
        };

        reader.onerror = function() {
            statusEl.textContent = 'Gagal membaca file dari komputer Anda.';
            statusEl.className = 'text-sm font-medium mt-2 text-red-600 block';
            processBtn.disabled = false;
            processBtn.textContent = 'Mulai Unggah';
        };

        reader.readAsArrayBuffer(file);
    }
</script>
@endonce
