@extends('layouts.app')

@section('title', 'Setup Wajah - ' . $student->name)
@section('header_title', 'Registrasi Wajah Siswa')

@section('content')
<div class="flex gap-6">
    <!-- Camera/Photo Upload Section -->
    <div class="glass-card p-6 flex-1">
        <div class="mb-6">
            <h2 style="font-size: 1.125rem; font-weight: 600;">{{ $student->name }} ({{ $student->class }})</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Unggah foto atau ambil gambar langsung untuk mengekstrak data wajah.</p>
        </div>

        <div id="video-container" style="position: relative; width: 100%; height: 400px; background: #1e293b; border-radius: 0.75rem; overflow: hidden; margin-bottom: 1.5rem; border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center;">
            <video id="video" width="100%" height="100%" autoplay muted style="object-fit: cover; object-position: top; display: none;"></video>
            <img id="photo-preview" style="width: 100%; height: 100%; object-fit: contain; display: none;">
            <canvas id="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
            
            <div id="placeholder" class="text-center">
                <i data-lucide="user-square-2" style="width: 60px; height: 60px; color: var(--text-muted); opacity: 0.3; margin: 0 auto 1rem;"></i>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Silakan Aktifkan Kamera atau Unggah Foto</p>
            </div>

            <div id="loading-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.7); z-index: 10;">
                <div style="text-align: center;">
                    <i data-lucide="loader-2" class="animate-spin mb-2" style="width: 2rem; height: 2rem;"></i>
                    <p style="font-size: 0.875rem;">Memuat AI model...</p>
                </div>
            </div>
        </div>

        <div class="flex gap-4 items-center">
            <button id="start-camera" class="premium-button">
                <i data-lucide="camera"></i>
                Aktifkan Kamera
            </button>
            <button id="capture" class="premium-button" style="background: var(--success); display: none;">
                <i data-lucide="scan"></i>
                Ambil Data Wajah
            </button>
            <div style="color: var(--text-muted); font-size: 0.875rem;">Atau</div>
            <input type="file" id="photo-upload" accept="image/*" style="display: none;">
            <button id="trigger-upload" class="premium-button" style="background: #f1f5f9; color: var(--text-main); border: 1px solid #cbd5e1;">
                <i data-lucide="upload"></i>
                Unggah Foto
            </button>
        </div>
    </div>

    <!-- Status & Info Section -->
    <div class="glass-card p-6" style="width: 350px;">
        <h3 style="font-size: 1rem; margin-bottom: 1rem;">Status Registrasi</h3>
        
        <div id="status-card" class="mb-6 p-4 rounded-lg" style="background: #f8fafc; border: 1px solid var(--glass-border);">
            <div id="status-text" class="status-badge status-absent">Belum Ada Data</div>
            <p id="status-desc" style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">Siswa belum terdaftar untuk fitur scan wajah.</p>
        </div>

        <div class="mb-6">
            <h4 style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem;">QR Code Siswa</h4>
            <div class="glass-card p-3 flex justify-center" style="background: white;">
                {!! QrCode::size(150)->color(0, 0, 0)->backgroundColor(255,255,255)->generate($student->qr_token) !!}
            </div>
            <p style="text-align: center; margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">Token: {{ $student->qr_token }}</p>
            
            <a href="{{ route('admin.students.qr-download', $student->id) }}" class="premium-button" style="width: 100%; justify-content: center; background: #f1f5f9; color: var(--text-main); border: 1px solid #cbd5e1;">
                <i data-lucide="download"></i>
                Download QR Code
            </a>
        </div>

        <div id="save-section" style="display: none;">
            <button id="save-data" class="premium-button" style="width: 100%;">
                <i data-lucide="save"></i>
                Simpan Ke Database
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const studentId = '{{ $student->id }}';
    const video = document.getElementById('video');
    const photoPreview = document.getElementById('photo-preview');
    const placeholder = document.getElementById('placeholder');
    const captureBtn = document.getElementById('capture');
    const startCamBtn = document.getElementById('start-camera');
    const uploadInput = document.getElementById('photo-upload');
    const triggerUpload = document.getElementById('trigger-upload');
    const loadingOverlay = document.getElementById('loading-overlay');
    const saveBtn = document.getElementById('save-data');
    const saveSection = document.getElementById('save-section');
    
    let descriptor = null;

    // Load AI models
    async function loadModels() {
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/';
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            loadingOverlay.style.display = 'none';
        } catch (e) {
            alert('Gagal memuat AI models. Cek koneksi internet.');
        }
    }

    loadModels();

    // Start Camera
    startCamBtn.onclick = async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            video.srcObject = stream;
            video.style.display = 'block';
            photoPreview.style.display = 'none';
            placeholder.style.display = 'none';
            captureBtn.style.display = 'inline-flex';
            startCamBtn.style.display = 'none';
        } catch (err) {
            alert('Tidak dapat mengakses kamera: ' + err);
        }
    };

    // Process Image for Face Data
    async function processImage(input) {
        const detection = await faceapi.detectSingleFace(input).withFaceLandmarks().withFaceDescriptor();
        
        if (detection) {
            descriptor = JSON.stringify(Array.from(detection.descriptor));
            
            document.getElementById('status-text').className = 'status-badge status-present';
            document.getElementById('status-text').innerText = 'Wajah Terdeteksi';
            document.getElementById('status-desc').innerText = 'Data berhasil diekstrak. Silakan klik simpan.';
            saveSection.style.display = 'block';
        } else {
            alert('Wajah tidak terdeteksi. Gunakan foto yang lebih jelas.');
            saveSection.style.display = 'none';
        }
    }

    // Capture from Camera
    captureBtn.onclick = async () => {
        await processImage(video);
    };

    // Upload Photo
    triggerUpload.onclick = () => uploadInput.click();
    uploadInput.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
            const img = await faceapi.bufferToImage(file);
            photoPreview.src = img.src;
            photoPreview.style.display = 'block';
            video.style.display = 'none';
            placeholder.style.display = 'none';
            await processImage(img);
        }
    };

    // Save Data to Server
    saveBtn.onclick = async () => {
        if (!descriptor) return;
        saveBtn.disabled = true;
        saveBtn.innerText = 'Menyimpan...';

        const formData = new FormData();
        formData.append('descriptor', descriptor);
        if (uploadInput.files[0]) {
            formData.append('photo', uploadInput.files[0]);
        }
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch(`/admin/students/face-setup/${studentId}`, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server Error:', errorText);
                throw new Error('Gagal menyimpan ke server (Status: ' + response.status + ')');
            }

            const data = await response.json();
            if (data.success) {
                alert('Berhasil disimpan!');
                window.location.href = '{{ route("admin.students.index") }}';
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan: ' + err.message);
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Simpan Ke Database';
        }
    };
</script>
@endpush
