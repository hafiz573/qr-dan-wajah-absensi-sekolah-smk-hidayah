@extends('layouts.app')

@section('title', 'Pengaturan')
@section('header_title', 'Konfigurasi Sistem')

@section('content')
<div class="glass-card p-6" style="max-width: 600px; margin: 0 auto;">
    <div class="mb-6">
        <h2 style="font-size: 1.125rem; font-weight: 600;">Pengaturan Waktu Absensi</h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Konfigurasi batas waktu kehadiran siswa.</p>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <i data-lucide="check-circle" style="width: 18px;"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-weight: 600;">
                <i data-lucide="alert-circle" style="width: 18px;"></i>
                Terjadi Kesalahan:
            </div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Batas Waktu Terlambat (WIB)
            </label>
            <input type="time" name="time_late" value="{{ $settings['time_late'] }}" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none;" step="1" required>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Setelah waktu ini, siswa akan tercatat "Terlambat".</p>
        </div>

        <div class="mb-6">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Batas Waktu Alfa (Tutup Absen)
            </label>
            <input type="time" name="time_absent" value="{{ $settings['time_absent'] }}" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none;" step="1" required>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Setelah waktu ini, sistem tidak akan menerima scan lagi dan menganggap siswa Alfa.</p>
        </div>

        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Zona Waktu (Timezone)
            </label>
            <select name="timezone" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none;">
                <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                <option value="Asia/Makassar" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                <option value="Asia/Jayapura" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
            </select>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Pilih zona waktu sesuai lokasi sekolah Anda.</p>
        </div>

        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Tipe Sekolah
            </label>
            <select name="school_type" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none;">
                <option value="SD" {{ ($settings['school_type'] ?? 'SMK') == 'SD' ? 'selected' : '' }}>SD (Kelas I - VI)</option>
                <option value="SMP" {{ ($settings['school_type'] ?? 'SMK') == 'SMP' ? 'selected' : '' }}>SMP (Kelas VII - IX)</option>
                <option value="SMK" {{ ($settings['school_type'] ?? 'SMK') == 'SMK' ? 'selected' : '' }}>SMK/SMA (Kelas X - XII)</option>
            </select>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Menentukan batas kenaikan kelas (SD:6, SMP:9, SMK:12).</p>
        </div>

        <div class="mb-6">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Waktu Kirim Laporan WA Otomatis
            </label>
            <input type="time" name="report_time" value="{{ $settings['report_time'] }}" class="glass-card @error('report_time') border-danger @enderror" style="width: 100%; padding: 0.75rem 1rem; outline: none;" step="1" required>
            @error('report_time')
                <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
            @enderror
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">Waktu sistem akan mengirim laporan harian ke semua Wali Kelas secara bertahap.</p>
        </div>

        <div class="flex gap-4 p-4 rounded-lg bg-blue-50 border border-blue-100 mb-6" style="background: rgba(37, 99, 235, 0.05);">
            <i data-lucide="info" style="color: var(--primary);"></i>
            <p style="font-size: 0.75rem; color: var(--text-main);">
                Zona waktu saat ini: <strong>{{ config('app.timezone') }}</strong>. 
                Jika waktu masih tidak pas, silakan ubah zona waktu di atas.
            </p>
        </div>

        <button type="submit" class="premium-button" style="width: 100%;">
            <i data-lucide="save"></i>
            Simpan Konfigurasi
        </button>
    </form>

    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="mb-4">
            <h2 style="font-size: 1.125rem; font-weight: 600;">Manajemen Kenaikan Kelas</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Fitur ini akan menaikkan tingkat semua siswa (Romawi) dan memberikan status "Lulus" bagi yang sudah di tingkat akhir.</p>
        </div>

        <form action="{{ route('admin.students.promote') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menaikkan semua kelas siswa sekarang? Tindakan ini tidak dapat dibatalkan.')">
            @csrf
            <button type="submit" class="glass-card" style="width: 100%; padding: 0.75rem; color: var(--warning); border-color: var(--warning); cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='rgba(245, 158, 11, 0.1)'" onmouseout="this.style.background='transparent'">
                <i data-lucide="trending-up" style="vertical-align: middle; margin-right: 0.5rem; width: 18px;"></i>
                Naikkan Kelas Sekarang (Manual)
            </button>
        </form>
        <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.5rem; text-align: center;">Kenaikan otomatis dijadwalkan setiap 1 Juli.</p>
    </div>
</div>
@endsection
