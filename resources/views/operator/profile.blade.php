@extends('partials.layouts.operator')

@section('title', 'Profil Operator')

@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Profil Operator</h1>
        <p class="text-slate-500 mt-1">Kelola informasi profil dan foto Anda di sini.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Main Content -->
    <form action="{{ route('operator.profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        @csrf
        <div class="op-card-header">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Avatar Section -->
                <div x-data="{ photoPreview: null }" class="flex flex-col items-center space-y-4">
                    <!-- Hidden File Input -->
                    <input type="file" name="avatar" id="avatar" class="hidden" x-ref="photo" 
                           x-on:change="
                               const reader = new FileReader();
                               reader.onload = (e) => { photoPreview = e.target.result; };
                               reader.readAsDataURL($refs.photo.files[0]);
                           " accept="image/png, image/jpeg, image/gif">

                    <div class="w-32 h-32 rounded-full bg-emerald-100 overflow-hidden border-4 border-white shadow-lg relative group" @click="$refs.photo.click()">
                        <!-- Current Profile Photo -->
                        <img x-show="!photoPreview" src="{{ Auth::user()?->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()?->name ?? 'Siti') . '&background=EBF4FF&color=1E3A8A' }}" alt="Profile" class="w-full h-full object-cover">
                        <!-- New Profile Photo Preview -->
                        <span x-show="photoPreview" class="block w-full h-full bg-cover bg-no-repeat bg-center" x-bind:style="'background-image: url(\'' + photoPreview + '\');'" style="display: none;"></span>

                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <button type="button" @click="$refs.photo.click()" class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-semibold hover:bg-emerald-100 transition-colors">
                        Ubah Foto
                    </button>
                    <p class="text-xs text-slate-400 text-center max-w-[150px]">Format JPG, PNG atau GIF. Maksimal 2MB.</p>
                    @error('avatar')
                        <p class="text-xs text-red-500 mt-1 font-semibold text-center">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Section -->
                <div class="flex-1 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', Auth::user()?->name) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none" placeholder="Masukkan nama" required>
                            @error('name')
                                <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', Auth::user()?->email) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none" placeholder="Masukkan email" required>
                            @error('email')
                                <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Peran / Jabatan</label>
                            <input type="text" value="{{ ucfirst(Auth::user()?->role) }}" disabled class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">Nomor Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', Auth::user()?->phone) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all outline-none" placeholder="Masukkan nomor">
                            @error('phone')
                                <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-200 transition-all">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
