@extends('errors.layout')
@section('title', '401 - Tidak Diotorisasi')
@section('illustration')
<svg class="animated-svg w-40 h-40" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- ID Card Base -->
    <rect x="25" y="20" width="70" height="80" rx="10" fill="rgba(80, 137, 198, 0.05)" stroke="#5089C6" stroke-width="3"/>
    <rect x="30" y="25" width="60" height="70" rx="6" stroke="rgba(80, 137, 198, 0.15)" stroke-width="1.5"/>
    
    <!-- Lanyard hole -->
    <rect x="52" y="10" width="16" height="6" rx="3" fill="#050b1a" stroke="#5089C6" stroke-width="2"/>
    <!-- Avatar Placeholder -->
    <circle cx="60" cy="48" r="14" fill="rgba(80, 137, 198, 0.1)" stroke="#5089C6" stroke-width="2.5"/>
    
    <!-- User Lines (skeleton text) -->
    <line x1="42" y1="72" x2="78" y2="72" stroke="rgba(255, 255, 255, 0.3)" stroke-width="3" stroke-linecap="round"/>
    <line x1="48" y1="80" x2="72" y2="80" stroke="rgba(255, 255, 255, 0.15)" stroke-width="2.5" stroke-linecap="round"/>
    <!-- Padlock overlay on profile -->
    <g transform="translate(64, 46)">
        <circle cx="14" cy="14" r="14" fill="#050b1a" stroke="#cbaa4c" stroke-width="2"/>
        <g transform="translate(7, 6)">
            <!-- Lock Shackle -->
            <path d="M4 7 V4 A 3 3 0 0 1 10 4 V7" stroke="#cbaa4c" stroke-width="1.8" fill="none"/>
            <!-- Lock Body -->
            <rect x="1" y="6" width="12" height="9" rx="2" fill="#cbaa4c"/>
            <!-- Keyhole dot -->
            <circle cx="7" cy="10.5" r="1" fill="#050b1a"/>
        </g>
    </g>
    <!-- Key Floating Animation -->
    <g>
        <animateTransform 
            attributeName="transform" 
            type="translate" 
            values="0,0; -6,5; 0,0" 
            dur="4s" 
            repeatCount="indefinite"
        />
        <path d="M22 45 L15 52 L15 57 L20 57 L20 54 L23 54 L23 51 L25 51 L27 45 Z" stroke="#cbaa4c" stroke-width="2" fill="none"/>
        <circle cx="25" cy="43" r="2" fill="#cbaa4c"/>
    </g>
</svg>
@endsection
@section('code', '401')
@section('title_id', 'Diperlukan Otorisasi')
@section('desc_id', 'Halaman ini dilindungi dan memerlukan autentikasi. Silakan masuk (login) ke akun Anda terlebih dahulu untuk mengakses halaman.')
@section('title_en', 'Authorization Required')
@section('desc_en', 'This page is password-protected and requires verification. Please log in to your account first to proceed.')
@section('custom_action')
<a href="{{ route('login') }}" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#035397] to-[#5089C6] hover:from-[#5089C6] hover:to-[#035397] text-white font-bold text-sm shadow-lg shadow-blue-900/30 flex items-center justify-center gap-2 border border-blue-400/20">
    <i class="fa-solid fa-right-to-bracket"></i>
    <span>Masuk / Log In</span>
</a>
@endsection