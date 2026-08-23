@extends('layouts.admin')

@section('title', 'Dokumen Kelayakan IPRO - Admin')

@section('content')
<div class="min-h-screen bg-[#F7FAF8] p-4 sm:p-6 md:p-7 lg:p-8 space-y-6">

    <!-- Banner Header Stat -->
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-file-invoice-dollar text-[#FFD54F]"></i>
                <span>Peninjauan Finansial Proyek (Database Riil)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Pusat Peninjauan Dokumen Proyek IPRO
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                Lihat struktur Estimasi CAPEX, Proyeksi Laba Rugi (P&L), dan Simulasi Arus Kas yang disajikan secara terintegrasi dari database.
            </p>
        </div>

        <div class="relative z-10 w-full md:w-auto flex md:flex-col items-center md:items-end justify-between md:justify-center pt-4 md:pt-0 border-t border-emerald-700/60 md:border-t-0 shrink-0">
            <span class="text-xs uppercase font-bold text-[#FFD54F] tracking-wider block mb-0.5">Total Proyek</span>
            <span class="text-2xl md:text-3xl font-black text-white">{{ number_format($projects->total(), 0, ',', '.') }} Dokumen</span>
        </div>
    </section>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-[#CFE3D5] flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
        <form method="GET" action="{{ route('admin.proyek-ipro.index') }}" class="relative flex-1 min-w-0">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Cari nama proyek atau deskripsi..." 
                class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 text-sm placeholder:text-slate-400 outline-none shadow-2xs"
            >
        </form>
        
        <form method="GET" action="{{ route('admin.proyek-ipro.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:flex items-center gap-3">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <div class="relative w-full lg:w-auto">
                <select name="kabupaten_id" onchange="this.form.submit()" class="w-full lg:w-auto h-11 px-3.5 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] text-sm text-slate-700 bg-white outline-none appearance-none pr-9 font-medium shadow-2xs">
                    <option value="">Semua Kabupaten/Kota</option>
                    @foreach($kabupatens as $kab)
                        <option value="{{ $kab->kab_id }}" {{ request('kabupaten_id') == $kab->kab_id ? 'selected' : '' }}>
                            {{ $kab->nama_kabupaten }}
                        </option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
            </div>

            <div class="relative w-full lg:w-auto">
                <select name="sektor_id" onchange="this.form.submit()" class="w-full lg:w-auto h-11 px-3.5 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] text-sm text-slate-700 bg-white outline-none appearance-none pr-9 font-medium shadow-2xs">
                    <option value="">Semua Sektor Ekonomi</option>
                    @foreach($sektors as $sek)
                        <option value="{{ $sek->sektor_id }}" {{ request('sektor_id') == $sek->sektor_id ? 'selected' : '' }}>
                            {{ $sek->nama_sektor }}
                        </option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none"></i>
            </div>

            @if(request('search') || request('kabupaten_id') || request('sektor_id'))
                <a href="{{ route('admin.proyek-ipro.index') }}" class="sm:col-span-2 lg:col-span-1 h-11 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold inline-flex items-center justify-center gap-2 transition-colors">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset Filter</span>
                </a>
            @endif

            <button type="button" onclick="exportIproListToExcel()" class="sm:col-span-2 lg:col-span-1 h-11 px-4 rounded-xl bg-[#1F497D] hover:bg-[#16355B] text-white text-xs font-bold inline-flex items-center justify-center gap-2 transition-colors shadow-xs shrink-0 cursor-pointer">
                <i class="fa-solid fa-file-excel text-emerald-400"></i>
                <span>Export Excel</span>
            </button>
        </form>
    </div>

    <!-- TABEL MEMANJANG (RESPONSIVE TABLE VIEW) -->
    <div class="bg-white rounded-2xl shadow-xs border border-[#CFE3D5] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm text-left border-collapse">
                <thead class="bg-[#F7FAF8] border-b border-[#CFE3D5]">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider w-12 text-center">No</th>
                        <th scope="col" class="px-5 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider min-w-[260px]">Nama Proyek & Deskripsi</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider min-w-[160px]">Kabupaten / Kota</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider min-w-[180px]">Sektor Ekonomi</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider text-center min-w-[120px]">Tahun & Tenor</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider min-w-[160px]">Penginput (Operator)</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider text-center min-w-[180px]">Modul Dokumen</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider text-center min-w-[110px]">Status</th>
                        <th scope="col" class="px-4 py-3.5 text-xs font-bold text-[#17201C] uppercase tracking-wider text-center min-w-[120px]">Aksi Tinjau</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#CFE3D5] text-xs sm:text-sm">
                    @forelse($projects as $index => $project)
                        <tr class="hover:bg-[#F7FAF8] transition-colors">
                            
                            <td class="px-4 py-4 text-center text-slate-500 font-mono font-semibold">{{ $projects->firstItem() + $index }}</td>
                            
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.proyek-ipro.show', $project->id) }}" class="font-bold text-[#17201C] hover:text-[#145239] transition-colors text-sm block mb-0.5">
                                    {{ $project->nama_proyek }}
                                </a>
                                @if($project->deskripsi)
                                    <p class="text-xs text-[#667069] line-clamp-1" title="{{ $project->deskripsi }}">
                                        {{ $project->deskripsi }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-[#E7F2EB] text-[#145239] border border-[#CFE3D5]">
                                    <i class="fa-solid fa-location-dot text-[10px] mr-1"></i>
                                    {{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Wilayah Sumut' }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <span class="text-xs font-semibold text-[#17201C] block truncate max-w-[180px]">
                                    {{ $project->sektor ? $project->sektor->nama_sektor : '-' }}
                                </span>
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="text-xs font-mono font-bold text-slate-800">{{ $project->tahun_awal }}</div>
                                <div class="text-[11px] text-[#1E5D41] font-semibold">{{ $project->jangka_waktu_tahun }} Tahun</div>
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800 text-xs block">{{ $project->user ? $project->user->name : 'Operator' }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $project->updated_at ? $project->updated_at->diffForHumans() : '-' }}</span>
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <span class="px-2 py-1 rounded-md bg-[#E7F2EB] text-[#145239] text-[10px] font-bold border border-[#CFE3D5]">
                                        CAPEX ({{ $project->capexComponents->count() }})
                                    </span>
                                    <span class="px-2 py-1 rounded-md bg-amber-50 text-[#D4A017] text-[10px] font-bold border border-amber-200">
                                        P&L
                                    </span>
                                    <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200">
                                        Cashflow
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($project->status_publikasi === 'published')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        Published
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                        Draft
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <a href="{{ route('admin.proyek-ipro.show', $project->id) }}" class="px-3.5 py-1.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold transition-colors shadow-xs inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                    <span>Tinjau</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center mx-auto mb-3 text-xl">
                                    <i class="fa-solid fa-folder-open"></i>
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Dokumen Proyek Investasi</p>
                                <p class="text-xs text-slate-400 mt-1">Data proyek yang diinput oleh Operator akan otomatis muncul di halaman peninjauan Admin ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    @endif

</div>

<script>
    async function exportIproListToExcel() {
        if (typeof ExcelJS === 'undefined') {
            alert('Library ExcelJS belum siap. Silakan periksa koneksi internet Anda.');
            return;
        }

        const wb = new ExcelJS.Workbook();
        wb.creator = 'DPMPTSP Admin';
        const ws = wb.addWorksheet('Daftar Proyek IPRO');

        const COLOR_HEADER_BG = 'FF1F497D'; // Biru Tua Header
        const COLOR_HEADER_TEXT = 'FFFFFFFF'; // Putih
        const COLOR_BORDER = 'FFD9D9D9';

        ws.columns = [
            { header: '', key: 'colNo', width: 8 },
            { header: '', key: 'colNama', width: 35 },
            { header: '', key: 'colDeskripsi', width: 45 },
            { header: '', key: 'colKab', width: 22 },
            { header: '', key: 'colSektor', width: 25 },
            { header: '', key: 'colTahun', width: 14 },
            { header: '', key: 'colTenor', width: 14 },
            { header: '', key: 'colOperator', width: 22 },
            { header: '', key: 'colStatus', width: 15 },
        ];

        // Title Banner
        ws.mergeCells('A1:I1');
        const titleCell = ws.getCell('A1');
        titleCell.value = 'DAFTAR DOKUMEN PROYEK INVESTASI (IPRO)';
        titleCell.font = { name: 'Calibri', size: 14, bold: true, color: { argb: 'FF1F497D' } };
        titleCell.alignment = { vertical: 'middle', horizontal: 'left' };

        ws.mergeCells('A2:I2');
        const subTitleCell = ws.getCell('A2');
        subTitleCell.value = 'Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu (DPMPTSP)';
        subTitleCell.font = { name: 'Calibri', size: 11, italic: true, color: { argb: 'FF475569' } };
        subTitleCell.alignment = { vertical: 'middle', horizontal: 'left' };

        ws.addRow([]);

        // Table Header
        const headerTitles = ['No', 'Nama Proyek', 'Deskripsi Proyek', 'Kabupaten / Kota', 'Sektor Ekonomi', 'Tahun Awal', 'Tenor (Thn)', 'Penginput (Operator)', 'Status'];
        const headerRow = ws.addRow(headerTitles);
        headerRow.height = 28;

        headerRow.eachCell((cell, colNumber) => {
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: COLOR_HEADER_BG } };
            cell.font = { name: 'Calibri', size: 11, bold: true, color: { argb: COLOR_HEADER_TEXT } };
            cell.alignment = {
                vertical: 'middle',
                horizontal: (colNumber === 1 || colNumber === 6 || colNumber === 7 || colNumber === 9) ? 'center' : 'left',
                wrapText: true
            };
            cell.border = {
                top: { style: 'thin', color: { argb: COLOR_BORDER } },
                left: { style: 'thin', color: { argb: COLOR_BORDER } },
                bottom: { style: 'medium', color: { argb: COLOR_HEADER_BG } },
                right: { style: 'thin', color: { argb: COLOR_BORDER } }
            };
        });

        // Rows from DB
        const projectsData = [
            @foreach($projects as $index => $project)
            {
                no: {{ $projects->firstItem() + $index }},
                nama: @json($project->nama_proyek),
                deskripsi: @json($project->deskripsi ?: '-'),
                kabupaten: @json($project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Wilayah Sumut'),
                sektor: @json($project->sektor ? $project->sektor->nama_sektor : '-'),
                tahun: {{ $project->tahun_awal ?: '-' }},
                tenor: {{ $project->jangka_waktu_tahun ?: '-' }},
                operator: @json($project->user ? $project->user->name : 'Operator'),
                status: @json(ucfirst($project->status_publikasi ?: 'Draft'))
            },
            @endforeach
        ];

        projectsData.forEach((p, idx) => {
            const row = ws.addRow([
                p.no,
                p.nama,
                p.deskripsi,
                p.kabupaten,
                p.sektor,
                p.tahun,
                p.tenor,
                p.operator,
                p.status
            ]);
            row.height = 22;

            const isEven = idx % 2 === 1;
            const bgHex = isEven ? 'FFF7FAF8' : 'FFFFFFFF';

            row.eachCell((cell, colNumber) => {
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: bgHex } };
                cell.font = { name: 'Calibri', size: 10, color: { argb: 'FF1E293B' } };
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: (colNumber === 1 || colNumber === 6 || colNumber === 7 || colNumber === 9) ? 'center' : 'left'
                };
                cell.border = {
                    top: { style: 'thin', color: { argb: COLOR_BORDER } },
                    left: { style: 'thin', color: { argb: COLOR_BORDER } },
                    bottom: { style: 'thin', color: { argb: COLOR_BORDER } },
                    right: { style: 'thin', color: { argb: COLOR_BORDER } }
                };
            });
        });

        const buffer = await wb.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'Daftar_Proyek_IPRO_DPMPTSP.xlsx';
        link.click();
        URL.revokeObjectURL(link.href);
    }
</script>
@endsection
