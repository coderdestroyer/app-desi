@if(!empty($dashboard['table']))

<section class="table-section">

    <div class="table-card">

        <div class="table-header flex items-center justify-between flex-wrap gap-3">

            <h3>

                Detail Hasil Analisis

            </h3>

            <div class="export-btn-group">

                <button type="button" id="btnExportExcel" class="btn-export btn-export-excel" title="Export data ke format Excel (.xlsx)">

                    <i class="fa-solid fa-file-excel"></i> Export Excel

                </button>

                <button type="button" id="btnExportJson" class="btn-export btn-export-json" title="Export data ke format JSON (.json)">

                    <i class="fa-solid fa-file-code"></i> Export JSON

                </button>

            </div>

        </div>

        <div class="table-responsive">

            <table class="analysis-table">

                <thead>

                    <tr>

                        @foreach(array_keys($dashboard['table'][0]) as $column)

                            <th>

                                {{ \Illuminate\Support\Str::replace(['Ssa', 'Lq'], ['SSA', 'LQ'], ucwords(str_replace('_',' ', $column))) }}

                            </th>

                        @endforeach

                    </tr>

                </thead>

                <tbody>

                    @foreach($dashboard['table'] as $row)

                    <tr>

                    @foreach($row as $key => $value)

                    @php
                        $column = strtolower($key);
                    @endphp

                    <td>

                    {{-- Tahun --}}
                    @if($column === 'tahun')

                        {{ (int) $value }}

                    {{-- ID --}}
                    @elseif(in_array($column,['id','sektor_id']))

                        {{ (int) $value }}

                    {{-- Nama sektor --}}
                    @elseif(in_array($column,['nama_sektor','sektor']))

                        {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower($value)) }}

                    {{-- Nilai LQ --}}
                    @elseif($column === 'nilai_lq')

                        {{ number_format((float)$value,3,',','.') }}

                    {{-- Rn, Rin, Rij --}}
                    @elseif(in_array($column,['rn','rin','rij']))

                        {{ number_format((float)$value,4,',','.') }}

                    {{-- Nij, Mij, Cij, Dij, Nilai SSA --}}
                    @elseif(in_array($column,['nij','mij','cij','dij','nilai_ssa']))

                        {{ number_format((float)$value, 2, ',', '.') }}

                    {{-- Pertumbuhan & Kontribusi (%) --}}
                    @elseif(in_array($column,[
                        'pertumbuhan_kabupaten',
                        'kontribusi_kabupaten',
                        'pertumbuhan_provinsi',
                        'kontribusi_provinsi'
                    ]))

                        {{ number_format((float)$value * 100, 2, ',', '.') }}%

                    {{-- Badge Kategori --}}
                    @elseif($column === 'kategori')

                        @php

                            $class = match($value){

                                // LQ
                                'Basis' => 'badge-success',
                                'Non Basis' => 'badge-warning',

                                // Tipologi Sektor
                                'Sektor Unggulan' => 'badge-success',
                                'Sektor Potensial' => 'badge-primary',
                                'Sektor Berkembang' => 'badge-warning',
                                'Sektor Relatif Tertinggal' => 'badge-danger',

                                default => 'badge-secondary'

                            };

                        @endphp

                        <span class="badge {{ $class }}">
                            {{ $value }}
                        </span>
                    {{-- Badge Pertumbuhan SSA --}}
                    @elseif($column === 'kategori_pertumbuhan')

                        @php
                            $class = str_contains($value,'Cepat')
                                ? 'badge-success'
                                : 'badge-danger';
                        @endphp

                        <span class="badge {{ $class }}">
                            {{ $value }}
                        </span>

                    {{-- Badge Daya Saing SSA --}}
                    @elseif($column === 'kategori_daya_saing')

                        @php
                            $class = str_contains($value,'Baik')
                                ? 'badge-primary'
                                : 'badge-danger';
                        @endphp

                        <span class="badge {{ $class }}">
                            {{ $value }}
                        </span>

                    {{-- Badge Kuadran --}}
                    @elseif($column === 'kuadran')

                        @php
                            $class = match($value){
                                'Kuadran I'   => 'badge-success',
                                'Kuadran II'  => 'badge-primary',
                                'Kuadran III' => 'badge-warning',
                                'Kuadran IV'  => 'badge-danger',
                                default       => 'badge-secondary'
                            };
                        @endphp

                        <span class="badge {{ $class }}">
                            {{ $value }}
                        </span>

                    {{-- Angka lainnya --}}
                    @elseif(is_numeric($value))

                        {{ number_format((float)$value,2,',','.') }}

                    {{-- Default --}}
                    @else

                        {{ $value }}

                    @endif

                    </td>

                    @endforeach

                    </tr>

                    @endforeach

                </tbody>
            </table>

        </div>

    </div>

</section>

@endif