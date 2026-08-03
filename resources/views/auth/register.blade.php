<x-guest-layout>

    <div class="w-full max-w-[480px] mx-auto bg-white">

        {{-- LOGO --}}
        <div class="flex justify-center mb-6">
            <img src="{{ asset('images/logo-dpmptsp.png') }}" class="h-20 w-auto object-contain" alt="Logo DPMPTSP">
        </div>

        {{-- WELCOME HEADERS --}}
        <div class="text-center mb-2">
            <h2 class="text-3xl md:text-4xl font-extrabold text-[#1E5E3F] tracking-wider">SELAMAT DATANG</h2>
            <p class="text-[#476F5B] text-sm md:text-base font-bold mt-2">Dasbor Eksekutif untuk Investasi Sumatera</p>
        </div>

        {{-- SECTION TITLE --}}
        <div class="text-center mb-8">
            <h3 class="text-xl md:text-2xl font-black text-[#1E5E3F]">Daftar Akun</h3>
        </div>

        <form
            method="POST"
            action="{{ route('register') }}"
            class="space-y-4"
        >
            @csrf

            {{-- NAME --}}
            <div>
                <div
                    class="
                        relative
                        flex
                        items-center
                        min-h-[64px]
                        px-4
                        rounded-2xl
                        bg-[#ECEFF1]
                        border
                        border-transparent
                        focus-within:border-[#1E5E3F]
                        focus-within:ring-2
                        focus-within:ring-[#1E5E3F]/10
                        transition-all
                        duration-200
                    "
                >
                    {{-- ICON WRAPPER --}}
                    <div class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>

                    {{-- INPUT FIELD --}}
                    <div class="flex-1 ml-4">
                        <label
                            for="name"
                            class="
                                block
                                text-[10px]
                                font-bold
                                text-gray-400
                                uppercase
                                tracking-wider
                                mb-0.5
                            "
                        >
                            Nama
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Nama Lengkap"
                            required
                            autofocus
                            autocomplete="name"
                            class="
                                block
                                w-full
                                p-0
                                border-0
                                bg-transparent
                                text-sm
                                font-medium
                                text-gray-900
                                placeholder:text-gray-400
                                focus:ring-0
                                focus:outline-none
                            "
                        >
                    </div>
                </div>

                @error('name')
                    <p class="mt-2 text-xs font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- EMAIL --}}
            <div>
                <div
                    class="
                        relative
                        flex
                        items-center
                        min-h-[64px]
                        px-4
                        rounded-2xl
                        bg-[#ECEFF1]
                        border
                        border-transparent
                        focus-within:border-[#1E5E3F]
                        focus-within:ring-2
                        focus-within:ring-[#1E5E3F]/10
                        transition-all
                        duration-200
                    "
                >
                    {{-- ICON WRAPPER --}}
                    <div class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>

                    {{-- INPUT FIELD --}}
                    <div class="flex-1 ml-4">
                        <label
                            for="email"
                            class="
                                block
                                text-[10px]
                                font-bold
                                text-gray-400
                                uppercase
                                tracking-wider
                                mb-0.5
                            "
                        >
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="example@gmail.com"
                            required
                            autocomplete="username"
                            class="
                                block
                                w-full
                                p-0
                                border-0
                                bg-transparent
                                text-sm
                                font-medium
                                text-gray-900
                                placeholder:text-gray-400
                                focus:ring-0
                                focus:outline-none
                            "
                        >
                    </div>
                </div>

                @error('email')
                    <p class="mt-2 text-xs font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- PASSWORD --}}
            <div>
                <div
                    class="
                        relative
                        flex
                        items-center
                        min-h-[64px]
                        px-4
                        rounded-2xl
                        bg-[#ECEFF1]
                        border
                        border-transparent
                        focus-within:border-[#1E5E3F]
                        focus-within:ring-2
                        focus-within:ring-[#1E5E3F]/10
                        transition-all
                        duration-200
                    "
                >
                    {{-- ICON WRAPPER --}}
                    <div class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 rotate-45">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H3.75v-2.25c0-.527.21-1.033.586-1.414l5.379-5.379c.403-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                        </svg>
                    </div>

                    {{-- INPUT FIELD --}}
                    <div class="flex-1 ml-4 pr-8">
                        <label
                            for="password"
                            class="
                                block
                                text-[10px]
                                font-bold
                                text-gray-400
                                uppercase
                                tracking-wider
                                mb-0.5
                            "
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="••••••••••••"
                            required
                            autocomplete="new-password"
                            class="
                                block
                                w-full
                                p-0
                                border-0
                                bg-transparent
                                text-sm
                                font-medium
                                text-gray-900
                                placeholder:text-gray-400
                                focus:ring-0
                                focus:outline-none
                            "
                        >
                    </div>

                    {{-- SHOW PASSWORD EYE --}}
                    <button
                        type="button"
                        onclick="togglePassword('password', 'regEye')"
                        class="
                            absolute
                            right-4
                            text-gray-400
                            hover:text-[#1E5E3F]
                            transition-colors
                        "
                        aria-label="Tampilkan password"
                    >
                        <svg
                            id="regEye"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="mt-2 text-xs font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- PASSWORD CONFIRMATION --}}
            <div>
                <div
                    class="
                        relative
                        flex
                        items-center
                        min-h-[64px]
                        px-4
                        rounded-2xl
                        bg-[#ECEFF1]
                        border
                        border-transparent
                        focus-within:border-[#1E5E3F]
                        focus-within:ring-2
                        focus-within:ring-[#1E5E3F]/10
                        transition-all
                        duration-200
                    "
                >
                    {{-- ICON WRAPPER --}}
                    <div class="w-10 h-10 bg-black text-white flex items-center justify-center rounded-xl flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 rotate-45">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H3.75v-2.25c0-.527.21-1.033.586-1.414l5.379-5.379c.403-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                        </svg>
                    </div>

                    {{-- INPUT FIELD --}}
                    <div class="flex-1 ml-4 pr-8">
                        <label
                            for="password_confirmation"
                            class="
                                block
                                text-[10px]
                                font-bold
                                text-gray-400
                                uppercase
                                tracking-wider
                                mb-0.5
                            "
                        >
                            Konfirmasi Password
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="••••••••••••"
                            required
                            autocomplete="new-password"
                            class="
                                block
                                w-full
                                p-0
                                border-0
                                bg-transparent
                                text-sm
                                font-medium
                                text-gray-900
                                placeholder:text-gray-400
                                focus:ring-0
                                focus:outline-none
                            "
                        >
                    </div>

                    {{-- SHOW PASSWORD EYE --}}
                    <button
                        type="button"
                        onclick="togglePassword('password_confirmation', 'confirmRegEye')"
                        class="
                            absolute
                            right-4
                            text-gray-400
                            hover:text-[#1E5E3F]
                            transition-colors
                        "
                        aria-label="Tampilkan password"
                    >
                        <svg
                            id="confirmRegEye"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- REGIONAL SCOPE SELECTION --}}
            <div x-data="{ scopeType: '{{ old('scope_type', 'kabupaten') }}' }" class="space-y-3 pt-1 pb-2">
                <div class="p-3.5 rounded-2xl bg-[#EEF8F2] border border-[#CFE3D5]">
                    <label class="block text-xs font-bold text-[#1E5E3F] uppercase tracking-wider mb-1">
                        Pengajuan Wilayah Kerja (Regional Scope) <span class="text-rose-500">*</span>
                    </label>
                    <p class="text-[11px] text-slate-600 mb-2.5">
                        Pilih alokasi tingkat wilayah kerja data PDRB yang Anda ajukan ke Administrator:
                    </p>

                    <div class="grid grid-cols-2 gap-2 mb-2.5">
                        <label class="flex items-center gap-2 p-2 rounded-xl border bg-white cursor-pointer text-xs font-semibold"
                            :class="scopeType === 'provinsi' ? 'border-[#1E5E3F] text-[#1E5E3F] ring-1 ring-[#1E5E3F]' : 'border-gray-200 text-gray-600'">
                            <input type="radio" name="scope_type" value="provinsi" x-model="scopeType" class="text-[#1E5E3F] focus:ring-[#1E5E3F]">
                            <span>Provinsi (+ Sub-Kab)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border bg-white cursor-pointer text-xs font-semibold"
                            :class="scopeType === 'kabupaten' ? 'border-[#1E5E3F] text-[#1E5E3F] ring-1 ring-[#1E5E3F]' : 'border-gray-200 text-gray-600'">
                            <input type="radio" name="scope_type" value="kabupaten" x-model="scopeType" class="text-[#1E5E3F] focus:ring-[#1E5E3F]">
                            <span>Kabupaten / Kota</span>
                        </label>
                    </div>

                    {{-- PROVINSI SELECT --}}
                    <div x-show="scopeType === 'provinsi'" class="space-y-1">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Pilih Provinsi</label>
                        <select name="provinsi_id" class="w-full rounded-xl border-gray-300 focus:border-[#1E5E3F] focus:ring-[#1E5E3F] text-xs py-2 bg-white">
                            <option value="">-- Pilih Provinsi --</option>
                            @foreach($provinsis ?? [] as $prov)
                                <option value="{{ $prov->provinsi_id }}" @selected(old('provinsi_id') == $prov->provinsi_id)>
                                    {{ $prov->nama_provinsi }}
                                </option>
                            @endforeach
                        </select>
                        @error('provinsi_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- KABUPATEN SELECT --}}
                    <div x-show="scopeType === 'kabupaten'" class="space-y-1">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Pilih Kabupaten / Kota</label>
                        <select name="kabupaten_id" class="w-full rounded-xl border-gray-300 focus:border-[#1E5E3F] focus:ring-[#1E5E3F] text-xs py-2 bg-white">
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                            @foreach($kabupatens ?? [] as $kab)
                                <option value="{{ $kab->kab_id }}" @selected(old('kabupaten_id') == $kab->kab_id)>
                                    {{ $kab->nama_kabupaten }}
                                </option>
                            @endforeach
                        </select>
                        @error('kabupaten_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- REMEMBER & FORGOT --}}
            <div class="flex items-center justify-between pt-2 pb-4 text-xs font-semibold text-gray-400">
                <label
                    for="remember"
                    class="
                        flex
                        items-center
                        gap-2
                        cursor-pointer
                        hover:text-gray-600
                        transition-colors
                    "
                >
                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        class="
                            w-4
                            h-4
                            rounded
                            border-gray-300
                            text-[#1E5E3F]
                            focus:ring-[#1E5E3F]
                            focus:ring-offset-0
                        "
                    >
                    <span>Ingat akun saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="hover:text-[#1E5E3F] hover:underline"
                    >
                        Lupa Kata sandi
                    </a>
                @endif
            </div>

            {{-- SUBMIT --}}
            <button
                type="submit"
                class="
                    w-full
                    h-14
                    rounded-2xl
                    bg-[#4A705B]
                    text-white
                    text-sm
                    font-bold
                    hover:bg-[#3B5B49]
                    focus:outline-none
                    focus:ring-4
                    focus:ring-[#4A705B]/20
                    transition-all
                    duration-200
                    shadow-sm
                "
            >
                Daftar
            </button>

            {{-- LOGIN LINK --}}
            <p class="text-center text-xs text-gray-500 pt-2">
                Belum punya akun?
                <a
                    href="{{ route('login') }}"
                    class="
                        text-[#1E5E3F]
                        hover:text-[#3B5B49]
                        font-bold
                        hover:underline
                    "
                >
                    Masuk
                </a>
            </p>

            {{-- GOOGLE REGISTRATION --}}
            @if (Route::has('google.redirect'))
                <div class="relative flex py-3 items-center">
                    <div class="flex-grow border-t border-gray-100"></div>
                    <span class="flex-shrink mx-4 text-[10px] text-gray-400 uppercase font-bold tracking-wider">Atau</span>
                    <div class="flex-grow border-t border-gray-100"></div>
                </div>

                <a
                    href="{{ route('google.redirect') }}"
                    class="
                        flex
                        items-center
                        justify-center
                        gap-3
                        w-full
                        h-12
                        rounded-2xl
                        bg-white
                        border
                        border-gray-200
                        text-sm
                        font-semibold
                        text-gray-700
                        hover:bg-gray-50
                        transition-all
                        duration-200
                    "
                >
                    <svg class="w-5 h-5" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.6 20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.7 1.1 7.8 3l5.7-5.7C34 6 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20c11.6 0 19.3-8.2 19.3-19.7 0-1.3-.1-2.4-.3-3.3z"/>
                        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 18.9 12 24 12c3 0 5.7 1.1 7.8 3l5.7-5.7C34 6 29.3 4 24 4c-7.7 0-14.4 4.4-17.7 10.7z"/>
                        <path fill="#4CAF50" d="M24 44c5.1 0 9.8-1.9 13.3-5.1l-6.1-5.2C29.2 35.2 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.5 16.2 44 24 44z"/>
                        <path fill="#1976D2" d="M43.6 20H24v8h11.3c-.8 2.3-2.3 4.3-4.2 5.7l6.1 5.2c-.4.4 6.8-5 6.8-14.9 0-1.3-.1-2.7-.4-4z"/>
                    </svg>
                    <span>Masuk dengan Google</span>
                </a>
            @endif

        </form>

    </div>

    @push('scripts')
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (!input || !icon) {
                return;
            }

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3a3 3 0 0 1-4.243-4.243m0 0-3.65-3.65m0 0a3 3 0 0 1 4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532 3.29 3.29" />
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                `;
            }
        }
    </script>
    @endpush

</x-guest-layout>