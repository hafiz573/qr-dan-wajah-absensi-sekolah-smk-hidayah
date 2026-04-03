@extends('layouts.app')

@section('title', 'Proses Wajah Masal')
@section('header_title', 'Sinkronisasi Data Wajah')

@section('content')
<div class="glass-card p-8 text-center" style="max-width: 800px; margin: 0 auto;">
    <div id="setup-area">
        <i data-lucide="scan-face" style="width: 4rem; height: 4rem; margin: 0 auto 1.5rem; color: var(--primary);"></i>
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">Siap Memproses Wajah</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">
            Terdapat <strong>{{ $count }}</strong> siswa yang memiliki foto namun belum memiliki data wajah AI.<br>
            Klik tombol di bawah untuk mulai memproses secara otomatis.
        </p>
        
        <button id="start-btn" class="premium-button" style="padding: 1rem 2rem; font-size: 1.125rem;">
            <i data-lucide="play"></i>
            Mulai Sinkronisasi Otomatis
        </button>
    </div>

    <div id="process-area" style="display: none;">
        <div class="mb-8">
            <div class="flex justify-between items-end mb-2">
                <span id="progress-text" style="font-weight: 600; color: var(--primary);">Memproses 0/{{ $count }}</span>
                <span id="percent-text" style="font-size: 0.875rem; color: var(--text-muted);">0%</span>
            </div>
            <div style="width: 100%; height: 12px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                <div id="progress-bar" style="width: 0%; height: 100%; background: var(--primary); transition: width 0.3s ease;"></div>
            </div>
        </div>

        <div id="current-item" class="glass-card p-6 flex items-center gap-6 text-left mb-6" style="background: #f8fafc;">
            <div id="photo-box" style="width: 100px; height: 100px; border-radius: 12px; overflow: hidden; background: #e2e8f0;">
                <img id="scanned-image" style="width: 100%; height: 100%; object-fit: cover; object-position: top;">
            </div>
            <div style="flex: 1;">
                <h3 id="student-name" style="font-weight: 700; font-size: 1.125rem;">Nama Siswa</h3>
                <p id="student-class" style="color: var(--text-muted); font-size: 0.875rem;">Kelas</p>
                <div id="status-badge" class="mt-2" style="display: inline-block;">
                    <span class="status-badge" style="background: #cbd5e1; color: #475569;">Sedang Memproses...</span>
                </div>
            </div>
        </div>

        <div id="log-container" class="text-left" style="max-height: 200px; overflow-y: auto; background: #f8fafc; padding: 1rem; border-radius: 0.5rem; font-family: monospace; font-size: 0.75rem;">
            <div style="color: var(--text-muted);">Antrean dimulai...</div>
        </div>
    </div>

    <div id="finish-area" style="display: none;">
        <i data-lucide="check-circle" style="width: 4rem; height: 4rem; margin: 0 auto 1.5rem; color: var(--success);"></i>
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">Sinkronisasi Selesai!</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">
            Berhasil memproses <strong id="success-count">0</strong> siswa.<br>
            Data wajah sekarang sudah siap digunakan untuk scanner.
        </p>
        <a href="{{ route('admin.students.index') }}" class="premium-button">
            Kembali ke Daftar Siswa
        </a>
    </div>
</div>

<!-- Hidden Elements for Processing -->
<canvas id="temp-canvas" style="display: none;"></canvas>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const setupArea = document.getElementById('setup-area');
    const processArea = document.getElementById('process-area');
    const finishArea = document.getElementById('finish-area');
    const startBtn = document.getElementById('start-btn');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const percentText = document.getElementById('percent-text');
    const logContainer = document.getElementById('log-container');
    
    let studentsToProcess = [];
    let currentIndex = 0;
    let successCount = 0;

    async function initModels() {
        const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights/';
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
    }

    function addLog(message, color = '#64748b') {
        const div = document.createElement('div');
        div.style.color = color;
        div.style.marginBottom = '2px';
        div.innerText = `[${new Date().toLocaleTimeString()}] ${message}`;
        logContainer.prepend(div);
    }

    async function startSync() {
        setupArea.style.display = 'none';
        processArea.style.display = 'block';
        
        addLog('Menginisialisasi model AI...');
        await initModels();
        addLog('Model AI siap. Mengambil daftar siswa...');

        try {
            const response = await fetch("{{ route('api.students.to-sync') }}");
            studentsToProcess = await response.json();
            
            if (studentsToProcess.length === 0) {
                addLog('Tidak ada siswa yang butuh sinkronisasi.');
                showFinish();
                return;
            }

            addLog(`Ditemukan ${studentsToProcess.length} siswa. Memulai pemrosesan...`);
            processNext();
        } catch (e) {
            addLog(`Gagal mengambil data: ${e.message}`, '#ef4444');
        }
    }

    async function processNext() {
        if (currentIndex >= studentsToProcess.length) {
            showFinish();
            return;
        }

        const student = studentsToProcess[currentIndex];
        
        // Update UI
        document.getElementById('student-name').innerText = student.name;
        document.getElementById('student-class').innerText = `Kelas ${student.class}`;
        document.getElementById('scanned-image').src = student.photo_url;
        
        const count = studentsToProcess.length;
        const percent = Math.round(((currentIndex) / count) * 100);
        progressBar.style.width = `${percent}%`;
        progressText.innerText = `Memproses ${currentIndex + 1}/${count}`;
        percentText.innerText = `${percent}%`;

        addLog(`Memproses ${student.name}...`);

        try {
            // Load image
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.src = student.photo_url;
            
            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = () => reject(new Error('Gagal memuat gambar'));
            });

            // Scan face
            const result = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();

            if (result) {
                // Save to server
                const saveResponse = await fetch(`/admin/students/face-setup/${student.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        descriptor: Array.from(result.descriptor)
                    })
                });

                const saveData = await saveResponse.json();
                if (saveData.success) {
                    addLog(`Berhasil: ${student.name}`, '#10b981');
                    successCount++;
                } else {
                    addLog(`Server Error: ${student.name} - ${saveData.message}`, '#f59e0b');
                }
            } else {
                addLog(`Gagal: Wajah tidak terdeteksi pada foto ${student.name}`, '#f59e0b');
            }
        } catch (e) {
            addLog(`Error: ${student.name} - ${e.message}`, '#ef4444');
        }

        currentIndex++;
        setTimeout(processNext, 500); // Small delay to prevent browser freeze
    }

    function showFinish() {
        progressBar.style.width = `100%`;
        percentText.innerText = `100%`;
        addLog('Semua proses selesai.');
        
        setTimeout(() => {
            processArea.style.display = 'none';
            finishArea.style.display = 'block';
            document.getElementById('success-count').innerText = successCount;
            lucide.createIcons();
        }, 1000);
    }

    startBtn.onclick = startSync;
    lucide.createIcons();
</script>
@endpush
