@extends('errors.layout')
@section('title', '500 - Kesalahan Server Internal')
@section('icon')
<i class="fa-solid fa-triangle-exclamation text-6xl text-red-500"></i>
@endsection
@section('code', '500')
@section('title_id', 'Kesalahan Server Internal')
@section('desc_id', 'Maaf, terjadi kendala teknis pada sistem internal kami. Tim pengembang kami telah diberitahu dan sedang berusaha memperbaikinya segera.')

@section('custom_action')
<button onclick="window.location.reload()" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-[#145239] hover:bg-[#0b5d3d] text-white font-bold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang</span>
</button>
@endsection