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
    
    <!-- Custom CSS for Clean government styling -->
    @stack('styles')
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative bg-gray-100 text-gray-850 font-sans overflow-x-hidden">
    
    <!-- Content Wrapper -->
    <div class="relative z-10 w-full max-w-2xl py-8">
        
        <!-- Main Panel Card -->
        <div class="bg-white border border-gray-200 shadow-sm hover:border-gray-300 hover:shadow rounded-3xl p-8 md:p-12 text-center transition-all duration-300">
            
            <!-- Dynamic Icon Slot -->
            <div class="flex justify-center mb-6">
                @yield('icon')
            </div>
            
            <!-- Error Code -->
            <div class="text-7xl md:text-8xl font-extrabold tracking-tighter text-[#145239] leading-none select-none mb-4">
                @yield('code')
            </div>
            
            <!-- Error Texts -->
            <div class="space-y-4 mb-10 max-w-lg mx-auto">
                <!-- Indonesian main text -->
                <div>
                    <h3 class="text-gray-900 font-bold text-xl md:text-2xl leading-snug">
                        @yield('title_id')
                    </h3>
                    <p class="text-gray-600 text-sm md:text-base mt-2 leading-relaxed">
                        @yield('desc_id')
                    </p>
                </div>
            </div>
            
            <!-- Action Buttons / CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="window.history.back()" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-white hover:bg-gray-50 text-gray-700 font-semibold text-sm border border-gray-300 shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </button>
                
                @yield('custom_action')
                
                <a href="{{ url('/') }}" class="transition-all duration-200 ease-in-out hover:-translate-y-[1px] active:translate-y-0 w-full sm:w-auto px-6 py-3 rounded-xl bg-[#145239] hover:bg-[#0b5d3d] text-white font-semibold text-sm shadow-sm flex items-center justify-center gap-2 border border-transparent">
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda</span>
                </a>
            </div>
            
        </div>
        
        <!-- Footer Info -->
        <div class="text-center mt-8 text-xs text-gray-400">
            <p>&copy; {{ date('Y') }} Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu Provinsi Sumatera Utara.</p>
            <p class="mt-1 opacity-70">Sistem Informasi Peta Peluang Investasi (App DESI)</p>
        </div>
        
    </div>

    @stack('scripts')
</body>
</html>