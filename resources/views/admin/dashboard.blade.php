@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Ringkasan Absensi')

@section('content')
<div class="dashboard-grid mb-8">
    <!-- Stat Cards -->
    <div class="glass-card p-6 border-l-4" style="border-left-color: var(--success);">
        <div class="flex justify-between items-start">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Hadir Tepat Waktu</p>
                <h3 style="font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">{{ $stats['hadir'] ?? 0 }}</h3>
            </div>
            <div class="p-3" style="background: rgba(16, 185, 129, 0.1); border-radius: 0.75rem; color: var(--success);">
                <i data-lucide="check-circle"></i>
            </div>
        </div>
        <p style="font-size: 0.75rem; margin-top: 1rem; color: var(--text-muted);">Siswa masuk sebelum 07:00</p>
    </div>

    <div class="glass-card p-6 border-l-4" style="border-left-color: var(--warning);">
        <div class="flex justify-between items-start">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Terlambat</p>
                <h3 style="font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">{{ $stats['terlambat'] ?? 0 }}</h3>
            </div>
            <div class="p-3" style="background: rgba(245, 158, 11, 0.1); border-radius: 0.75rem; color: var(--warning);">
                <i data-lucide="clock"></i>
            </div>
        </div>
        <p style="font-size: 0.75rem; margin-top: 1rem; color: var(--text-muted);">Siswa masuk setelah 07:00</p>
    </div>

    <div class="glass-card p-6 border-l-4" style="border-left-color: #f97316;">
        <div class="flex justify-between items-start">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Sakit</p>
                <h3 style="font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">{{ $stats['sakit'] ?? 0 }}</h3>
            </div>
            <div class="p-3" style="background: rgba(249, 115, 22, 0.1); border-radius: 0.75rem; color: #f97316;">
                <i data-lucide="thermometer"></i>
            </div>
        </div>
        <p style="font-size: 0.75rem; margin-top: 1rem; color: var(--text-muted);">Siswa sakit (input manual)</p>
    </div>

    <div class="glass-card p-6 border-l-4" style="border-left-color: #3b82f6;">
        <div class="flex justify-between items-start">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Izin</p>
                <h3 style="font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">{{ $stats['izin'] ?? 0 }}</h3>
            </div>
            <div class="p-3" style="background: rgba(59, 130, 246, 0.1); border-radius: 0.75rem; color: #3b82f6;">
                <i data-lucide="file-text"></i>
            </div>
        </div>
        <p style="font-size: 0.75rem; margin-top: 1rem; color: var(--text-muted);">Siswa berkepentingan (manual)</p>
    </div>

    <div class="glass-card p-6 border-l-4" style="border-left-color: var(--danger);">
        <div class="flex justify-between items-start">
            <div>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Tidak Hadir (Alfa)</p>
                <h3 style="font-size: 2rem; font-weight: 700; margin-top: 0.5rem;">{{ $stats['alfa'] ?? 0 }}</h3>
            </div>
            <div class="p-3" style="background: rgba(239, 68, 68, 0.1); border-radius: 0.75rem; color: var(--danger);">
                <i data-lucide="x-circle"></i>
            </div>
        </div>
        <p style="font-size: 0.75rem; margin-top: 1rem; color: var(--text-muted);">Siswa belum melakukan scan</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Recent Activity -->
    <div class="glass-card p-6">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Aktivitas Terbaru</h3>
        <div class="space-y-4">
            @forelse($recent_attendances ?? [] as $item)
            <div class="flex items-center gap-4 p-3 rounded-lg" style="background: #ffffff; border: 1px solid var(--glass-border);">
                <div style="width: 10px; height: 10px; border-radius: 50%; background: 
                    {{ $item->status == 'Hadir' ? 'var(--success)' : '' }}
                    {{ $item->status == 'Terlambat' ? 'var(--warning)' : '' }}
                    {{ $item->status == 'Sakit' ? '#f97316' : '' }}
                    {{ $item->status == 'Izin' ? '#3b82f6' : '' }}
                "></div>
                <div style="flex: 1;">
                    <p style="font-weight: 600; font-size: 0.875rem; color: var(--text-main);">{{ $item->student->name }}</p>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Kelas {{ $item->student->class }}</p>
                </div>
                <div class="text-right">
                    <p style="font-weight: 500; font-size: 0.875rem;">{{ $item->time }}</p>
                    <span class="status-badge 
                        {{ $item->status == 'Hadir' ? 'status-present' : '' }}
                        {{ $item->status == 'Terlambat' ? 'status-late' : '' }}
                        {{ $item->status == 'Sakit' ? 'status-sakit' : '' }}
                        {{ $item->status == 'Izin' ? 'status-izin' : '' }}
                    ">{{ $item->status }}</span>
                </div>
            </div>
            @empty
            <p style="color: var(--text-muted); font-size: 0.875rem; text-align: center; py-6;">Belum ada aktivitas hari ini.</p>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-card p-6">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Akses Cepat</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <a href="{{ route('scanner.index') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="scan-line" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">Buka Scanner</p>
            </a>
            <a href="{{ route('admin.students.create') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="user-plus" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">Tambah Siswa</p>
            </a>
            <a href="{{ route('admin.attendances.index') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="file-text" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">Laporan</p>
            </a>
            <a href="{{ route('admin.teachers.index') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="contact-2" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">Wali Kelas</p>
            </a>
            <a href="{{ route('admin.whatsapp.connect') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="message-square" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">WA Connect</p>
            </a>
            <a href="{{ route('admin.attendances.manual') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="edit-3" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">Catat Sakit/Izin</p>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="glass-card p-4 text-center hover:shadow-lg transition-all border-none quick-action-link" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <i data-lucide="settings" style="width: 2rem; height: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                <p style="font-size: 0.875rem; font-weight: 600; color: var(--text-main);">Pengaturan</p>
            </a>
        </div>
    </div>
</div>
@endsection
