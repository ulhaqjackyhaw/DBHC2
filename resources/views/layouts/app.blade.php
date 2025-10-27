<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul halaman akan diisi oleh halaman anak --}}

    <!-- Favicon logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-title.png') }}">
    <title>@yield('title') - Analitik Kepegawaian</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js" defer></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
        }

        /* Modern Scrollbar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: rgba(71, 85, 105, 0.2);
            border-radius: 12px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #64748b 0%, #94a3b8 100%);
            border-radius: 12px;
            border: 2px solid rgba(30, 41, 59, 0.3);
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #94a3b8 0%, #cbd5e1 100%);
        }

        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Gradient utilities */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            aside {
                width: 100% !important;
                max-width: 320px;
            }
        }

        /* Enhanced hover effects */
        .nav-link:hover .bi {
            transform: scale(1.1);
        }

        /* Logo animation */
        .company-logo {
            transition: transform 0.3s ease-in-out;
        }

        .company-logo:hover {
            transform: scale(1.05) rotate(2deg);
        }
    </style>

    {{-- Tempat untuk script atau style tambahan dari halaman anak --}}
    @stack('head-scripts')
</head>

<body x-data="{ sidebarOpen: window.innerWidth >= 768 }" @resize.window="sidebarOpen = window.innerWidth >= 768" class="h-screen overflow-hidden">
    <div class="d-flex h-100 position-relative">

        {{-- 1. Memanggil file partial sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Overlay untuk mobile --}}
        <div x-show="sidebarOpen && window.innerWidth < 768" @click="sidebarOpen = false"
            class="position-fixed top-0 start-0 w-100 h-100 bg-black bg-opacity-50 z-40 d-md-none" x-transition.opacity
            x-cloak></div>

        <div class="flex-grow-1 d-flex flex-column h-100 transition-all duration-300 ease-in-out"
            :class="{ 'md:ml-[260px]': sidebarOpen }">

            {{-- 2. Memanggil file partial header --}}
            @include('layouts.partials.header')

            {{-- 3. Konten utama yang akan diisi oleh setiap halaman anak --}}
            <main class="flex-grow-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Tempat untuk JavaScript spesifik dari halaman anak --}}
    @stack('body-scripts')
</body>

</html>
