@extends('errors.layout')
@section('title', '503 - Layanan Tidak Tersedia')
@section('icon')
<i class="fa-solid fa-gears text-6xl text-gray-400"></i>
@endsection
@section('code', '503')
@section('title_id', 'Pemeliharaan Sistem / Layanan Tidak Tersedia')
@section('desc_id', 'Sistem kami sedang offline untuk pemeliharaan rutin atau peningkatan kapasitas. Mohon maaf atas ketidaknyamanan ini dan silakan coba lagi nanti.')

@section('custom_action')
<button onclick="window.location.reload()" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-[#cbaa4c] hover:bg-[#a6862f] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang</span>
</button>
@endsection