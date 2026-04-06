@extends('layouts.app')

@section('title', 'Input Status Manual')
@section('header_title', 'Catat Sakit atau Izin')

@section('content')
<div class="glass-card p-6" style="max-width: 600px; margin: 0 auto;">
    <div class="mb-6">
        <h2 style="font-size: 1.125rem; font-weight: 600;">Form Absensi Manual</h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Gunakan form ini untuk mencatat siswa yang Sakit atau Izin.</p>
    </div>

    @if(session('error'))
        <div class="p-4 mb-4 rounded-lg" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.attendances.store-manual') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">Pilih Siswa</label>
            <input list="students" name="student_id_search" id="student_input" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none;" placeholder="Ketik Nama atau NIS..." autocomplete="off" required>
            <input type="hidden" name="student_id" id="student_id">
            <datalist id="students">
                @foreach($students as $student)
                    <option data-id="{{ $student->id }}" value="{{ $student->nis }} - {{ $student->name }} ({{ $student->class }})"></option>
                @endforeach
            </datalist>
            @error('student_id') <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">Status Kehadiran</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status" value="Sakit" required>
                    <span class="status-badge status-sakit">Sakit</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="status" value="Izin" required>
                    <span class="status-badge status-izin">Izin</span>
                </label>
            </div>
            @error('status') <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">Tanggal</label>
            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="glass-card" style="width: 100%; padding: 0.75rem 1rem; outline: none;" required>
            @error('date') <p style="color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="premium-button">
                <i data-lucide="check-square"></i>
                Simpan Kehadiran
            </button>
            <a href="{{ route('admin.attendances.index') }}" class="nav-link" style="margin-bottom: 0;">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('student_input').addEventListener('input', function(e) {
        const val = e.target.value;
        const options = document.getElementById('students').childNodes;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                document.getElementById('student_id').value = options[i].getAttribute('data-id');
                break;
            }
        }
    });

    // Form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!document.getElementById('student_id').value) {
            e.preventDefault();
            alert('Mohon pilih siswa dari daftar yang tersedia.');
        }
    });
</script>
@endpush
@endsection
