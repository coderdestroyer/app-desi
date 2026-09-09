<form
    method="GET"
    action="{{ route('analysis') }}"
    class="filter-box"
>
    {{-- =========================
        Provinsi
    ========================== --}}
    <select name="provinsi">

        <option value="">
            Pilih Provinsi
        </option>

        @foreach($provinsi as $item)

            <option
                value="{{ $item->provinsi_id }}"
                {{ ($filter['provinsi'] ?? null) == $item->provinsi_id ? 'selected' : '' }}
            >
                {{ Str::title(Str::lower($item->nama_provinsi)) }} 
            </option>

        @endforeach

    </select>

    {{-- =========================
        Kabupaten / Provinsi
    ========================== --}}
    <select name="kabupaten">

        <option value="">
            Pilih Kabupaten / Kota / Provinsi
        </option>

        @if(isset($provinsi) && count($provinsi) > 0)
            <optgroup label="Tingkat Provinsi (vs Nasional)">
                @foreach($provinsi as $p)
                    <option
                        value="prov_{{ $p->provinsi_id }}"
                        data-provinsi="{{ $p->provinsi_id }}"
                        {{ ($filter['kabupaten'] ?? null) == ('prov_' . $p->provinsi_id) ? 'selected' : '' }}
                    >
                        🏛️ PROVINSI {{ Str::upper($p->nama_provinsi) }}
                    </option>
                @endforeach
            </optgroup>
        @endif

        <optgroup label="Tingkat Kabupaten / Kota (vs Provinsi)">
            @foreach($kabupaten as $item)

                <option
                    value="{{ $item->kab_id }}"
                    data-provinsi="{{ $item->provinsi_id }}"
                    {{ ($filter['kabupaten'] ?? null) == $item->kab_id ? 'selected' : '' }}
                >
                    {{ Str::title(Str::lower($item->nama_kabupaten)) }} 
                </option>

            @endforeach
        </optgroup>

    </select>

    {{-- =========================
        Metode
    ========================== --}}
    <select name="metode">

        <option
            value="lq"
            {{ ($filter['metode'] ?? 'lq') == 'lq' ? 'selected' : '' }}
        >
            Location Quotient (LQ)
        </option>

        <option
            value="ssa"
            {{ ($filter['metode'] ?? '') == 'ssa' ? 'selected' : '' }}
        >
            Shift Share Analysis (SSA)
        </option>

        <option
            value="tipologi"
            {{ ($filter['metode'] ?? '') == 'tipologi' ? 'selected' : '' }}
        >
            Tipologi Sektor
        </option>

        <option
            value="klassen"
            {{ ($filter['metode'] ?? '') == 'klassen' ? 'selected' : '' }}
        >
            Tipologi Klassen
        </option>

    </select>

    {{-- =========================
        Tahun
    ========================== --}}
    <select name="tahun">

        @for($tahun = 2021; $tahun <= 2025; $tahun++)

            <option
                value="{{ $tahun }}"
                {{ ($filter['tahun'] ?? 2025) == $tahun ? 'selected' : '' }}
            >
                {{ $tahun }}
            </option>

        @endfor

    </select>

    {{-- =========================
        Submit
    ========================== --}}
    <button
        type="submit"
        class="btn-analysis"
    >
        Analisis
    </button>

</form>