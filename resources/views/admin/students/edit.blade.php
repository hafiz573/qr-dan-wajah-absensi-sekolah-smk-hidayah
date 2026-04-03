@extends('layouts.app')

@section('title', 'Edit Siswa - ' . $student->name)
@section('header_title', 'Edit Data Siswa')

@section('content')
<div class="glass-card p-6" style="max-width: 600px; margin: 0 auto;">
    <div class="mb-6">
        <h2 style="font-size: 1.125rem; font-weight: 600;">Edit Profil Siswa</h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Perbarui informasi dasar siswa.</p>
    </div>

    <form action="{{ route('admin.students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">NIS</label>
            <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none; border: 1px solid var(--glass-border);" placeholder="Contoh: 2024001" required>
            @error('nis') <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $student->name) }}" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none; border: 1px solid var(--glass-border);" placeholder="Nama Lengkap Siswa" required>
            @error('name') <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">Kelas</label>
            <input type="text" name="class" value="{{ old('class', $student->class) }}" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none; border: 1px solid var(--glass-border);" placeholder="Contoh: XII RPL 1" required>
            @error('class') <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="premium-button">
                <i data-lucide="save"></i>
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.students.index') }}" class="nav-link" style="margin-bottom: 0;">Batal</a>
        </div>
    </form>
</div>
@endsection
