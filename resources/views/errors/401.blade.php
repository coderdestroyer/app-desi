@extends('errors.layout')
@section('title', '401 - Tidak Diotorisasi')
@section('icon')
<i class="fa-solid fa-lock text-6xl text-amber-400"></i>
@endsection
@section('code', '401')
@section('title_id', 'Diperlukan Otorisasi')
@section('desc_id', 'Halaman ini dilindungi dan memerlukan autentikasi. Silakan masuk (login) ke akun Anda terlebih dahulu untuk mengakses halaman.')

@section('custom_action')
<a href="{{ route('login') }}" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-[#145239] hover:bg-[#0b5d3d] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
    <i class="fa-solid fa-right-to-bracket"></i>
    <span>Masuk</span>
</a>
@endsection