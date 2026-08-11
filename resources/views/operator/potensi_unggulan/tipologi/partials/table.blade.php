<div class="w-full overflow-x-auto border border-slate-200/80 rounded-xl">
    <table id="tipologiTable" class="w-full min-w-[950px] text-left border-collapse text-xs">
        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3.5 w-12 text-center">No</th>
                <th class="px-4 py-3.5 whitespace-nowrap">Tingkat Wilayah</th>
                <th class="px-4 py-3.5 min-w-[160px]">DAERAH ANALISIS</th>
                <th class="px-4 py-3.5 min-w-[160px]">PEMBANDING</th>
                <th class="px-4 py-3.5 text-center whitespace-nowrap">TAHUN</th>
                <th class="px-4 py-3.5 text-center min-w-[110px]">KUADRAN I</th>
                <th class="px-4 py-3.5 text-center min-w-[110px]">KUADRAN II</th>
                <th class="px-4 py-3.5 text-center min-w-[110px]">KUADRAN III</th>
                <th class="px-4 py-3.5 text-center min-w-[110px]">KUADRAN IV</th>
                <th class="px-4 py-3.5 text-center min-w-[160px]">KLASIFIKASI DOMINAN</th>
                <th class="px-4 py-3.5 text-center w-36">AKSI</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            @forelse($tipologiData as $index => $data)
                <tr class="hover:bg-emerald-50/30 transition-colors {{ !empty($data['is_provinsi']) ? 'bg-emerald-50/40 font-semibold' : '' }}">
                    <td class="px-4 py-3.5 text-center font-mono text-slate-400">{{ ($tipologiData->currentPage() - 1) * $tipologiData->perPage() + $loop->iteration }}</td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        @if(!empty($data['is_provinsi']) || $data['tingkat_wilayah'] === 'Provinsi')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-extrabold bg-[#145239] text-white border border-[#145239] whitespace-nowrap">
                                <i class="fa-solid fa-building-columns text-[10px] text-[#FFD54F]"></i> Provinsi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">
                                Kabupaten/Kota
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 font-bold text-slate-800">{{ $data['daerah_analisis'] }}</td>
                    <td class="px-4 py-3.5 text-slate-600 font-medium">{{ $data['daerah_pembanding'] }}</td>
                    <td class="px-4 py-3.5 text-center font-mono font-bold whitespace-nowrap">{{ $data['tahun'] }}</td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap">
                            {{ $data['c1_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap">
                            {{ $data['c2_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200 whitespace-nowrap">
                            {{ $data['c3_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 whitespace-nowrap">
                            {{ $data['c4_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center font-bold text-xs text-slate-700 whitespace-nowrap">
                        {{ $data['status_dominan'] }}
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        <a href="{{ route('operator.tipologi.show', [
                            'tingkat_wilayah' => $data['tingkat_wilayah'],
                            'tahun' => $data['tahun'],
                            'kabupaten_id' => $data['kabupaten_id'] ?? null,
                            'provinsi_id' => $data['provinsi_id'] ?? null
                        ]) }}" class="inline-flex items-center gap-1.5 bg-[#145239] hover:bg-[#0F8A5F] text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs whitespace-nowrap">
                            <i class="fa-solid fa-list-check text-[#FFD54F]"></i>
                            <span>Lihat Detail</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="px-4 py-8 text-center text-slate-500 font-medium">
                        Belum ada data ringkasan Tipologi Sektor yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Component -->
<x-pagination :paginator="$tipologiData" />
