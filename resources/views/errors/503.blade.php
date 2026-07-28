@extends('errors.layout')
@section('title', '503 - Layanan Tidak Tersedia')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Wrench Wielding/Floating -->
    <g transform="translate(15, 15)">
        <animateTransform 
            attributeName="transform" 
            type="rotate" 
            values="0 45 45; 15 45 45; -5 45 45; 0 45 45" 
            dur="6s" 
            repeatCount="indefinite"
        />
        <!-- Wrench SVG outline -->
        <path d="M72 18 C78 12, 85 12, 85 12 C85 12, 85 19, 79 25 L55 49 L41 35 Z" fill="rgba(80, 137, 198, 0.25)" stroke="#5089C6" stroke-width="2.5"/>
        <path d="M42 34 L18 58 C15 61, 11 61, 8 58 C5 55, 5 51, 8 48 L32 24 Z" fill="rgba(203, 170, 76, 0.2)" stroke="#cbaa4c" stroke-width="2.5"/>
    </g>
    <!-- Large Gear -->
    <g transform="translate(75, 75)">
        <animateTransform 
            attributeName="transform" 
            type="rotate" 
            from="0" 
            to="360" 
            dur="12s" 
            repeatCount="indefinite" 
        />
        <circle cx="0" cy="0" r="22" stroke="#5089C6" stroke-width="5" fill="none"/>
        <circle cx="0" cy="0" r="10" stroke="#5089C6" stroke-width="3" fill="none"/>
        
        <!-- Gear teeth -->
        <rect x="-4" y="-28" width="8" height="8" rx="2" fill="#5089C6"/>
        <rect x="-4" y="20" width="8" height="8" rx="2" fill="#5089C6"/>
        <rect x="-28" y="-4" width="8" height="8" rx="2" fill="#5089C6"/>
        <rect x="20" y="-4" width="8" height="8" rx="2" fill="#5089C6"/>
        
        <rect x="-20" y="-20" width="8" height="8" rx="2" fill="#5089C6" transform="rotate(45)"/>
        <rect x="12" y="-20" width="8" height="8" rx="2" fill="#5089C6" transform="rotate(45)"/>
        <rect x="-20" y="12" width="8" height="8" rx="2" fill="#5089C6" transform="rotate(45)"/>
        <rect x="12" y="12" width="8" height="8" rx="2" fill="#5089C6" transform="rotate(45)"/>
    </g>
    <!-- Small Gear -->
    <g transform="translate(42, 85)">
        <animateTransform 
            attributeName="transform" 
            type="rotate" 
            from="0" 
            to="-360" 
            dur="8s" 
            repeatCount="indefinite" 
        />
        <circle cx="0" cy="0" r="14" stroke="#cbaa4c" stroke-width="4" fill="none"/>
        
        <!-- Gear teeth -->
        <rect x="-3" y="-18" width="6" height="6" rx="1.5" fill="#cbaa4c"/>
        <rect x="-3" y="12" width="6" height="6" rx="1.5" fill="#cbaa4c"/>
        <rect x="-18" y="-3" width="6" height="6" rx="1.5" fill="#cbaa4c"/>
        <rect x="12" y="-3" width="6" height="6" rx="1.5" fill="#cbaa4c"/>
    </g>
    <!-- Construction Stripe Banner -->
    <g transform="translate(10, 105)">
        <!-- Stripe base -->
        <rect x="0" y="0" width="100" height="6" fill="#cbaa4c" rx="3"/>
        <line x1="10" y1="0" x2="20" y2="6" stroke="#050b1a" stroke-width="4"/>
        <line x1="30" y1="0" x2="40" y2="6" stroke="#050b1a" stroke-width="4"/>
        <line x1="50" y1="0" x2="60" y2="6" stroke="#050b1a" stroke-width="4"/>
        <line x1="70" y1="0" x2="80" y2="6" stroke="#050b1a" stroke-width="4"/>
        <line x1="90" y1="0" x2="100" y2="6" stroke="#050b1a" stroke-width="4"/>
    </g>
</svg>
@endsection
@section('code', '503')
@section('title_id', 'Pemeliharaan Sistem / Layanan Tidak Tersedia')
@section('desc_id', 'Sistem kami sedang offline untuk pemeliharaan rutin atau peningkatan kapasitas. Mohon maaf atas ketidaknyamanan ini dan silakan coba lagi nanti.')
@section('title_en', 'Service Unavailable / Maintenance')
@section('desc_en', 'Our system is temporarily down for scheduled updates, capacity upgrades, or routine maintenance. Please try again shortly.')
@section('custom_action')
<button onclick="window.location.reload()" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#cbaa4c] to-[#e4c264] hover:from-[#e4c264] hover:to-[#cbaa4c] text-[#050b1a] font-bold text-sm shadow-lg shadow-yellow-900/30 flex items-center justify-center gap-2 border border-yellow-300/20">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang / Refresh</span>
</button>
@endsection