@extends('layouts.app')

@section('title', 'Arsip Siswa')
@section('header_title', 'Arsip Siswa (Lulus)')

@section('content')
<div class="glass-card p-6">
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.25rem;">Daftar Siswa Lulus</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Total: {{ $students->count() }} siswa diarsip</p>
        </div>
        <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
            <form action="{{ route('admin.students.archive') }}" method="GET" class="flex flex-col md:flex-row gap-2 flex-1">
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
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 1rem; width: 40px;">
                        <input type="checkbox" id="select-all" class="glass-card" style="width: 18px; height: 18px; cursor: pointer;">
                    </th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Foto</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">NIS</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Nama</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Kelas Terakhir</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
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
                    <td style="padding: 1rem;">
                        <span class="status-badge status-present">{{ $student->class }}</span>
                    </td>
                    <td style="padding: 1rem;">
                        <div class="flex gap-2">
                            <form action="{{ route('admin.students.restore', $student->id) }}" method="POST" onsubmit="return confirm('Kembalikan siswa ini ke daftar aktif?')">
                                @csrf
                                <button type="submit" class="nav-link" style="padding: 0.5rem; margin-bottom: 0; background: none; border: 1px solid var(--primary); cursor: pointer; color: var(--primary);" title="Kembalikan ke Aktif">
                                    <i data-lucide="rotate-ccw" style="width: 18px;"></i>
                                    <span style="font-size: 0.75rem; margin-left: 0.25rem;">Pulihkan</span>
                                </button>
                            </form>
                            <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Hapus permanen data siswa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="nav-link" style="padding: 0.5rem; margin-bottom: 0; background: none; border: none; cursor: pointer; color: var(--danger);" title="Hapus">
                                    <i data-lucide="trash-2" style="width: 18px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                        <i data-lucide="archive" style="width: 48px; height: 48px; margin: 0 auto 1rem; opacity: 0.2;"></i>
                        <p>Tidak ada data siswa di arsip.</p>
                    </td>
                </tr>
                @endforelse
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
        
        selectAll.checked = selectedCount === checkboxes.length && checkboxes.length > 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkDeleteButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkDeleteButton);
    });

    function submitBulkDelete() {
        const selected = document.querySelectorAll('.student-checkbox:checked');
        const count = selected.length;
        if (count === 0) return;

        if (confirm(`Apakah Anda yakin ingin menghapus ${count} siswa terpilih secara permanen?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('admin.students.bulk-destroy') }}";
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

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
