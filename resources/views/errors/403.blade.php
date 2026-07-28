@extends('errors.layout')
@section('title', '403 - Akses Ditolak')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Security Shield Outline -->
    <path d="M60 15 C80 15, 95 20, 95 20 C95 20, 95 65, 60 95 C25 65, 25 20, 25 20 C25 20, 40 15, 60 15 Z" 
          fill="rgba(244, 63, 94, 0.05)" stroke="rgba(244, 63, 94, 0.3)" stroke-width="3" stroke-linejoin="round"/>
          
    <path d="M60 22 C76 22, 88 26, 88 26 C88 26, 88 60, 60 85 C32 60, 32 26, 32 26 C32 26, 44 22, 60 22 Z" 
          fill="none" stroke="rgba(244, 63, 94, 0.1)" stroke-width="1.5" stroke-linejoin="round"/>
    <!-- Glowing Padlock -->
    <g transform="translate(42, 38)">
        <!-- Shackle -->
        <path d="M10 18 V11 A 8 8 0 0 1 26 11 V18" stroke="#f43f5e" stroke-width="4.5" stroke-linecap="round" fill="none"/>
        <path d="M10 18 V11 A 8 8 0 0 1 26 11 V18" stroke="#ffffff" stroke-width="1" stroke-linecap="round" fill="none" opacity="0.4"/>
        
        <!-- Lock Body -->
        <rect x="3" y="17" width="30" height="24" rx="6" fill="#0b1120" stroke="#f43f5e" stroke-width="4"/>
        <rect x="7" y="21" width="22" height="16" rx="4" fill="rgba(244, 63, 94, 0.1)"/>
        
        <!-- Keyhole -->
        <circle cx="18" cy="27" r="3" fill="#f43f5e"/>
        <path d="M18 29 L18 34" stroke="#f43f5e" stroke-width="2.5" stroke-linecap="round"/>
    </g>
    <!-- Scan Line Grid Mask -->
    <g>
        <animateTransform 
            attributeName="transform" 
            type="translate" 
            values="0, 18; 0, 85; 0, 18" 
            dur="4s" 
            repeatCount="indefinite"
        />
        <!-- Scanning laser line -->
        <line x1="22" y1="0" x2="98" y2="0" stroke="#f43f5e" stroke-width="2.5" opacity="0.8" />
        <line x1="22" y1="0" x2="98" y2="0" stroke="#ffffff" stroke-width="1" opacity="0.4" />
        <!-- Laser glow -->
        <polygon points="22,-4 98,-4 98,4 22,4" fill="url(#laserGlow)" opacity="0.15"/>
    </g>
    <!-- Pulsing Alerts -->
    <circle cx="60" cy="98" r="3" fill="#f43f5e">
        <animate attributeName="opacity" values="0.3;1;0.3" dur="1.5s" repeatCount="indefinite"/>
    </circle>
    
    <defs>
        <linearGradient id="laserGlow" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#f43f5e"/>
            <stop offset="100%" stop-color="#f43f5e" stop-opacity="0"/>
        </linearGradient>
    </defs>
</svg>
@endsection
@section('code', '403')
@section('title_id', 'Akses Ditolak / Dilarang')
@section('desc_id', 'Anda tidak memiliki hak akses atau izin yang diperlukan untuk membuka halaman ini. Hubungi administrator sistem jika Anda merasa ini adalah kesalahan.')
@section('title_en', 'Access Forbidden')
@section('desc_en', 'You do not have the required role or authorization to access this page. Please contact the system administrator if you believe this is an error.')