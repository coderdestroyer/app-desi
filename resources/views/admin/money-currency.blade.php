@extends('layouts.admin')

@section('title', 'Konversi Mata Uang')

@section('content')
    @php
        $currentDirection = old(
            'direction',
            $direction ?? 'idr_to_usd'
        );

        $isIdrToUsd = $currentDirection === 'idr_to_usd';
    @endphp

    <div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8">

        {{-- ====================================================== --}}
        {{-- CARD HIJAU INFORMASI KURS --}}
        {{-- ====================================================== --}}

        <section
            class="
                relative
                overflow-hidden
                rounded-2xl
                bg-gradient-to-r
                from-[#145239]
                via-[#0F8A5F]
                to-[#1E5D41]
                p-6
                shadow-xl
                md:p-8
            "
        >
            {{-- Ornamen latar --}}
            <div
                class="
                    pointer-events-none
                    absolute
                    -right-16
                    -top-20
                    h-64
                    w-64
                    rounded-full
                    bg-emerald-300/20
                    blur-3xl
                "
            ></div>

            <div
                class="
                    pointer-events-none
                    absolute
                    -bottom-20
                    left-1/3
                    h-56
                    w-56
                    rounded-full
                    bg-yellow-300/20
                    blur-3xl
                "
            ></div>

            <div
                class="
                    relative
                    z-10
                    flex
                    flex-col
                    gap-6
                    xl:flex-row
                    xl:items-center
                    xl:justify-between
                "
            >
                {{-- Judul --}}
                <div class="max-w-xl">
                    <div
                        class="
                            mb-4
                            inline-flex
                            items-center
                            gap-2
                            rounded-full
                            border
                            border-white/20
                            bg-emerald-900/30
                            px-3
                            py-1.5
                            text-xs
                            font-bold
                            text-emerald-50
                            backdrop-blur-sm
                        "
                    >
                        <i
                            class="
                                fa-solid
                                fa-money-bill-transfer
                                text-[#FFD54F]
                            "
                        ></i>

                        Currency Converter
                    </div>

                    <h1
                        class="
                            m-0
                            text-2xl
                            font-extrabold
                            tracking-tight
                            text-white
                            md:text-3xl
                        "
                    >
                        Konversi Mata Uang
                    </h1>

                    <p
                        class="
                            mb-0
                            mt-3
                            text-sm
                            leading-7
                            text-emerald-100
                        "
                    >
                        Konversi Rupiah dan Dollar Amerika menggunakan
                        kurs terbaru dari CurrencyFreaks.
                    </p>
                </div>

                {{-- Informasi kurs --}}
                <div
                    class="
                        grid
                        w-full
                        grid-cols-1
                        gap-4
                        sm:grid-cols-2
                        xl:max-w-3xl
                        xl:grid-cols-3
                    "
                >
                    <article
                        class="
                             w-full
                            rounded-xl
                            border
                            border-white/20
                            bg-white/10
                            p-4
                            backdrop-blur-sm
                        "
                    >
                        <p
                            class="
                                m-0
                                text-[11px]
                                font-semibold
                                uppercase
                                tracking-wider
                                text-emerald-100
                            "
                        >
                            Kurs Saat Ini
                        </p>

                        <p
                            class="
                                mb-0
                                mt-2
                                text-lg
                                font-extrabold
                                text-white
                            "
                        >
                            @if (isset($rate) && $rate)
                                1 USD =
                                Rp{{ number_format(
                                    $rate,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            @else
                                Tidak tersedia
                            @endif
                        </p>
                    </article>

                    <article
                        class="
                            w-full
                            rounded-xl
                            border
                            border-white/20
                            bg-white/10
                            p-4
                            backdrop-blur-sm
                        "
                    >
                        <p
                            class="
                                m-0
                                text-[11px]
                                font-semibold
                                uppercase
                                tracking-wider
                                text-emerald-100
                            "
                        >
                            Currency Pair
                        </p>

                        <p
                            id="heroDirection"
                            class="
                                mb-0
                                mt-2
                                text-lg
                                font-extrabold
                                text-[#FFD54F]
                            "
                        >
                            {{ $isIdrToUsd
                                ? 'IDR → USD'
                                : 'USD → IDR' }}
                        </p>
                    </article>

                    <article
                        class="
                            rounded-xl
                            border
                            border-white/20
                            bg-white/10
                            p-4
                            backdrop-blur-sm
                            sm:col-span-2
                            xl:col-span-1
                        "
                    >
                        <p
                            class="
                                m-0
                                text-[11px]
                                font-semibold
                                uppercase
                                tracking-wider
                                text-emerald-100
                            "
                        >
                            Terakhir Diperbarui
                        </p>

                        <p
                            class="
                                mb-0
                                mt-2
                                text-xs
                                font-semibold
                                leading-relaxed
                                text-white
                            "
                        >
                            {{ $updatedAt ?? '-' }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ====================================================== --}}
        {{-- PESAN ERROR --}}
        {{-- ====================================================== --}}

        @if ($errors->has('currency'))
            <div
                class="
                    mt-6
                    flex
                    items-start
                    gap-3
                    rounded-2xl
                    border
                    border-red-200
                    bg-red-50
                    p-4
                    text-sm
                    text-red-700
                "
            >
                <div
                    class="
                        flex
                        h-10
                        w-10
                        flex-shrink-0
                        items-center
                        justify-center
                        rounded-xl
                        bg-red-100
                    "
                >
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div>
                    <p class="m-0 font-bold">
                        Konversi gagal dilakukan
                    </p>

                    <p class="mb-0 mt-1 leading-relaxed">
                        {{ $errors->first('currency') }}
                    </p>
                </div>
            </div>
        @endif

        @if ($isStale ?? false)
            <div
                class="
                    mt-6
                    flex
                    items-start
                    gap-3
                    rounded-2xl
                    border
                    border-amber-200
                    bg-amber-50
                    p-4
                    text-sm
                    text-amber-800
                "
            >
                <div
                    class="
                        flex
                        h-10
                        w-10
                        flex-shrink-0
                        items-center
                        justify-center
                        rounded-xl
                        bg-amber-100
                    "
                >
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>

                <div>
                    <p class="m-0 font-bold">
                        Menggunakan kurs tersimpan
                    </p>

                    <p class="mb-0 mt-1 leading-relaxed">
                        {{ $rateError }}
                    </p>
                </div>
            </div>
        @endif

        {{-- ====================================================== --}}
        {{-- KARTU KONVERTER --}}
        {{-- ====================================================== --}}

        <section
            class="
                mt-6
                overflow-hidden
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-sm
            "
        >
            <header
                class="
                    flex
                    flex-col
                    gap-3
                    border-b
                    border-slate-100
                    bg-slate-50/60
                    p-5
                    md:flex-row
                    md:items-center
                    md:justify-between
                    md:p-6
                "
            >
                <div>
                    <h2
                        class="
                            m-0
                            text-lg
                            font-bold
                            text-slate-800
                        "
                    >
                        Kalkulator Mata Uang
                    </h2>

                    <p
                        class="
                            mb-0
                            mt-1
                            text-xs
                            text-slate-500
                        "
                    >
                        Klik tombol panah untuk menukar arah konversi.
                    </p>
                </div>

                <div
                    class="
                        inline-flex
                        w-fit
                        items-center
                        gap-2
                        rounded-full
                        border
                        px-4
                        py-2
                        text-xs
                        font-bold
                        {{ isset($rate) && $rate
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-red-200 bg-red-50 text-red-700' }}
                    "
                >
                    <span
                        class="
                            h-2.5
                            w-2.5
                            rounded-full
                            {{ isset($rate) && $rate
                                ? 'bg-emerald-500'
                                : 'bg-red-500' }}
                        "
                    ></span>

                    {{ isset($rate) && $rate
                        ? 'Kurs tersedia'
                        : 'Kurs tidak tersedia' }}
                </div>
            </header>

            <form
                method="POST"
                action="{{ route('admin.money-currency.convert') }}"
                id="currencyForm"
                class="p-5 md:p-6"
            >
                @csrf

                <input
                    type="hidden"
                    name="direction"
                    id="direction"
                    value="{{ $currentDirection }}"
                >

                <div
                    class="
                        grid
                        grid-cols-1
                        items-start
                        gap-5
                        xl:grid-cols-[minmax(210px,0.9fr)_minmax(240px,1fr)_56px_minmax(240px,1fr)]
                    "
                >
                    {{-- Nominal --}}
                    <div>
                        <label
                            for="amount"
                            id="amountLabel"
                            class="
                                mb-2
                                block
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            {{ $isIdrToUsd
                                ? 'Jumlah Rupiah'
                                : 'Jumlah Dollar' }}
                        </label>

                        <div class="relative">
                            <span
                                id="sourceSymbol"
                                class="
                                    pointer-events-none
                                    absolute
                                    inset-y-0
                                    left-0
                                    flex
                                    items-center
                                    pl-4
                                    text-sm
                                    font-extrabold
                                    text-emerald-700
                                "
                            >
                                {{ $isIdrToUsd ? 'Rp' : '$' }}
                            </span>

                            <input
                                type="text"
                                id="amount"
                                name="amount"
                                inputmode="{{ $isIdrToUsd
                                    ? 'numeric'
                                    : 'decimal' }}"
                                autocomplete="off"
                                value="{{ old(
                                    'amount',
                                    isset($amount) && $amount !== null
                                        ? (
                                            $isIdrToUsd
                                                ? number_format(
                                                    $amount,
                                                    0,
                                                    ',',
                                                    '.'
                                                )
                                                : number_format(
                                                    $amount,
                                                    2,
                                                    ',',
                                                    '.'
                                                )
                                        )
                                        : ''
                                ) }}"
                                placeholder="{{ $isIdrToUsd
                                    ? '19.000'
                                    : '10,00' }}"
                                class="
                                    h-14
                                    w-full
                                    rounded-xl
                                    border
                                    bg-white
                                    pl-12
                                    pr-16
                                    text-base
                                    font-semibold
                                    text-slate-800
                                    outline-none
                                    transition
                                    placeholder:font-normal
                                    placeholder:text-slate-300
                                    focus:border-emerald-500
                                    focus:ring-4
                                    focus:ring-emerald-100
                                    @error('amount')
                                        border-red-400
                                    @else
                                        border-slate-200
                                    @enderror
                                "
                                required
                            >

                            <span
                                id="sourceCode"
                                class="
                                    pointer-events-none
                                    absolute
                                    inset-y-0
                                    right-0
                                    flex
                                    items-center
                                    pr-4
                                    text-xs
                                    font-bold
                                    text-slate-400
                                "
                            >
                                {{ $isIdrToUsd ? 'IDR' : 'USD' }}
                            </span>
                        </div>

                        @error('amount')
                            <p
                                class="
                                    mb-0
                                    mt-2
                                    text-xs
                                    font-medium
                                    text-red-600
                                "
                            >
                                <i
                                    class="
                                        fa-solid
                                        fa-circle-exclamation
                                        mr-1
                                    "
                                ></i>

                                {{ $message }}
                            </p>
                        @else
                            <p
                                id="amountExample"
                                class="
                                    mb-0
                                    mt-2
                                    text-xs
                                    text-slate-400
                                "
                            >
                                {{ $isIdrToUsd
                                    ? 'Contoh: 19000 atau 19.000'
                                    : 'Contoh: 10 atau 10,50' }}
                            </p>
                        @enderror
                    </div>

                    {{-- Dari mata uang --}}
                    <div>
                        <label
                            class="
                                mb-2
                                block
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Dari Mata Uang
                        </label>

                        <div
                            class="
                                flex
                                h-14
                                items-center
                                gap-3
                                rounded-xl
                                border
                                border-slate-200
                                bg-white
                                px-4
                            "
                        >
                            <span
                                id="fromFlag"
                                class="
                                    flex
                                    h-9
                                    w-9
                                    flex-shrink-0
                                    items-center
                                    justify-center
                                    rounded-lg
                                    bg-slate-100
                                    text-xl
                                "
                            >
                                {{ $isIdrToUsd ? '🇮🇩' : '🇺🇸' }}
                            </span>

                            <div class="min-w-0">
                                <p
                                    id="fromCode"
                                    class="
                                        m-0
                                        text-sm
                                        font-bold
                                        text-slate-800
                                    "
                                >
                                    {{ $isIdrToUsd ? 'IDR' : 'USD' }}
                                </p>

                                <p
                                    id="fromName"
                                    class="
                                        mb-0
                                        mt-0.5
                                        truncate
                                        text-xs
                                        text-slate-400
                                    "
                                >
                                    {{ $isIdrToUsd
                                        ? 'Rupiah Indonesia'
                                        : 'Dollar Amerika' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol switch --}}
                    <div
                        class="
                            flex
                            items-center
                            justify-center
                            xl:mt-8
                            xl:pb-0
                        "
                    >
                        <button
                            type="button"
                            id="switchCurrency"
                            class="
                                group
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-full
                                border
                                border-emerald-200
                                bg-emerald-50
                                text-emerald-700
                                shadow-sm
                                transition
                                hover:scale-105
                                hover:border-emerald-600
                                hover:bg-emerald-600
                                hover:text-white
                                focus:outline-none
                                focus:ring-4
                                focus:ring-emerald-100
                            "
                            aria-label="Tukar arah konversi"
                            title="Tukar arah konversi"
                        >
                            <i
                                id="switchIcon"
                                class="
                                    fa-solid
                                    fa-arrow-right-arrow-left
                                    transition-transform
                                    duration-300
                                "
                            ></i>
                        </button>
                    </div>

                    {{-- Ke mata uang --}}
                    <div>
                        <label
                            class="
                                mb-2
                                block
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Ke Mata Uang
                        </label>

                        <div
                            class="
                                flex
                                h-14
                                items-center
                                gap-3
                                rounded-xl
                                border
                                border-slate-200
                                bg-slate-50
                                px-4
                            "
                        >
                            <span
                                id="toFlag"
                                class="
                                    flex
                                    h-9
                                    w-9
                                    flex-shrink-0
                                    items-center
                                    justify-center
                                    rounded-lg
                                    bg-slate-100
                                    text-xl
                                "
                            >
                                {{ $isIdrToUsd ? '🇺🇸' : '🇮🇩' }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p
                                    id="toCode"
                                    class="
                                        m-0
                                        text-sm
                                        font-bold
                                        text-slate-800
                                    "
                                >
                                    {{ $isIdrToUsd ? 'USD' : 'IDR' }}
                                </p>

                                <p
                                    id="toName"
                                    class="
                                        mb-0
                                        mt-0.5
                                        truncate
                                        text-xs
                                        text-slate-400
                                    "
                                >
                                    {{ $isIdrToUsd
                                        ? 'Dollar Amerika'
                                        : 'Rupiah Indonesia' }}
                                </p>
                            </div>

                            <i
                                class="
                                    fa-solid
                                    fa-lock
                                    text-xs
                                    text-slate-400
                                "
                            ></i>
                        </div>
                    </div>
                </div>

                {{-- Tombol aksi --}}
                <div
                    class="
                        mt-6
                        flex
                        flex-col
                        gap-3
                        sm:flex-row
                        sm:justify-end
                    "
                >
                    <button
                        type="button"
                        id="resetCurrency"
                        class="
                            inline-flex
                            h-11
                            items-center
                            justify-center
                            gap-2
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            px-5
                            text-sm
                            font-semibold
                            text-slate-600
                            transition
                            hover:border-slate-300
                            hover:bg-slate-50
                        "
                    >
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                        Reset
                    </button>

                    <button
                        type="submit"
                        {{ ! isset($rate) || ! $rate ? 'disabled' : '' }}
                        class="
                            inline-flex
                            h-11
                            items-center
                            justify-center
                            gap-2
                            rounded-xl
                            border
                            border-emerald-700
                            bg-emerald-600
                            px-6
                            text-sm
                            font-bold
                            text-white
                            shadow-sm
                            transition
                            hover:-translate-y-0.5
                            hover:bg-emerald-700
                            hover:shadow-md
                            focus:outline-none
                            focus:ring-4
                            focus:ring-emerald-200
                            disabled:cursor-not-allowed
                            disabled:border-slate-300
                            disabled:bg-slate-300
                            disabled:shadow-none
                        "
                    >
                        <i class="fa-solid fa-calculator"></i>
                        Hitung Konversi
                    </button>
                </div>
            </form>
        </section>

        {{-- ====================================================== --}}
        {{-- HASIL KONVERSI --}}
        {{-- ====================================================== --}}

        @if (
            isset($result) &&
            $result !== null &&
            isset($rate) &&
            $rate
        )
            <section
                class="
                    mt-6
                    grid
                    grid-cols-1
                    overflow-hidden
                    rounded-2xl
                    border
                    border-emerald-200
                    bg-gradient-to-r
                    from-emerald-50
                    via-white
                    to-yellow-50
                    shadow-sm
                    md:grid-cols-2
                "
            >
                <div
                    class="
                        border-b
                        border-emerald-100
                        p-6
                        md:border-b-0
                        md:border-r
                    "
                >
                    <p
                        class="
                            m-0
                            text-xs
                            font-bold
                            uppercase
                            tracking-wider
                            text-emerald-700
                        "
                    >
                        Hasil Konversi
                    </p>

                    <h2
                        class="
                            mb-0
                            mt-3
                            text-4xl
                            font-black
                            tracking-tight
                            text-slate-800
                        "
                    >
                        @if ($direction === 'idr_to_usd')
                            {{ number_format(
                                $result,
                                2,
                                ',',
                                '.'
                            ) }}

                            <span
                                class="
                                    text-xl
                                    text-emerald-700
                                "
                            >
                                USD
                            </span>
                        @else
                            <span
                                class="
                                    text-xl
                                    text-emerald-700
                                "
                            >
                                Rp
                            </span>

                            {{ number_format(
                                $result,
                                0,
                                ',',
                                '.'
                            ) }}
                        @endif
                    </h2>

                    <p
                        class="
                            mb-0
                            mt-3
                            text-sm
                            leading-relaxed
                            text-slate-500
                        "
                    >
                        @if ($direction === 'idr_to_usd')
                            Rp{{ number_format(
                                $amount,
                                0,
                                ',',
                                '.'
                            ) }}
                            setara dengan sekitar
                            {{ number_format(
                                $result,
                                2,
                                ',',
                                '.'
                            ) }}
                            Dollar Amerika.
                        @else
                            {{ number_format(
                                $amount,
                                2,
                                ',',
                                '.'
                            ) }}
                            USD setara dengan sekitar
                            Rp{{ number_format(
                                $result,
                                0,
                                ',',
                                '.'
                            ) }}.
                        @endif
                    </p>
                </div>

                <div class="p-6">
                    <p
                        class="
                            m-0
                            text-xs
                            font-bold
                            uppercase
                            tracking-wider
                            text-emerald-700
                        "
                    >
                        Kurs yang Digunakan
                    </p>

                    <h3
                        class="
                            mb-0
                            mt-3
                            text-2xl
                            font-extrabold
                            text-slate-800
                        "
                    >
                        1 USD =
                        Rp{{ number_format(
                            $rate,
                            2,
                            ',',
                            '.'
                        ) }}
                    </h3>

                    <p
                        class="
                            mb-0
                            mt-3
                            flex
                            items-center
                            gap-2
                            text-xs
                            text-slate-500
                        "
                    >
                        <i
                            class="
                                fa-regular
                                fa-clock
                                text-emerald-600
                            "
                        ></i>

                        Diperbarui: {{ $updatedAt }}
                    </p>
                </div>
            </section>
        @endif

        {{-- ====================================================== --}}
        {{-- INFORMASI DAN CATATAN --}}
        {{-- ====================================================== --}}

        <section
            class="
                mt-6
                grid
                grid-cols-1
                gap-5
                md:grid-cols-2
            "
        >
            <article
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-5
                    shadow-sm
                "
            >
                <div class="flex items-start gap-3">
                    <div
                        class="
                            flex
                            h-10
                            w-10
                            flex-shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-emerald-100
                            text-emerald-700
                        "
                    >
                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div>
                        <h3
                            class="
                                m-0
                                text-sm
                                font-bold
                                text-slate-800
                            "
                        >
                            Sumber Kurs
                        </h3>

                        <p
                            class="
                                mb-0
                                mt-2
                                text-xs
                                leading-6
                                text-slate-500
                            "
                        >
                            Data kurs diperoleh dari CurrencyFreaks
                            dan disimpan selama 10 menit agar aplikasi
                            tetap cepat serta hemat kuota API.
                        </p>
                    </div>
                </div>
            </article>

            <article
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-5
                    shadow-sm
                "
            >
                <div class="flex items-start gap-3">
                    <div
                        class="
                            flex
                            h-10
                            w-10
                            flex-shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-yellow-100
                            text-yellow-700
                        "
                    >
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h3
                            class="
                                m-0
                                text-sm
                                font-bold
                                text-slate-800
                            "
                        >
                            Catatan
                        </h3>

                        <p
                            class="
                                mb-0
                                mt-2
                                text-xs
                                leading-6
                                text-slate-500
                            "
                        >
                            Hasil konversi bersifat informatif.
                            Nilai transaksi sebenarnya dapat berbeda
                            karena spread, biaya, atau kebijakan bank.
                        </p>
                    </div>
                </div>
            </article>
        </section>

        <footer
            class="
                pb-1
                pt-8
                text-center
                text-xs
                text-slate-400
            "
        >
            Copyright &copy;
            {{ date('Y') }}
            DPMPTSP Provinsi Sumatera Utara
        </footer>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('currencyForm');
            const directionInput = document.getElementById('direction');
            const amountInput = document.getElementById('amount');

            const amountLabel = document.getElementById('amountLabel');
            const amountExample = document.getElementById('amountExample');
            const sourceSymbol = document.getElementById('sourceSymbol');
            const sourceCode = document.getElementById('sourceCode');

            const fromFlag = document.getElementById('fromFlag');
            const fromCode = document.getElementById('fromCode');
            const fromName = document.getElementById('fromName');

            const toFlag = document.getElementById('toFlag');
            const toCode = document.getElementById('toCode');
            const toName = document.getElementById('toName');

            const heroDirection = document.getElementById('heroDirection');
            const switchButton = document.getElementById('switchCurrency');
            const switchIcon = document.getElementById('switchIcon');
            const resetButton = document.getElementById('resetCurrency');

            if (
                ! form ||
                ! directionInput ||
                ! amountInput ||
                ! switchButton
            ) {
                return;
            }

            function isIdrToUsd() {
                return directionInput.value === 'idr_to_usd';
            }

            function updateInterface(clearAmount = false) {
                const idrToUsd = isIdrToUsd();

                amountLabel.textContent = idrToUsd
                    ? 'Jumlah Rupiah'
                    : 'Jumlah Dollar';

                sourceSymbol.textContent = idrToUsd
                    ? 'Rp'
                    : '$';

                sourceCode.textContent = idrToUsd
                    ? 'IDR'
                    : 'USD';

                fromFlag.textContent = idrToUsd
                    ? '🇮🇩'
                    : '🇺🇸';

                fromCode.textContent = idrToUsd
                    ? 'IDR'
                    : 'USD';

                fromName.textContent = idrToUsd
                    ? 'Rupiah Indonesia'
                    : 'Dollar Amerika';

                toFlag.textContent = idrToUsd
                    ? '🇺🇸'
                    : '🇮🇩';

                toCode.textContent = idrToUsd
                    ? 'USD'
                    : 'IDR';

                toName.textContent = idrToUsd
                    ? 'Dollar Amerika'
                    : 'Rupiah Indonesia';

                if (heroDirection) {
                    heroDirection.textContent = idrToUsd
                        ? 'IDR → USD'
                        : 'USD → IDR';
                }

                amountInput.placeholder = idrToUsd
                    ? '19.000'
                    : '10,00';

                amountInput.inputMode = idrToUsd
                    ? 'numeric'
                    : 'decimal';

                if (amountExample) {
                    amountExample.textContent = idrToUsd
                        ? 'Contoh: 19000 atau 19.000'
                        : 'Contoh: 10 atau 10,50';
                }

                if (clearAmount) {
                    amountInput.value = '';
                }
            }

            function formatIdr(value) {
                const numericValue = value.replace(
                    /[^0-9]/g,
                    ''
                );

                if (! numericValue) {
                    return '';
                }

                return new Intl.NumberFormat('id-ID')
                    .format(Number(numericValue));
            }

            function formatUsd(value) {
                let cleanedValue = value.replace(
                    /[^0-9,.]/g,
                    ''
                );

                const commaPosition = cleanedValue.indexOf(',');

                if (commaPosition !== -1) {
                    const integerPart = cleanedValue
                        .slice(0, commaPosition)
                        .replace(/[.,]/g, '');

                    const decimalPart = cleanedValue
                        .slice(commaPosition + 1)
                        .replace(/[.,]/g, '')
                        .slice(0, 2);

                    return integerPart + ',' + decimalPart;
                }

                const dotPosition = cleanedValue.indexOf('.');

                if (dotPosition !== -1) {
                    const integerPart = cleanedValue
                        .slice(0, dotPosition)
                        .replace(/[.,]/g, '');

                    const decimalPart = cleanedValue
                        .slice(dotPosition + 1)
                        .replace(/[.,]/g, '')
                        .slice(0, 2);

                    return integerPart + '.' + decimalPart;
                }

                return cleanedValue.replace(/[.,]/g, '');
            }

            amountInput.addEventListener('input', function () {
                amountInput.value = isIdrToUsd()
                    ? formatIdr(amountInput.value)
                    : formatUsd(amountInput.value);
            });

            switchButton.addEventListener('click', function () {
                directionInput.value = isIdrToUsd()
                    ? 'usd_to_idr'
                    : 'idr_to_usd';

                switchIcon.classList.add('rotate-180');

                window.setTimeout(function () {
                    switchIcon.classList.remove('rotate-180');
                }, 300);

                updateInterface(true);
                amountInput.focus();
            });

            resetButton?.addEventListener('click', function () {
                directionInput.value = 'idr_to_usd';

                updateInterface(true);
                amountInput.focus();
            });

            form.addEventListener('submit', function () {
                if (isIdrToUsd()) {
                    amountInput.value = amountInput.value.replace(
                        /[^0-9]/g,
                        ''
                    );

                    return;
                }

                amountInput.value = amountInput.value
                    .replace(',', '.')
                    .replace(/[^0-9.]/g, '');
            });

            updateInterface(false);
        });
    </script>
@endpush