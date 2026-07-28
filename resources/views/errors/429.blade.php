@extends('errors.layout')
@section('title', '429 - Terlalu Banyak Permintaan')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Speedometer Gauge Dial -->
    <path d="M20 80 A 45 45 0 0 1 100 80" stroke="rgba(255, 255, 255, 0.1)" stroke-width="8" stroke-linecap="round"/>
    
    <!-- Speed Zones (Green, Gold, Red) -->
    <path d="M20 80 A 45 45 0 0 1 50 40" stroke="#5089C6" stroke-width="8" stroke-linecap="round" opacity="0.6"/>
    <path d="M50 40 A 45 45 0 0 1 80 40" stroke="#cbaa4c" stroke-width="8" opacity="0.8"/>
    <path d="M80 40 A 45 45 0 0 1 100 80" stroke="#f43f5e" stroke-width="8" stroke-linecap="round"/>
    <!-- Speedometer Needle -->
    <g transform="translate(60, 75)">
        <animateTransform 
            attributeName="transform" 
            type="rotate" 
            values="45; 55; 48; 53; 45" 
            dur="0.8s" 
            repeatCount="indefinite"
            additive="sum"
        />
        <!-- Needle shaft -->
        <path d="M0 -3 L32 -32 C35 -35, 37 -30, 32 -32 L0 3 Z" fill="#f43f5e"/>
        <!-- Needle pin -->
        <circle cx="0" cy="0" r="7" fill="#050b1a" stroke="#f43f5e" stroke-width="3"/>
        <circle cx="0" cy="0" r="2.5" fill="#f43f5e"/>
    </g>
    <!-- Pulsing Alert Icon -->
    <g transform="translate(48, 88)">
        <!-- Triangle -->
        <path d="M12 2 L22 19 C23 21, 21 23, 19 23 L5 23 C3 23, 1 21, 2 19 Z" fill="rgba(244, 63, 94, 0.2)" stroke="#f43f5e" stroke-width="2" stroke-linejoin="round"/>
        <!-- Flashing exclamation mark -->
        <text x="12" y="17" font-family="'Poppins', sans-serif" font-size="12" font-weight="bold" fill="#f43f5e" text-anchor="middle">
            <animate attributeName="opacity" values="0.2;1;0.2" dur="1s" repeatCount="indefinite"/>
            !
        </text>
    </g>
</svg>
@endsection
@section('code', '429')
@section('title_id', 'Terlalu Banyak Permintaan')
@section('desc_id', 'Batas akses terlampaui. Anda telah melakukan terlalu banyak permintaan dalam waktu singkat. Harap tunggu sebentar sebelum mencoba kembali.')
@section('title_en', 'Too Many Requests')
@section('desc_en', 'Rate limit exceeded. You have sent too many requests in a short period of time. Please wait a moment and try again.')
@section('custom_action')
<button onclick="window.location.reload()" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#cbaa4c] to-[#e4c264] hover:from-[#e4c264] hover:to-[#cbaa4c] text-[#050b1a] font-bold text-sm shadow-lg shadow-yellow-900/30 flex items-center justify-center gap-2 border border-yellow-300/20">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang / Refresh</span>
</button>