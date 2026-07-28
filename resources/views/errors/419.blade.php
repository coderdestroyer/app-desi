@extends('errors.layout')
@section('title', '419 - Sesi Kedaluwarsa')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer Chronometer Ring -->
    <circle cx="60" cy="60" r="48" stroke="rgba(80, 137, 198, 0.2)" stroke-width="3" />
    <circle cx="60" cy="60" r="48" stroke="#5089C6" stroke-width="3" stroke-dasharray="30 270">
        <animateTransform 
            attributeName="transform" 
            type="rotate" 
            from="0 60 60" 
            to="360 60 60" 
            dur="8s" 
            repeatCount="indefinite" 
        />
    </circle>
    
    <!-- Clock ticks -->
    <line x1="60" y1="16" x2="60" y2="22" stroke="rgba(255, 255, 255, 0.4)" stroke-width="2"/>
    <line x1="60" y1="98" x2="60" y2="104" stroke="rgba(255, 255, 255, 0.4)" stroke-width="2"/>
    <line x1="16" y1="60" x2="22" y2="60" stroke="rgba(255, 255, 255, 0.4)" stroke-width="2"/>
    <line x1="98" y1="60" x2="104" y2="60" stroke="rgba(255, 255, 255, 0.4)" stroke-width="2"/>
    <!-- Animated Hourglass Group -->
    <g transform="translate(60, 60)">
        <animateTransform 
            attributeName="transform" 
            type="rotate" 
            values="0; 0; 180; 180; 360; 360" 
            keyTimes="0; 0.45; 0.55; 0.9; 0.95; 1"
            dur="6s" 
            repeatCount="indefinite" 
            additive="sum"
        />
        <g transform="translate(-20, -28)">
            <!-- Hourglass Frame -->
            <path d="M5 5 L35 5 M5 51 L35 51 M10 5 L10 12 C10 18, 17 25, 20 28 C23 25, 30 18, 30 12 L30 5" stroke="#5089C6" stroke-width="3.5" stroke-linecap="round"/>
            <path d="M10 51 L10 44 C10 38, 17 31, 20 28 C23 31, 30 38, 30 44 L30 51" stroke="#5089C6" stroke-width="3.5" stroke-linecap="round"/>
            
            <!-- Hourglass Sand Top -->
            <path d="M12 9 L28 9 C28 13, 24 18, 20 21 C16 18, 12 13, 12 9 Z" fill="#cbaa4c" opacity="0.8">
                <animate attributeName="opacity" values="0.8; 0; 0.8" dur="6s" keyTimes="0; 0.45; 1" repeatCount="indefinite"/>
            </g>
            
            <!-- Hourglass Sand Bottom -->
            <path d="M20 35 C17 38, 12 43, 12 47 L28 47 C28 43, 23 38, 20 35 Z" fill="#cbaa4c" opacity="0.1">
                <animate attributeName="opacity" values="0.1; 0.9; 0.1" dur="6s" keyTimes="0; 0.45; 1" repeatCount="indefinite"/>
            </g>
            
            <!-- Falling Sand Stream -->
            <line x1="20" y1="23" x2="20" y2="35" stroke="#cbaa4c" stroke-width="1.5" stroke-dasharray="2 3">
                <animate attributeName="stroke-dashoffset" values="0; 10" dur="1s" repeatCount="indefinite"/>
                <animate attributeName="opacity" values="1; 0; 1" dur="6s" keyTimes="0; 0.45; 1" repeatCount="indefinite"/>
            </line>
        </g>
    </g>
    
    <!-- Pulse indicators -->
    <circle cx="90" cy="35" r="4" fill="#cbaa4c">
        <animate attributeName="r" values="2;5;2" dur="2s" repeatCount="indefinite"/>
        <animate attributeName="opacity" values="0.4;1;0.4" dur="2s" repeatCount="indefinite"/>
    </circle>
</svg>
@endsection
@section('code', '419')
@section('title_id', 'Sesi Halaman Kedaluwarsa')
@section('desc_id', 'Sesi keamanan Anda telah berakhir karena tidak adanya aktivitas dalam beberapa waktu. Silakan muat ulang halaman ini untuk memperbarui token keamanan Anda.')
@section('title_en', 'Page Expired')
@section('desc_en', 'Your secure session has expired due to inactivity. Please refresh the page to obtain a new verification token and try again.')
@section('custom_action')
<button onclick="window.location.reload()" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#cbaa4c] to-[#e4c264] hover:from-[#e4c264] hover:to-[#cbaa4c] text-[#050b1a] font-bold text-sm shadow-lg shadow-yellow-900/30 flex items-center justify-center gap-2 border border-yellow-300/20">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang / Refresh</span>
</button>
@endsection