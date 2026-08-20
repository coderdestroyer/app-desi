<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analisis Sektor | DPMPTSP Provinsi Sumatera Utara</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

    @vite([
        'resources/css/navbar.css',
        'resources/css/home.css',
        'resources/css/about.css',
        'resources/css/analysis.css',
        'resources/css/comparison.css',

        'resources/js/navbar.js',
        'resources/js/home.js',
        'resources/js/about.js',
        'resources/js/analysis.js',
        'resources/js/comparison.js',
    ])
</head>
<body>

{{-- NAVBAR --}}
@include('partials.landing.navbar')



<section class="comparison-page">


    {{-- TITLE --}}
    <h1 class="comparison-title">
        Dashboard Analisis Sektor
    </h2>


    {{-- FILTER --}}
    <form 
        method="GET"
        action="{{ route('comparison') }}"
        class="comparison-filter"
    >
        <select name="provinsi">

            <option value="">
                Pilih Provinsi
            </option>

            @foreach($provinsi as $item)

                <option
                    value="{{ $item->provinsi_id }}"
                    @selected($filter['provinsi']==$item->provinsi_id)
                >
                    {{ Str::title(Str::lower($item->nama_provinsi)) }}                
                </option>

            @endforeach

        </select>

        <select name="kabupaten">

            <option value="">
                Pilih Kabupaten / Kota
            </option>

            @foreach($kabupaten as $item)

                <option
                    value="{{ $item->kab_id }}"
                    data-provinsi="{{ $item->provinsi_id }}"
                    @selected(($filter['kabupaten'] ?? null) == $item->kab_id)
                >
                    {{ Str::title(Str::lower($item->nama_kabupaten)) }}
                </option>

            @endforeach

        </select>

        <select name="sektor">

            <option value="">
                Pilih Sektor
            </option>

            @foreach($sektor as $item)

                <option
                    value="{{ $item->sektor_id }}"
                    @selected($filter['sektor']==$item->sektor_id)
                >
                    {{ Str::title(Str::lower($item->nama_sektor)) }}                </option>

            @endforeach

        </select>

        <select name="tahun_awal">


            @for($i=2021;$i<=2025;$i++)

            <option 
                value="{{ $i }}"
                {{ $filter['tahun_awal']==$i ? 'selected' : '' }}
            >

                {{ $i }}

            </option>

            @endfor


        </select>




        <select name="tahun_akhir">


            @for($i=2021;$i<=2025;$i++)

            <option 
                value="{{ $i }}"
                {{ $filter['tahun_akhir']==$i ? 'selected' : '' }}
            >

                {{ $i }}

            </option>

            @endfor


        </select>




        <button type="submit">

            Analisis

        </button>



    </form>

    @if($dashboard)

    {{-- SUMMARY --}}
    <div class="comparison-summary">

        <div class="summary-card">

            <h2>

                {{ $dashboard['summary']['growth']['average'] ?? '-' }}%

            </h2>

            <p>

                Pertumbuhan rata-rata

            </p>

            <small>

                Tertinggi pada
                {{ $dashboard['summary']['growth']['highest']['tahun'] ?? '-' }}

                ({{ $dashboard['summary']['growth']['highest']['nilai'] ?? '-' }}%)

            </small>

        </div>

        <div class="summary-card">

            <h2>

                {{ $dashboard['summary']['contribution']['average'] ?? '-' }}%

            </h2>

            <p>

                Kontribusi rata-rata

            </p>

            <small>

                Tertinggi pada
                {{ $dashboard['summary']['contribution']['highest']['tahun'] ?? '-' }}

                ({{ $dashboard['summary']['contribution']['highest']['nilai'] ?? '-' }}%)

            </small>

        </div>

        <div class="summary-card">

            <h2>

                {{ $dashboard['summary']['lq']['nilai'] ?? '-' }}

            </h2>

            <p>

                Nilai LQ Terakhir
                ({{ $dashboard['summary']['lq']['tahun'] ?? '-' }})

            </p>

            <small>

                {{ $dashboard['summary']['lq']['status'] ?? '-' }}

                <br>

                @if(($dashboard['summary']['lq']['change'] ?? 0) >= 0)

                    Naik

                @else

                    Turun

                @endif

                {{ abs($dashboard['summary']['lq']['change'] ?? 0) }}%

                dari tahun sebelumnya

            </small>

        </div>

        <div class="summary-card">

            <h2>

                {{ $dashboard['summary']['tipologi']['kategori'] ?? '-' }}

            </h2>

            <p>

                Status Tipologi Klassen Terakhir

            </p>

            <small>

                {{ $dashboard['summary']['tipologi']['movement'] ?? '-' }}

            </small>

        </div>

    </div>


    {{-- CHART --}}

    <div class="comparison-chart-grid">

        <div class="chart-card">
            <h3>Tren Pertumbuhan</h3>
            <canvas id="growthChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Tren Kontribusi</h3>
            <canvas id="contributionChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Tren Nilai LQ</h3>
            <canvas id="lqChart"></canvas>
        </div>

        <div class="chart-card">
            <h3>Tren Nilai SSA</h3>
            <canvas id="ssaChart"></canvas>
        </div>

    </div>


    {{-- TABLE --}}
    <div class="table-card">

        <h3>Ringkasan Hasil Analisis Sektor Per Tahun</h3>

        <table>

            <thead>
                <tr>
                    <th>Tahun</th>
                    <th>Pertumbuhan</th>
                    <th>Kontribusi</th>
                    <th>LQ</th>
                    <th>SSA</th>
                    <th>Status LQ</th>
                    <th>Kuadran</th>
                    <th>Kategori</th>
                </tr>
            </thead>

            <tbody>

            @forelse($dashboard['table'] ?? [] as $row)

                <tr>

                    <td>{{ $row['tahun'] }}</td>

                    <td>{{ number_format($row['growth'], 2) }}%</td>

                    <td>{{ number_format($row['contribution'], 2) }}%</td>

                    <td>{{ number_format($row['lq'], 3) }}</td>

                    <td>{{ number_format($row['ssa'], 2) }}</td>

                    <td>
                        <span class="status-badge {{ $row['status_lq'] === 'Basis' ? 'basis' : 'non-basis' }}">
                            {{ $row['status_lq'] }}
                        </span>
                    </td>

                    <td>{{ $row['kuadran'] }}</td>

                    <td>
                        <span class="status-badge
                            @switch($row['kategori'])
                                @case('Sektor Unggulan')
                                    unggulan
                                    @break

                                @case('Sektor Potensial')
                                    potensial
                                    @break

                                @case('Sektor Berkembang')
                                    berkembang
                                    @break

                                @default
                                    tertinggal
                            @endswitch
                        ">
                            {{ $row['kategori'] }}
                        </span>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="text-center">
                        Pilih kabupaten dan sektor untuk menampilkan data.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    @else

    <div class="comparison-empty">

        <h3>Analisis Sektor</h3>

        <p>
            Pilih kabupaten, sektor, dan rentang tahun,
            kemudian klik <strong>Analisis</strong>.
        </p>

    </div>

    @endif

    <script>
    window.comparisonCharts =
        @json($dashboard['charts'] ?? []);
    </script>

</section>

</body>
</html>
