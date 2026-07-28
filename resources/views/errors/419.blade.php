@extends('errors.layout')
@section('title', '419 - Sesi Kedaluwarsa')
@section('icon')
<i class="fa-solid fa-hourglass-end text-6xl text-amber-500"></i>
@endsection
@section('code', '419')
@section('title_id', 'Sesi Halaman Kedaluwarsa')
@section('desc_id', 'Sesi keamanan Anda telah berakhir karena tidak adanya aktivitas dalam beberapa waktu. Silakan muat ulang halaman ini untuk memperbarui token keamanan Anda.')

@section('custom_action')
<button onclick="window.location.reload()" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-[#cbaa4c] hover:bg-[#a6862f] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang</span>
</button>
@endsection