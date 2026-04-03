<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Pro - @yield('title')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Premium Styles -->
    <link rel="stylesheet" href="{{ asset('css/premium.css') }}">
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo mb-4" style="font-weight: 700; font-size: 1.5rem; color: var(--primary);">
            Absensi<span style="color: #334155;">Pro</span>
        </div>
        
        <nav>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i>
                Dashboard
            </a>
            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i data-lucide="users"></i>
                Data Siswa
            </a>
            <a href="{{ route('admin.attendances.index') }}" class="nav-link {{ request()->routeIs('admin.attendances.index') ? 'active' : '' }}">
                <i data-lucide="clipboard-list"></i>
                Laporan Absensi
            </a>
            <a href="{{ route('admin.archives.index') }}" class="nav-link {{ request()->routeIs('admin.archives.*') ? 'active' : '' }}">
                <i data-lucide="archive"></i>
                Arsip Laporan
            </a>
            <a href="{{ route('scanner.index') }}" class="nav-link {{ request()->routeIs('scanner.index') ? 'active' : '' }}">
                <i data-lucide="qr-code"></i>
                Halaman Scanner
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                <i data-lucide="settings"></i>
                Pengaturan
            </a>

            <div style="margin: 1.5rem 0 0.5rem; padding-left: 1rem; font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">
                WhatsApp Reporting
            </div>
            <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i data-lucide="contact-2"></i>
                Wali Kelas
            </a>
            <a href="{{ route('admin.whatsapp.connect') }}" class="nav-link {{ request()->routeIs('admin.whatsapp.connect') ? 'active' : '' }}">
                <i data-lucide="message-square"></i>
                Koneksi WhatsApp
            </a>
        </nav>
        
        <div class="mt-auto pt-6">
            <div class="user-profile flex items-center gap-3 p-3 rounded-lg" style="background: #f8fafc;">
                <div class="avatar flex items-center justify-center" style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; color: #64748b;">
                    <i data-lucide="user" style="width: 20px;"></i>
                </div>
                <div>
                    <p style="font-weight: 700; font-size: 0.875rem; color: #1e293b;">Admin</p>
                    <p style="font-size: 0.75rem; color: #64748b;">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="flex justify-between items-center mb-4">
            <h1 style="font-size: 1.25rem;">@yield('header_title')</h1>
            <div id="current-time" style="color: var(--text-muted); font-size: 0.875rem;"></div>
        </header>
        
        <div class="animate-fade-in">
            @yield('content')
        </div>
    </main>

    <script>
        // Update Time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').innerText = now.toLocaleTimeString('id-id', { 
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
                hour: '2-digit', minute: '2-digit', second: '2-digit' 
            });
        }
        setInterval(updateTime, 1000);
        updateTime();
        
        // Icon initialization
        lucide.createIcons();
    </script>
    
    @stack('scripts')
</body>
</html>
