@extends('layouts.app')

@section('title', 'Wali Kelas')
@section('header_title', 'Manajemen Wali Kelas')

@section('content')
<div class="glass-card p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.25rem;">Daftar Nomor WhatsApp Wali Kelas</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Setiap nomor akan menerima laporan absensi harian kelasnya.</p>
        </div>
        <button onclick="openAddModal()" class="premium-button">
            <i data-lucide="plus"></i>
            Tambah Kontak
        </button>
    </div>

    @if(session('success'))
        <div style="background: #d1fae5; border: 1px solid var(--success); color: #065f46; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Kelas</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Nomor WhatsApp</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 1rem; font-weight: 600;">{{ $contact->class_name }}</td>
                    <td style="padding: 1rem;">{{ $contact->phone_number }}</td>
                    <td style="padding: 1rem;">
                        <div class="flex gap-2">
                            <button onclick="openEditModal({{ $contact->id }}, '{{ $contact->class_name }}', '{{ $contact->phone_number }}')" class="nav-link" style="padding: 0.5rem; border:none; background:none; cursor:pointer;" title="Edit">
                                <i data-lucide="edit-2" style="width: 18px;"></i>
                            </button>
                            <form action="{{ route('admin.teachers.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Hapus kontak ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="nav-link" style="padding: 0.5rem; background: none; border: none; cursor: pointer; color: var(--danger);" title="Hapus">
                                    <i data-lucide="trash-2" style="width: 18px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="padding: 2rem; text-align: center; color: var(--text-muted);">Belum ada data kontak wali kelas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 50; align-items: center; justify-content: center;">
    <div class="glass-card p-8 w-full max-w-md">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Tambah Kontak Wali Kelas</h3>
        <form action="{{ route('admin.teachers.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Pilih Kelas</label>
                <select name="class_name" class="glass-card w-full" style="padding: 0.75rem; outline: none;" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($availableClasses as $class)
                        <option value="{{ $class }}">{{ $class }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Nomor WhatsApp</label>
                <input type="text" name="phone_number" class="glass-card w-full" style="padding: 0.75rem; outline: none;" placeholder="Contoh: 08123456789" required>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Gunakan format angka awal 0 atau 62.</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddModal()" class="premium-button" style="background: transparent; border: 1px solid var(--glass-border); color: var(--text-main);">Batal</button>
                <button type="submit" class="premium-button">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 50; align-items: center; justify-content: center;">
    <div class="glass-card p-8 w-full max-w-md">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Edit Kontak Wali Kelas</h3>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Kelas</label>
                <input type="text" id="edit_class_name" name="class_name" class="glass-card w-full" style="padding: 0.75rem; outline: none; background: rgba(0,0,0,0.05);" readonly>
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Nomor WhatsApp</label>
                <input type="text" id="edit_phone_number" name="phone_number" class="glass-card w-full" style="padding: 0.75rem; outline: none;" required>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="premium-button" style="background: transparent; border: 1px solid var(--glass-border); color: var(--text-main);">Batal</button>
                <button type="submit" class="premium-button">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }
    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }
    function openEditModal(id, class_name, phone_number) {
        document.getElementById('editForm').action = `/admin/teacher-contacts/${id}`;
        document.getElementById('edit_class_name').value = class_name;
        document.getElementById('edit_phone_number').value = phone_number;
        document.getElementById('editModal').style.display = 'flex';
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>
@endsection
