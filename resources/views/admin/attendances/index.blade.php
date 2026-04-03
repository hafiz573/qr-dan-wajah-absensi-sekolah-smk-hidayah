@extends('layouts.app')

@section('title', 'Laporan Absensi')
@section('header_title', 'Laporan Kehadiran Hari Ini')

@section('content')
<div class="glass-card p-6">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 600;">Log Absensi Siswa</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">{{ $attendances->count() }} catatan kehadiran hari ini.</p>
        </div>
        <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
            <form action="{{ route('admin.attendances.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 flex-1">
                <div class="search-container">
                    <i data-lucide="search" class="search-icon" style="width: 18px; height: 18px;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="glass-card search-input" style="padding: 0.5rem 1rem; outline: none;" placeholder="Cari Nama / NIS...">
                </div>
                <div class="flex gap-2">
                    <select name="class" class="glass-card" style="padding: 0.5rem 1rem; outline: none;" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c }}" {{ request('class') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.archives.index') }}" class="nav-link" style="margin: 0; padding: 0.5rem 1rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem;" title="Lihat Riwayat Arsip">
                    <i data-lucide="archive" style="width: 18px;"></i>
                    <span class="md:inline hidden">Arsip</span>
                </a>
                <form action="{{ route('admin.archives.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="premium-button" style="background: white; color: var(--text-main); border: 1px solid #cbd5e1; font-size: 0.875rem; padding: 0.5rem 1rem;">
                        <i data-lucide="save"></i>
                        Arsip Hari Ini
                    </button>
                </form>
                <form action="{{ route('admin.archives.monthly') }}" method="POST">
                    @csrf
                    <button type="submit" class="premium-button" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                        <i data-lucide="file-spreadsheet"></i>
                        Rekap Bulanan
                    </button>
                </form>
                @if(env('TEST_DEMO'))
                <form action="{{ route('admin.attendances.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data absensi hari ini? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" class="premium-button" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.875rem; padding: 0.5rem 1rem;">
                        <i data-lucide="trash-2"></i>
                        Reset Absensi
                    </button>
                </form>
                @endif
                <button onclick="window.print()" class="premium-button" style="background: white; color: var(--text-main); border: 1px solid #cbd5e1; font-size: 0.875rem; padding: 0.5rem 1rem;">
                    <i data-lucide="printer"></i>
                </button>
            </div>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Waktu</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Nama Siswa</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Kelas</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Status</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Metode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 1rem; font-family: monospace; font-size: 0.9375rem;">{{ $attendance->time }}</td>
                    <td style="padding: 1rem; font-weight: 500;">{{ $attendance->student->name }}</td>
                    <td style="padding: 1rem;">{{ $attendance->student->class }}</td>
                    <td style="padding: 1rem;">
                        <span class="status-badge {{ $attendance->status == 'Hadir' ? 'status-present' : 'status-late' }}">
                            {{ $attendance->status }}
                        </span>
                    </td>
                    <td style="padding: 1rem; color: var(--text-muted); font-size: 0.875rem;">
                        <span class="flex items-center gap-2">
                            <i data-lucide="scan-face" style="width: 14px;"></i>
                            Auto-Scan
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                        <i data-lucide="folder-open" style="width: 3rem; height: 3rem; margin: 0 auto 1rem; opacity: 0.2;"></i>
                        <p>Belum ada data absensi untuk hari ini.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
