<div class="overflow-x-auto border border-slate-200/80 rounded-xl">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3.5 text-center w-12">No</th>
                <th class="px-5 py-3.5 text-center w-36">Tahun PDB Nasional</th>
                <th class="px-4 py-3.5 text-center min-w-[160px]">Sektor Terisi</th>
                <th class="px-5 py-3.5 text-right min-w-[200px]">Total PDB Nasional (Rp Juta)</th>
                <th class="px-4 py-3.5 text-center w-40">Aksi / Detail PDB</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm text-slate-700 font-medium">
            @forelse($pdbGroups as $index => $group)
                <tr class="hover:bg-emerald-50/30 transition-colors">
                    <td class="px-4 py-3.5 text-center text-slate-400 font-mono">
                        {{ $pdbGroups->firstItem() + $index }}
                    </td>
                    <td class="px-5 py-3.5 text-center font-mono font-semibold whitespace-nowrap">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-800">
                            {{ $group->tahun }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        @if($group->total_sektor >= 17)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#145239] border border-emerald-200 leading-snug">
                                <i class="fa-solid fa-circle-check text-[11px] text-emerald-600"></i>
                                <span>{{ $group->total_sektor }}/17 Sektor</span>
                            </span>
                        @elseif($group->total_sektor > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 leading-snug">
                                <i class="fa-solid fa-clock text-[11px] text-amber-600"></i>
                                <span>{{ $group->total_sektor }}/17 Sektor</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200 leading-snug">
                                <i class="fa-solid fa-circle-minus text-[11px] text-slate-400"></i>
                                <span>0 Sektor</span>
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                        Rp {{ number_format($group->total_pdb, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <a href="{{ route('operator.pdb-nasional.detail', ['tahun' => $group->tahun]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EEF8F2] hover:bg-[#CFE3D5] text-[#145239] font-bold text-xs border border-[#CFE3D5] transition-colors shadow-2xs">
                            <i class="fa-solid fa-eye text-xs"></i>
                            <span>Lihat Detail</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                        Belum ada data PDB Nasional yang terdaftar di sistem.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Component -->
<x-pagination :paginator="$pdbGroups" />
