@extends('layouts.app')

@section('title', 'WhatsApp Connect')
@section('header_title', 'Koneksi WhatsApp')

@section('content')
<div class="glass-card p-6" style="max-width: 800px; margin: 0 auto;">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Status & QR Section -->
        <div class="flex-1">
            <div class="mb-6">
                <h2 style="font-size: 1.125rem; font-weight: 600;">Status Koneksi</h2>
                <p id="connection-status-text" style="color: var(--text-muted); font-size: 0.875rem;">Mengecek status perangkat...</p>
            </div>

            <div id="qr-container" style="display: none; background: white; padding: 1.5rem; border-radius: 1rem; width: fit-content; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <img id="qr-image" src="" alt="Scan Me" style="width: 256px; height: 256px;">
                <p style="text-align: center; font-size: 0.75rem; color: var(--text-muted); margin-top: 1rem;">Buka WhatsApp > Perangkat Tertaut > Tautkan Perangkat</p>
            </div>

            <div id="connected-container" style="display: none; text-align: center; padding: 2rem;">
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 2rem; border-radius: 1rem; display: inline-flex; flex-direction: column; align-items: center; gap: 1rem;">
                    <i data-lucide="check-circle" style="width: 48px; height: 48px;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 700;">Terhubung!</h3>
                    <p style="font-size: 0.875rem;">Sistem siap mengirim laporan harian otomatis.</p>
                </div>
                
                <div class="mt-8">
                    <form action="{{ route('admin.whatsapp.logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan koneksi WhatsApp?')">
                        @csrf
                        <button type="submit" class="premium-button" style="background: var(--danger); border-color: var(--danger);">
                            <i data-lucide="log-out"></i>
                            Putuskan Koneksi WhatsApp
                        </button>
                    </form>
                </div>
            </div>

            <div id="initializing-container" style="text-align: center; padding: 2rem;">
                <div class="animate-spin" style="margin: 0 auto 1rem; width: 32px; height: 32px; border: 4px solid var(--glass-border); border-top-color: var(--primary); border-radius: 50%;"></div>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Memulai modul WhatsApp...</p>
            </div>
        </div>

        <!-- Test Message Section -->
        <div class="flex-1 border-l border-gray-100 pl-0 md:pl-8" id="test-message-section" style="opacity: 0.5; pointer-events: none;">
            <div class="mb-6">
                <h2 style="font-size: 1.125rem; font-weight: 600;">Uji Coba Kirim</h2>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Kirim pesan percobaan untuk memastikan koneksi aktif.</p>
            </div>

            @if(session('success'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem;">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem;">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.whatsapp.test') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Nomor Tujuan</label>
                    <input type="text" name="number" class="glass-card w-full" style="padding: 0.5rem 0.75rem; outline: none;" placeholder="08xxxxxx">
                </div>
                <div class="mb-6">
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Pesan</label>
                    <textarea name="message" class="glass-card w-full" style="padding: 0.5rem 0.75rem; outline: none;" rows="3">Halo, ini adalah pesan uji coba dari Sistem Absensi.</textarea>
                </div>
                <button type="submit" class="premium-button w-full">
                    <i data-lucide="send"></i>
                    Kirim Pesan
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateStatus() {
        fetch("{{ route('admin.whatsapp.status') }}")
            .then(res => res.json())
            .then(data => {
                const statusText = document.getElementById('connection-status-text');
                const qrContainer = document.getElementById('qr-container');
                const qrImage = document.getElementById('qr-image');
                const connectedContainer = document.getElementById('connected-container');
                const initializingContainer = document.getElementById('initializing-container');
                const testSection = document.getElementById('test-message-section');

                statusText.innerText = `Status: ${data.status}`;

                if (data.status === 'QR_READY' && data.qr) {
                    qrContainer.style.display = 'block';
                    qrImage.src = data.qr;
                    connectedContainer.style.display = 'none';
                    initializingContainer.style.display = 'none';
                    testSection.style.opacity = '0.5';
                    testSection.style.pointerEvents = 'none';
                } else if (data.status === 'CONNECTED') {
                    qrContainer.style.display = 'none';
                    connectedContainer.style.display = 'block';
                    initializingContainer.style.display = 'none';
                    testSection.style.opacity = '1';
                    testSection.style.pointerEvents = 'auto';
                    statusText.innerText = 'WhatsApp Terhubung';
                    statusText.style.color = 'var(--success)';
                } else if (data.status === 'INITIALIZING') {
                    qrContainer.style.display = 'none';
                    connectedContainer.style.display = 'none';
                    initializingContainer.style.display = 'block';
                } else if (data.status === 'DISCONNECTED') {
                    statusText.innerText = 'Server Bridge Offline / Terputus';
                    statusText.style.color = 'var(--danger)';
                    qrContainer.style.display = 'none';
                    connectedContainer.style.display = 'none';
                    initializingContainer.style.display = 'none';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('connection-status-text').innerText = 'Gagal memuat status. Pastikan server bridge aktif.';
            });
    }

    // Poll every 3 seconds
    setInterval(updateStatus, 3000);
    updateStatus();
</script>
@endpush

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
@endsection
