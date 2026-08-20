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
        Kabupaten
    ========================== --}}
    <select name="kabupaten">

        <option value="">
            Pilih Kabupaten / Kota
        </option>

        @foreach($kabupaten as $item)

            <option
                value="{{ $item->kab_id }}"
                data-provinsi="{{ $item->provinsi_id }}"
                {{ ($filter['kabupaten'] ?? null) == $item->kab_id ? 'selected' : '' }}
            >
                {{ Str::title(Str::lower($item->nama_kabupaten)) }} 
            </option>

        @endforeach

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