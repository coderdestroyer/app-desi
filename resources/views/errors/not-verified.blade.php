@extends('errors.layout')
@section('title', 'Akun Belum Diverifikasi')
@section('icon')
<i class="fa-solid fa-user-clock text-6xl text-amber-500"></i>
@endsection
@section('code', 'Pending')
@section('title_id', 'Akun Belum Diverifikasi')
@section('desc_id', 'Akun Anda saat ini belum diverifikasi atau disetujui oleh Administrator. Silakan hubungi pihak pengelola sistem atau tunggu persetujuan sebelum dapat masuk.')

@section('custom_action')
@auth
<form method="POST" action="{{ route('logout') }}" class="inline">
    @csrf
    <button type="submit" class="btn-flat w-full sm:w-auto px-6 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Keluar</span>
    </button>
</form>
@else
<a href="{{ route('login') }}" class="btn-flat w-full sm:w-auto px-6 py-3 rounded-xl bg-[#145239] hover:bg-[#0b5d3d] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
    <i class="fa-solid fa-right-to-bracket"></i>
    <span>Masuk</span>
</a>
@endauth
@endsection
