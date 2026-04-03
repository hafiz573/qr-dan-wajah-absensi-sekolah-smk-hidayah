@extends('layouts.app')

@section('title', 'Data Siswa')
@section('header_title', 'Manajemen Data Siswa')

@section('content')
<div class="glass-card p-6">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.25rem;">Daftar Siswa</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Total: {{ $students->count() }} siswa terdaftar</p>
        </div>
        <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
            <form action="{{ route('admin.students.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 flex-1">
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
            <div class="flex gap-2">
                <button type="button" id="btn-bulk-delete" class="premium-button" style="background: var(--danger); border-color: var(--danger); display: none;" onclick="submitBulkDelete()">
                    <i data-lucide="trash-2"></i>
                    Hapus Terpilih (<span id="selected-count">0</span>)
                </button>
                <a href="{{ route('admin.students.create') }}" class="premium-button">
                    <i data-lucide="user-plus"></i>
                    Tambah Siswa
                </a>
            </div>
        </div>
    </div>

    @php
        $needSync = \App\Models\Student::whereNotNull('photo')->whereNull('face_descriptor')->count();
    @endphp
    @if($needSync > 0)
    <div style="background: white; border: 1.5px solid var(--primary); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);">
        <div class="flex items-center gap-4">
            <div style="background: var(--primary); padding: 0.75rem; border-radius: 0.75rem; color: white;">
                <i data-lucide="sparkles"></i>
            </div>
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--primary);">Foto Sudah Siap!</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Sistem mendeteksi <strong>{{ $needSync }}</strong> foto yang belum diekstrak datanya oleh AI.</p>
            </div>
        </div>
        <a href="{{ route('admin.students.bulk-sync') }}" class="premium-button" style="padding: 0.75rem 1.5rem;">
            <i data-lucide="zap"></i>
            Proses Wajah Masal
        </a>
    </div>
    @endif

    <!-- Import Section -->
    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <div style="background: rgba(37, 99, 235, 0.1); padding: 0.75rem; border-radius: 0.75rem;">
                    <i data-lucide="file-up" style="color: var(--primary);"></i>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 600;">Import Siswa via Excel</h3>
                    <p style="font-size: 0.875rem; color: var(--text-muted);">Unggah file Excel lengkap dengan foto siswa.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.students.import-template') }}" class="nav-link" style="margin:0; background: white; border: 1px solid #e2e8f0;">
                    <i data-lucide="download"></i>
                    Unduh Template
                </a>
                <form action="{{ route('admin.students.import-excel') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="file" id="excel_file" class="hidden" onchange="this.form.submit()" accept=".xlsx,.xls,.csv">
                    <button type="button" onclick="document.getElementById('excel_file').click()" class="premium-button" style="background: #ffffff; color: var(--text-main); border: 1px solid #cbd5e1;">
                        <i data-lucide="upload"></i>
                        Pilih File (.xlsx)
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #d1fae5; border: 1px solid var(--success); color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div style="background: #fee2e2; border: 1px solid var(--danger); color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('error') }}
        </div>
    @endif

    <div style="overflow-x: auto;">
    <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 1rem; width: 40px;">
                            <input type="checkbox" id="select-all" class="glass-card" style="width: 18px; height: 18px; cursor: pointer;">
                        </th>
                        <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Foto</th>
                        <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">NIS</th>
                        <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Nama</th>
                        <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Kelas</th>
                        <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Face Data</th>
                        <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 1rem;">
                            <input type="checkbox" name="ids[]" value="{{ $student->id }}" class="student-checkbox glass-card" style="width: 18px; height: 18px; cursor: pointer;">
                        </td>
                        <td style="padding: 1rem;">
                        <div style="width: 40px; height: 40px; border-radius: 8px; background: #f1f5f9; overflow: hidden;">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" style="width: 100%; height: 100%; object-fit: cover; object-position: top;">
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #cbd5e1;">
                                    <i data-lucide="user" style="width: 20px;"></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td style="padding: 1rem;">{{ $student->nis }}</td>
                    <td style="padding: 1rem; font-weight: 500;">{{ $student->name }}</td>
                    <td style="padding: 1rem;">{{ $student->class }}</td>
                    <td style="padding: 1rem;">
                        @if($student->face_descriptor)
                            <span class="status-badge status-present">Terdaftar</span>
                        @else
                            <span class="status-badge status-absent">Belum Ada</span>
                        @endif
                    </td>
                    <td style="padding: 1rem;">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.students.qr-download', $student->id) }}" class="nav-link" style="padding: 0.5rem; margin-bottom: 0;" title="Download QR Code">
                                <i data-lucide="qr-code" style="width: 18px;"></i>
                            </a>
                            <a href="{{ route('admin.students.face-setup', $student->id) }}" class="nav-link" style="padding: 0.5rem; margin-bottom: 0;" title="Setup Face/Photo">
                                <i data-lucide="camera" style="width: 18px;"></i>
                            </a>
                            <a href="{{ route('admin.students.edit', $student->id) }}" class="nav-link" style="padding: 0.5rem; margin-bottom: 0;" title="Edit">
                                <i data-lucide="edit-2" style="width: 18px;"></i>
                            </a>
                            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="nav-link" style="padding: 0.5rem; margin-bottom: 0; background: none; border: none; cursor: pointer; color: var(--danger);" title="Hapus">
                                    <i data-lucide="trash-2" style="width: 18px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                    </tr>
                    @endforeach
                </tbody>
            </table>
    </div>
</div>

@push('scripts')
<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const bulkDeleteBtn = document.getElementById('btn-bulk-delete');
    const selectedCountSpan = document.getElementById('selected-count');

    function updateBulkDeleteButton() {
        const selectedCount = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCountSpan.innerText = selectedCount;
        bulkDeleteBtn.style.display = selectedCount > 0 ? 'inline-flex' : 'none';
        
        // Update select all state
        selectAll.checked = selectedCount === checkboxes.length && checkboxes.length > 0;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
        });
        updateBulkDeleteButton();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteButton);
    });

    function submitBulkDelete() {
        const selected = document.querySelectorAll('.student-checkbox:checked');
        const count = selected.length;
        if (count === 0) return;

        if (confirm(`Apakah Anda yakin ingin menghapus ${count} siswa terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
            // Buat form dinamis untuk menghindari nested form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('admin.students.bulk-destroy') }}";
            
            // CSRF Token
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            // Sisipkan ID yang dipilih
            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush
@endsection
