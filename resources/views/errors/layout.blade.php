<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - DPMPTSP Provinsi Sumatera Utara</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom CSS for Premium Animations -->
    <style>
        body {
            font-family: 'Poppins', 'Inter', sans-serif;
            background-color: #050b1a;
            color: #f8fafc;
            overflow-x: hidden;
        }
        /* Animated Mesh Gradient Background */
        .mesh-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
            overflow: hidden;
            pointer-events: none;
        }
        .mesh-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            mix-blend-mode: screen;
            animation: floatBlob 25s infinite alternate ease-in-out;
        }
        .blob-1 {
            top: -10%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(0, 30, 108, 0.8) 0%, rgba(3, 83, 151, 0) 70%);
            animation-duration: 20s;
        }
        .blob-2 {
            bottom: -20%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(80, 137, 198, 0.6) 0%, rgba(0, 30, 108, 0) 70%);
            animation-duration: 28s;
            animation-delay: -5s;
        }
        .blob-3 {
            top: 40%;
            left: 50%;
            width: 45vw;
            height: 45vw;
            background: radial-gradient(circle, rgba(203, 170, 76, 0.25) 0%, rgba(3, 83, 151, 0) 70%);
            animation-duration: 24s;
            animation-delay: -10s;
        }
        @keyframes floatBlob {
            0% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(8%, 12%) scale(1.15);
            }
            100% {
                transform: translate(-5%, -8%) scale(0.9);
            }
        }
        /* Glassmorphism Card styling */
        .glass-card {
            background: rgba(10, 20, 47, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        inset 0 1px 1px rgba(255, 255, 255, 0.1);
        }
        .glass-card:hover {
            border-color: rgba(80, 137, 198, 0.25);
            box-shadow: 0 25px 60px -10px rgba(0, 30, 108, 0.4),
                        0 0 40px 5px rgba(80, 137, 198, 0.05),
                        inset 0 1px 1px rgba(255, 255, 255, 0.15);
        }
        /* Pulsing Glow Text for Status Codes */
        .glow-code {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #ffffff 30%, #5089C6 70%, #cbaa4c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 15px rgba(80, 137, 198, 0.3));
            animation: pulseGlow 4s infinite alternate ease-in-out;
        }
        @keyframes pulseGlow {
            0% {
                filter: drop-shadow(0 0 15px rgba(80, 137, 198, 0.3));
            }
            100% {
                filter: drop-shadow(0 0 25px rgba(203, 170, 76, 0.5));
            }
        }
        /* Illustration animation base styles */
        .animated-svg {
            animation: floatSvg 6s infinite ease-in-out;
        }
        @keyframes floatSvg {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-12px);
            }
        }
        /* Scanline animation for forbidden/access error */
        .scanner-line {
            animation: scan 3s infinite linear;
        }
        @keyframes scan {
            0% {
                transform: translateY(-100%);
            }
            100% {
                transform: translateY(100%);
            }
        }
        /* Interactive buttons styling */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-glow::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transform: rotate(45deg) translate(-70%, -70%);
            transition: all 0.6s ease;
        }
        .btn-glow:hover::after {
            transform: rotate(45deg) translate(70%, 70%);
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative">
    
    <!-- Background Animating Mesh Blobs -->
    <div class="mesh-container">
        <div class="mesh-blob blob-1"></div>
        <div class="mesh-blob blob-2"></div>
        <div class="mesh-blob blob-3"></div>
    </div>
    
    <!-- Content Wrapper -->
    <div class="relative z-10 w-full max-w-2xl py-8">
        
        <!-- DPMPTSP Branding Header -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                <img src="{{ asset('images/logo-dpmptsp.png') }}" class="h-14 md:h-16 w-auto object-contain filter drop-shadow-lg transition-transform duration-300 group-hover:scale-105" alt="Logo DPMPTSP">
                <div class="text-left border-l border-white/20 pl-3">
                    <h2 class="text-white font-extrabold text-sm md:text-base tracking-wide leading-tight group-hover:text-[#5089C6] transition-colors duration-300">DPMPTSP</h2>
                    <p class="text-slate-400 font-semibold text-xs leading-none">Provinsi Sumatera Utara</p>
                </div>
            </a>
        </div>
        
        <!-- Main Glassmorphism Card -->
        <div class="glass-card rounded-3xl p-8 md:p-12 text-center transition-all duration-300">
            
            <!-- Dynamic Illustration Slot -->
            <div class="flex justify-center mb-8 h-48 relative">
                @yield('illustration')
            </div>
            
            <!-- Error Code Code -->
            <div class="glow-code select-none mb-4">
                @yield('code')
            </div>
            
            <!-- Error Texts -->
            <div class="space-y-4 mb-10 max-w-lg mx-auto">
                <!-- Indonesian main text -->
                <div>
                    <h3 class="text-white font-bold text-xl md:text-2xl leading-snug">
                        @yield('title_id')
                    </h3>
                    <p class="text-slate-300 text-sm md:text-base mt-2 leading-relaxed font-sans">
                        @yield('desc_id')
                    </p>
                </div>
                
                <!-- English sub text -->
                <div class="pt-3 border-t border-white/10 opacity-70">
                    <h4 class="text-slate-300 font-medium text-base md:text-lg italic leading-tight">
                        @yield('title_en')
                    </h4>
                    <p class="text-slate-400 text-xs md:text-sm mt-1 italic leading-relaxed font-sans">
                        @yield('desc_en')
                    </p>
                </div>
            </div>
            
            <!-- Action Buttons / CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="window.history.back()" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm border border-white/10 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali / Go Back</span>
                </button>
                
                @yield('custom_action')
                
                <a href="{{ url('/') }}" class="btn-glow w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-[#035397] to-[#5089C6] hover:from-[#5089C6] hover:to-[#035397] text-white font-semibold text-sm shadow-lg shadow-blue-900/40 flex items-center justify-center gap-2 border border-blue-400/20">
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda / Home</span>
                </a>
            </div>
            
        </div>
        
        <!-- Footer Info -->
        <div class="text-center mt-8 text-xs text-slate-500 font-sans">
            <p>&copy; {{ date('Y') }} Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu Provinsi Sumatera Utara.</p>
            <p class="mt-1 opacity-70">Sistem Informasi Peta Peluang Investasi (App DESI)</p>
        </div>
        
    </div>
    @stack('scripts')
</body>
</html>