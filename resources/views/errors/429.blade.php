@extends('errors.layout')
@section('title', '429 - Terlalu Banyak Permintaan')
@section('icon')
<i class="fa-solid fa-gauge-high text-6xl text-amber-500"></i>
@endsection
@section('code', '429')
@section('title_id', 'Terlalu Banyak Permintaan')
@section('desc_id', 'Batas akses terlampaui. Anda telah melakukan terlalu banyak permintaan dalam waktu singkat. Harap tunggu sebentar sebelum mencoba kembali.')

@section('custom_action')
<button onclick="window.location.reload()" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-[#cbaa4c] hover:bg-[#a6862f] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang</span>
</button>
@endsection