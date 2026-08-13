<div class="overflow-x-auto border border-slate-200/80 rounded-xl">
    <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3.5 text-center w-12">No</th>
                <th class="px-5 py-3.5 min-w-[150px]">Provinsi</th>
                <th class="px-5 py-3.5 min-w-[180px]">Kabupaten / Kota</th>
                <th class="px-4 py-3.5 text-center w-28">Tahun PDRB</th>
                <th class="px-4 py-3.5 text-center min-w-[150px]">Sektor Terisi</th>
                <th class="px-5 py-3.5 text-right w-44">Total PDRB (Rp Juta)</th>
                <th class="px-4 py-3.5 text-center w-36">Aksi & Manajemen</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm text-slate-700 font-medium">
            @forelse($pdrbGroups as $index => $group)
                <tr class="hover:bg-emerald-50/30 transition-colors">
                    <td class="px-4 py-3.5 text-center text-slate-400 font-mono">
                        {{ $pdrbGroups->firstItem() + $index }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-700 font-semibold">
                        {{ $group->kabupaten->provinsi->nama_provinsi ?? '-' }}
                    </td>
                    <td class="px-5 py-3.5 text-slate-900 font-bold">
                        {{ $group->kabupaten->nama_kabupaten ?? '-' }}
                    </td>
                    <td class="px-4 py-3.5 text-center font-mono font-semibold whitespace-nowrap">
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
                        Rp {{ number_format($group->total_pdrb, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                        @if(($tab ?? 'own') === 'own' && Auth::user()->canAccessKabupaten($group->kabupaten_id))
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Edit Button (Icon Only) --}}
                                <a href="{{ route('operator.pdrb.entry', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                    title="Edit Data PDRB ({{ $group->kabupaten->nama_kabupaten ?? '' }} {{ $group->tahun }})">
                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                </a>

                                {{-- Delete Group Button (Icon Only) --}}
                                <button type="button"
                                    @click="openDeleteModal('{{ route('operator.pdrb.destroy-group', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}', '{{ $group->kabupaten->nama_kabupaten ?? 'Kabupaten/Kota' }}', '{{ $group->tahun }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors shadow-2xs"
                                    title="Hapus Data PDRB {{ $group->kabupaten->nama_kabupaten ?? '' }} {{ $group->tahun }}">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        @else
                            <a href="{{ route('operator.pdrb.detail', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}"
                                class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 transition-colors text-xs font-bold"
                                title="Lihat Rincian Sektor PDRB">
                                <i class="fa-solid fa-eye text-xs"></i>
                                <span>Lihat Nilai</span>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-3xl mb-2 block text-slate-300"></i>
                        @if(request()->filled('search'))
                            Data PDRB dengan kata kunci <strong>"{{ request('search') }}"</strong> tidak ditemukan.
                            <div class="mt-2">
                                <a href="{{ route('operator.pdrb.index', ['tab' => $tab ?? 'own']) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                                    <i class="fa-solid fa-rotate-left mr-1"></i> Reset Pencarian
                                </a>
                            </div>
                        @else
                            Belum ada data PDRB yang terdaftar. Klik <strong>"+ Inisiasi Data PDRB Baru"</strong> untuk menambah daerah & tahun baru.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Component -->
<x-pagination :paginator="$pdrbGroups" />
