@extends('errors.layout')
@section('title', '404 - Halaman Tidak Ditemukan')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Map Grid Background -->
    <rect x="10" y="10" width="100" height="100" rx="15" fill="rgba(3, 83, 151, 0.1)" stroke="rgba(80, 137, 198, 0.2)" stroke-width="1.5"/>
    <line x1="30" y1="10" x2="30" y2="110" stroke="rgba(80, 137, 198, 0.1)" stroke-width="1"/>
    <line x1="60" y1="10" x2="60" y2="110" stroke="rgba(80, 137, 198, 0.1)" stroke-width="1"/>
    <line x1="90" y1="10" x2="90" y2="110" stroke="rgba(80, 137, 198, 0.1)" stroke-width="1"/>
    <line x1="10" y1="30" x2="110" y2="30" stroke="rgba(80, 137, 198, 0.1)" stroke-width="1"/>
    <line x1="10" y1="60" x2="110" y2="60" stroke="rgba(80, 137, 198, 0.1)" stroke-width="1"/>
    <line x1="10" y1="90" x2="110" y2="90" stroke="rgba(80, 137, 198, 0.1)" stroke-width="1"/>
    
    <!-- Pulsing Radar Coordinates -->
    <circle cx="60" cy="60" r="8" fill="rgba(203, 170, 76, 0.2)">
        <animate attributeName="r" values="4;16;4" dur="3s" repeatCount="indefinite" />
        <animate attributeName="opacity" values="0.8;0;0.8" dur="3s" repeatCount="indefinite" />
    </circle>
    <circle cx="60" cy="60" r="3" fill="#cbaa4c"/>
    
    <!-- Outer orbiting particles -->
    <circle cx="30" cy="30" r="2.5" fill="#5089C6">
        <animate attributeName="opacity" values="0.2;1;0.2" dur="2s" repeatCount="indefinite" />
    </circle>
    <circle cx="90" cy="90" r="2.5" fill="#5089C6">
        <animate attributeName="opacity" values="1;0.2;1" dur="2s.5" repeatCount="indefinite" />
    </circle>
    
    <!-- Searching Magnifying Glass -->
    <g>
        <animateTransform 
            attributeName="transform" 
            type="translate" 
            values="0,0; 8,-8; -10,10; 0,0" 
            dur="6s" 
            repeatCount="indefinite" 
            type="translate"
        />
        <!-- Handle -->
        <path d="M72 72 L88 88" stroke="#cbaa4c" stroke-width="4.5" stroke-linecap="round"/>
        <!-- Ring -->
        <circle cx="60" cy="60" r="16" stroke="#5089C6" stroke-width="4" fill="rgba(80, 137, 198, 0.15)"/>
        <!-- Glass shine -->
        <path d="M52 52 A 11 11 0 0 1 68 52" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" opacity="0.6"/>
        <!-- Question Mark Inside Glass -->
        <text x="60" y="65" font-family="'Poppins', sans-serif" font-size="14" font-weight="bold" fill="#ffffff" text-anchor="middle">?</text>
    </g>
</svg>
@endsection
@section('code', '404')
@section('title_id', 'Halaman Tidak Ditemukan')
@section('desc_id', 'Maaf, halaman yang Anda tuju tidak ditemukan pada sistem kami. Pastikan alamat URL yang ditulis sudah benar atau kembali ke beranda.')
@section('title_en', 'Page Not Found')
@section('desc_en', 'Sorry, the page you are looking for does not exist on our system. Please check the URL spelling or navigate back to the home page.')