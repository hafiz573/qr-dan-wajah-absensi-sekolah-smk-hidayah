@extends('layouts.app')

@section('title', 'Riwayat Arsip')
@section('header_title', 'Manajemen Arsip Laporan')

@section('content')
<div class="glass-card p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 600;">Daftar Berkas Terarsip</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Seluruh laporan yang telah diekspor ke Excel.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.attendances.index') }}" class="nav-link" style="margin:0;">
                <i data-lucide="arrow-left"></i>
                Kembali ke Log
            </a>
            <form action="{{ route('admin.archives.monthly') }}" method="POST">
                @csrf
                <button type="submit" class="premium-button">
                    <i data-lucide="file-spreadsheet"></i>
                    Buat Rekap Bulanan (.xlsx)
                </button>
            </form>
        </div>
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
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Tanggal Pembuatan</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Periode Laporan</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Jenis</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Jumlah Data</th>
                    <th style="padding: 1rem; color: var(--text-muted); font-weight: 500;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($archives as $archive)
                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 1rem;">{{ $archive->created_at->format('d/m/Y H:i') }}</td>
                    <td style="padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="{{ $archive->type == 'monthly' ? 'calendar' : 'file-text' }}" style="width: 16px; color: var(--primary);"></i>
                            <span style="font-weight: 500;">{{ $archive->period_label }}</span>
                        </div>
                    </td>
                    <td style="padding: 1rem;">
                        <span class="status-badge {{ $archive->type == 'monthly' ? 'status-present' : 'status-late' }}" style="font-size: 0.7rem; text-transform: uppercase;">
                            {{ $archive->type == 'monthly' ? 'Bulanan' : 'Harian' }}
                        </span>
                    </td>
                    <td style="padding: 1rem; color: var(--text-muted);">{{ $archive->total_records }} Records</td>
                    <td style="padding: 1rem;">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.archives.download', $archive->id) }}" class="nav-link" style="padding: 0.5rem; margin: 0; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem;" title="Download Excel">
                                <i data-lucide="download" style="width: 18px; color: var(--primary);"></i>
                            </a>
                            <form action="{{ route('admin.archives.destroy', $archive->id) }}" method="POST" onsubmit="return confirm('Hapus arsip ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="nav-link" style="padding: 0.5rem; margin: 0; background: none; border: none; cursor: pointer; color: var(--danger);" title="Hapus">
                                    <i data-lucide="trash-2" style="width: 18px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                        <i data-lucide="folder-search" style="width: 3rem; height: 3rem; margin: 0 auto 1rem; opacity: 0.2;"></i>
                        <p>Belum ada rekaman arsip di database.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
