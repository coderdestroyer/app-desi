<div class="overflow-x-auto border border-slate-200/80 rounded-xl">
    <table id="ssTable" class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3.5 w-12 text-center">No</th>
                <th class="px-4 py-3.5 whitespace-nowrap">Tingkat Wilayah</th>
                <th class="px-4 py-3.5 whitespace-nowrap">DAERAH ANALISIS</th>
                <th class="px-4 py-3.5 whitespace-nowrap">PEMBANDING</th>
                <th class="px-4 py-3.5 text-center whitespace-nowrap">PERIODE TAHUN</th>
                <th class="px-4 py-3.5 text-center whitespace-nowrap">PERTUMBUHAN CEPAT</th>
                <th class="px-4 py-3.5 text-center whitespace-nowrap">PERTUMBUHAN LAMBAT</th>
                <th class="px-4 py-3.5 text-center whitespace-nowrap">DAYA SAING TINGGI</th>
                <th class="px-4 py-3.5 text-center whitespace-nowrap">AKSI</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
            @forelse($ssData as $index => $data)
                <tr class="hover:bg-emerald-50/30 transition-colors {{ !empty($data['is_provinsi']) ? 'bg-emerald-50/40 font-semibold' : '' }}">
                    <td class="px-4 py-3.5 text-center font-mono font-semibold text-slate-500">{{ ($ssData->currentPage() - 1) * $ssData->perPage() + $loop->iteration }}</td>
                    <td class="px-4 py-3.5">
                        @if(!empty($data['is_provinsi']) || $data['tingkat_wilayah'] === 'Provinsi')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#145239] text-white border border-[#145239]">
                                <i class="fa-solid fa-building-columns text-[10px] text-[#FFD54F]"></i> Provinsi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                Kabupaten/Kota
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 font-bold text-slate-800">{{ $data['daerah_analisis'] }}</td>
                    <td class="px-4 py-3.5 text-slate-600 font-medium">{{ $data['daerah_pembanding'] }}</td>
                    <td class="px-4 py-3.5 text-center font-mono font-bold">{{ $data['tahun'] }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $data['sektor_cepat_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            {{ $data['sektor_lambat_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $data['daya_saing_tinggi_count'] ?? 0 }} Sektor
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <a href="{{ route('operator.ss.show', [
                            'tingkat_wilayah' => $data['tingkat_wilayah'],
                            'tahun' => $data['tahun_akhir'] ?? 2024,
                            'kabupaten_id' => $data['kabupaten_id'] ?? null,
                            'provinsi_id' => $data['provinsi_id'] ?? null
                        ]) }}" class="inline-flex items-center gap-1.5 bg-[#145239] hover:bg-[#0F8A5F] text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs">
                            <i class="fa-solid fa-list-check text-[#FFD54F]"></i>
                            <span>Lihat Detail</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-slate-500 font-medium">
                        Belum ada data ringkasan Shift-Share yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Component -->
<x-pagination :paginator="$ssData" />
