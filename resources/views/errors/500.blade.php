@extends('errors.layout')
@section('title', '500 - Kesalahan Server Internal')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Server Rack Structure -->
    <rect x="25" y="15" width="70" height="90" rx="8" fill="rgba(80, 137, 198, 0.05)" stroke="#5089C6" stroke-width="3"/>
    
    <!-- Shelf 1 -->
    <rect x="32" y="25" width="56" height="20" rx="3" fill="#0b1120" stroke="rgba(80, 137, 198, 0.5)" stroke-width="1.5"/>
    <circle cx="40" cy="35" r="2.5" fill="#f43f5e">
        <animate attributeName="opacity" values="0.2;1;0.2" dur="1.2s" repeatCount="indefinite"/>
    </circle>
    <circle cx="48" cy="35" r="2" fill="#5089C6"/>
    <line x1="56" y1="35" x2="80" y2="35" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" stroke-linecap="round"/>
    <!-- Shelf 2 -->
    <rect x="32" y="50" width="56" height="20" rx="3" fill="#0b1120" stroke="rgba(80, 137, 198, 0.5)" stroke-width="1.5"/>
    <circle cx="40" cy="60" r="2.5" fill="#f43f5e">
        <animate attributeName="opacity" values="1;0.2;1" dur="1.5s" repeatCount="indefinite"/>
    </circle>
    <circle cx="48" cy="60" r="2" fill="#cbaa4c"/>
    <line x1="56" y1="60" x2="74" y2="60" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" stroke-linecap="round"/>
    <!-- Shelf 3 -->
    <rect x="32" y="75" width="56" height="20" rx="3" fill="#0b1120" stroke="rgba(80, 137, 198, 0.5)" stroke-width="1.5"/>
    <circle cx="40" cy="85" r="2.5" fill="#f43f5e">
        <animate attributeName="opacity" values="0.1;1;0.1" dur="0.8s" repeatCount="indefinite"/>
    </circle>
    <circle cx="48" cy="85" r="2" fill="#5089C6"/>
    <line x1="56" y1="85" x2="82" y2="85" stroke="rgba(255, 255, 255, 0.2)" stroke-width="2" stroke-linecap="round"/>
    <!-- Floating Binary Particles (0 and 1) -->
    <g font-family="'Courier New', monospace" font-size="8" font-weight="bold" fill="#f43f5e" opacity="0.8">
        <text x="14" y="30">
            <animate attributeName="y" values="30;12" dur="3s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.8;0" dur="3s" repeatCount="indefinite"/>
            0
        </text>
        <text x="102" y="45">
            <animate attributeName="y" values="45;20" dur="4s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.8;0" dur="4s" repeatCount="indefinite"/>
            1
        </text>
        <text x="16" y="80">
            <animate attributeName="y" values="80;60" dur="3.5s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.6;0" dur="3.5s" repeatCount="indefinite"/>
            1
        </text>
        <text x="100" y="85">
            <animate attributeName="y" values="85;55" dur="2.5s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.7;0" dur="2.5s" repeatCount="indefinite"/>
            0
        </text>
    </g>
    <!-- Cloud Danger Icon Floating -->
    <g transform="translate(10, 8)">
        <animateTransform 
            attributeName="transform" 
            type="translate" 
            values="0,0; 0,-4; 0,0" 
            dur="3.8s" 
            repeatCount="indefinite"
            additive="sum"
        />
        <!-- Cloud outline -->
        <!-- Warning sign -->
    </g>
</svg>
@endsection
@section('code', '500')
@section('title_id', 'Kesalahan Server Internal')
@section('desc_id', 'Maaf, terjadi kendala teknis pada sistem internal kami. Tim pengembang kami telah diberitahu dan sedang berusaha memperbaikinya segera.')
@section('title_en', 'Internal Server Error')
@section('desc_en', 'Sorry, the server encountered an unexpected condition that prevented it from fulfilling your request. We are working on fixing it.')
@section('custom_action')
<button onclick="window.location.reload()" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#035397] to-[#5089C6] hover:from-[#5089C6] hover:to-[#035397] text-white font-bold text-sm shadow-lg shadow-blue-900/30 flex items-center justify-center gap-2 border border-blue-400/20">
    <i class="fa-solid fa-rotate-right"></i>
    <span>Muat Ulang / Refresh</span>
</button>
@endsection