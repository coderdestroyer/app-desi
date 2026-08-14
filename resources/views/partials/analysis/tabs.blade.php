@if(!empty($dashboard['tabs']))

<section class="table-section">

    <div class="table-card">

        <div class="tabs-header">

            @foreach($dashboard['tabs'] as $index => $tab)

                <button
                    type="button"
                    class="tab-button {{ $index === 0 ? 'active' : '' }}"
                    data-tab="{{ $tab['key'] }}"
                >

                    <span class="tab-title">
                        {{ $tab['title'] }}
                    </span>

                    <span class="tab-count">

                        {{ count($tab['rows']) }}

                    </span>

                </button>

            @endforeach

        </div>

        {{-- Content --}}
        <div class="tabs-body">

            @foreach($dashboard['tabs'] as $index => $tab)

                <div
                    class="tab-content {{ $index === 0 ? 'active' : '' }}"
                    id="{{ $tab['key'] }}"
                >

                    @if(!empty($tab['rows']))

                        @php
                            $columns = array_keys($tab['rows'][0]);
                        @endphp

                        <div class="table-responsive">

                            <table class="analysis-table">

                                <thead>

                                    <tr>

                                        @foreach($columns as $column)

                                            <th>{{ \Illuminate\Support\Str::replace(['Ssa', 'Lq'], ['SSA', 'LQ'], ucwords(str_replace('_',' ',$column))) }}</th>

                                        @endforeach

                                    </tr>

                                </thead>

                                <tbody>

                                @foreach($tab['rows'] as $row)

                                <tr>

                                @foreach($row as $key => $value)

                                @php
                                    $column = strtolower($key);
                                @endphp

                                <td>

                                @if($column === 'tahun')

                                    {{ (int)$value }}

                                @elseif(in_array($column,['id','sektor_id']))

                                    {{ (int)$value }}

                                @elseif(in_array($column,['nama','nama_sektor','sektor']))

                                    {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower($value)) }}

                                @elseif($column === 'nilai_lq')

                                    {{ number_format($value,3,',','.') }}

                                @elseif(in_array($column,['rn','rin','rij']))

                                    {{ number_format($value,4,',','.') }}

                                @elseif(in_array($column,['nij','mij','cij','dij','nilai_ssa']))

                                    {{ number_format($value,2,',','.') }}

                                @elseif($column==='kategori')

                                    @php
                                        $class=$value=='Basis'
                                            ?'badge-success'
                                            :'badge-warning';
                                    @endphp

                                    <span class="badge {{ $class }}">
                                        {{ $value }}
                                    </span>

                                @elseif($column==='kategori_pertumbuhan')

                                    <span class="badge {{ str_contains($value,'Cepat')?'badge-success':'badge-danger' }}">

                                        {{ $value }}

                                    </span>

                                @elseif($column==='kategori_daya_saing')

                                    <span class="badge {{ str_contains($value,'Baik')?'badge-primary':'badge-danger' }}">

                                        {{ $value }}

                                    </span>

                                @elseif($column==='kuadran')

                                    @php

                                        $class=match($value){

                                            'Kuadran I'=>'badge-success',

                                            'Kuadran II'=>'badge-primary',

                                            'Kuadran III'=>'badge-warning',

                                            'Kuadran IV'=>'badge-danger',

                                            default=>'badge-secondary'

                                        };

                                    @endphp

                                    <span class="badge {{ $class }}">

                                        {{ $value }}

                                    </span>

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

                    @else

                        <div class="empty-tab">

                            Tidak ada data.

                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    </div>

</section>

@endif