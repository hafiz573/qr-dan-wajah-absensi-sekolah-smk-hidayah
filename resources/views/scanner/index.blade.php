@extends('layouts.app')

@section('title', 'Scanner Kehadiran')
@section('header_title', 'Sistem Scan QR & Wajah')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Main Scanner Area -->
        <div class="glass-card p-8 flex-1">
        <div id="scanner-wrapper" style="position: relative; border-radius: 1rem; overflow: hidden; border: 2px solid var(--glass-border); aspect-ratio: 4/3; background: #000;">
            <div id="reader" style="width: 100%; height: 100%;"></div>
            <video id="face-video" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: top; display: none;"></video>
            <canvas id="face-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
            
            <!-- Real-time Feedback Badge -->
            <div id="face-hint-badge" style="position: absolute; top: 1.5rem; left: 50%; transform: translateX(-50%); z-index: 20; display: none;">
                <div class="glass-card flex items-center gap-2" style="background: rgba(15, 23, 42, 0.85); border: 1px solid var(--primary); padding: 0.5rem 1rem; border-radius: 2rem; color: white; white-space: nowrap; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    <i id="hint-icon" data-lucide="scan" style="width: 1.25rem; height: 1.25rem; color: var(--primary);"></i>
                    <span id="hint-text" style="font-size: 0.8125rem; font-weight: 600;">Menunggu Wajah...</span>
                </div>
            </div>
            <canvas id="brightness-canvas" style="display: none;" width="50" height="50"></canvas>
            
            <!-- Information Overlay -->
            <div id="scan-hint" style="position: absolute; bottom: 2rem; left: 0; right: 0; text-align: center; z-index: 10;">
                <span class="glass-card p-3" style="font-size: 0.875rem; background: rgba(255,255,255,0.9); color: var(--text-main); border: 1px solid var(--primary);">
                    Tunjukkan <strong style="color: var(--primary);">QR CODE</strong> Anda ke Kamera
                </span>
            </div>

            <!-- Loader / Busy -->
            <div id="scanner-loader" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.82); display: flex; align-items: center; justify-content: center; z-index: 100; display: none;">
                <div class="text-center">
                    <i data-lucide="loader-2" class="animate-spin mb-4" style="width: 3rem; height: 3rem; color: var(--primary);"></i>
                    <p id="loader-text" style="font-weight: 500;">Sedang Memvalidasi...</p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-between items-center">
            <div id="status-message" class="animate-fade-in" style="display: none;">
                <div class="glass-card p-4 flex items-center gap-3" style="border-color: var(--primary);">
                    <i data-lucide="info" style="color: var(--primary);"></i>
                    <div>
                        <p id="msg-title" style="font-weight: 600; font-size: 0.875rem;">Status Scan</p>
                        <p id="msg-desc" style="font-size: 0.75rem; color: var(--text-muted);">Menunggu input...</p>
                    </div>
                </div>
            </div>
            
            <button id="reset-scanner" class="premium-button" style="display: none; background: #ffffff; color: var(--text-main); border: 1px solid #cbd5e1;">
                <i data-lucide="refresh-ccw"></i>
                Ulangi Scan
            </button>
        </div>
    </div>

    <!-- Student Detail & Helper Area -->
    <div class="glass-card p-6" style="width: 400px;">
        <div id="empty-state" class="text-center py-10">
            <i data-lucide="user" style="width: 4rem; height: 4rem; margin: 0 auto; opacity: 0.2;"></i>
            <p style="margin-top: 1rem; color: var(--text-muted);">Belum ada siswa teridentifikasi.</p>
        </div>

        <div id="student-info" style="display: none;" class="animate-fade-in">
            <div id="student-photo" style="width: 100%; height: 280px; border-radius: 1rem; background: #f8fafc; margin-bottom: 1.5rem; overflow: hidden; border: 1px solid var(--glass-border);">
                <img id="found-photo" src="" style="width: 100%; height: 100%; object-fit: cover; object-position: top; display: none;">
                <div id="photo-placeholder" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="user" style="width: 50px; height: 50px; color: var(--primary);"></i>
                </div>
            </div>
            <h2 id="found-name" style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem;">-</h2>
            <p id="found-class" style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">Siswa / Kelas</p>
            
            <div style="background: rgba(253,186,116,0.1); padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--warning); margin-bottom: 1.5rem;">
                <p style="font-size: 0.75rem; font-weight: 600; color: var(--warning);">Validasi Kehadiran</p>
                <p style="font-size: 0.8125rem; color: var(--text-muted);">Menunggu verifikasi wajah...</p>
            </div>
        </div>

        <div style="border-top: 1px solid var(--glass-border); padding-top: 1.5rem; margin-top: 2rem;">
            <p style="font-size: 0.875rem; margin-bottom: 1rem; font-weight: 600; color: var(--text-muted);">Gunakan Mode Khusus Guru?</p>
            <button id="guru-backup" class="premium-button" style="width: 100%; background: var(--text-muted);">
                <i data-lucide="shield-check"></i>
                Masuk Mode Backup (Guru)
            </button>
        </div>
    </div>
</div>

<!-- Modal Guru Backup (Simple Overlay) -->
<div id="guru-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; display: none; align-items: center; justify-content: center;">
    <div class="glass-card p-6" style="width: 400px;">
        <h3 class="mb-4">Mode Input Manual</h3>
        <p class="mb-4" style="font-size: 0.875rem; color: var(--text-muted);">Guru dapat memasukkan absensi siswa jika sistem otomatis bermasalah.</p>
        <div class="mb-4">
            <select id="manual-student" class="glass-card" style="width: 100%; padding: 0.75rem; outline: none;">
                <option value="">Pilih Siswa...</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->nis }} - {{ $student->name }} ({{ $student->class }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button id="save-manual" class="premium-button" style="flex: 1;">Simpan</button>
            <button id="close-guru" class="nav-link" style="padding: 0.5rem; margin: 0; background: #f1f5f9; border: 1px solid #e2e8f0;">Tutup</button>
        </div>
    </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(15px); z-index: 2000; display: none; align-items: center; justify-content: center;">
    <div class="glass-card p-10 text-center animate-fade-in" style="width: 500px; border-color: var(--success);">
        <div class="mb-6" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 2px solid var(--success);">
            <i data-lucide="check" style="width: 40px; height: 40px; color: var(--success);"></i>
        </div>
        <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--success);">ABSENSI BERHASIL!</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Selamat belajar, selamat beraktivitas!</p>
        
        <div class="glass-card p-6 mb-8" style="background: rgba(255,255,255,0.03);">
            <h3 id="success-name" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem;">Nama Siswa</h3>
            <p id="success-class" style="color: var(--text-muted);">Kelas XII RPL 1</p>
            <div id="success-time" style="margin-top: 1rem; font-family: monospace; font-size: 1.125rem; font-weight: 700;">12:00:00</div>
        </div>
        
        <div id="countdown" style="font-size: 0.875rem; color: var(--text-muted);">
            Menutup dalam <span id="timer" style="font-weight: 700; color: white;">5</span> detik...
        </div>
    </div>
</div>
<!-- Warning Modal (Already Absent) -->
<div id="warning-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(15px); z-index: 2000; display: none; align-items: center; justify-content: center;">
    <div class="glass-card p-10 text-center animate-fade-in" style="width: 500px; border-color: var(--warning);">
        <div class="mb-6" style="width: 80px; height: 80px; background: rgba(251, 191, 36, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 2px solid var(--warning);">
            <i data-lucide="alert-triangle" style="width: 40px; height: 40px; color: var(--warning);"></i>
        </div>
        <h2 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--warning);">SUDAH ABSEN!</h2>
        <p id="warning-message" style="color: var(--text-muted); margin-bottom: 2rem;">Anda sudah melakukan absensi untuk hari ini.</p>
        
        <div class="glass-card p-6 mb-8" style="background: #f8fafc; border: 1px solid var(--glass-border);">
            <h3 id="warning-name" style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--text-main);">Nama Siswa</h3>
            <p id="warning-class" style="color: var(--text-muted);">Terima kasih atas kedisiplinan Anda.</p>
        </div>
        
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            Menutup otomatis dalam <span id="w-timer" style="font-weight: 700; color: var(--warning);">3</span> detik...
        </div>
    </div>
</div>
<!-- Error Modal (QR Failed / Connection Error) -->
<div id="error-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(15px); z-index: 2000; display: none; align-items: center; justify-content: center;">
    <div class="glass-card p-10 text-center animate-fade-in" style="width: 500px; border-color: var(--danger);">
        <div class="mb-6" style="width: 80px; height: 80px; background: rgba(220, 38, 38, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 2px solid var(--danger);">
            <i data-lucide="x-circle" style="width: 40px; height: 40px; color: var(--danger);"></i>
        </div>
        <h2 id="error-title" style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--danger);">QR GAGAL</h2>
        <p id="error-desc" style="color: var(--text-muted); margin-bottom: 2rem;">QR Code Tidak Dikenali! Gunakan QR Backup.</p>
        
        <button onclick="closeErrorModal()" class="premium-button" style="width: 100%; background: var(--danger);">
            <i data-lucide="refresh-ccw"></i>
            Coba Scan Ulang
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    let scanner = null;
    let isProcessing = false;
    let identifiedStudentId = null;
    let targetDescriptor = null;
    const faceVideo = document.getElementById('face-video');
    const faceOverlay = document.getElementById('face-overlay');
    const loader = document.getElementById('scanner-loader');
    const loaderText = document.getElementById('loader-text');
    const statusMsg = document.getElementById('status-message');
    const resetBtn = document.getElementById('reset-scanner');

    // Load Face Models
    let modelsLoaded = false;
    async function initFaceAPI() {
        if (modelsLoaded) return true;
        try {
            // Menggunakan jsDelivr CDN yang lebih stabil dan mendukung CORS untuk lingkungan lokal
            const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights/';
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            modelsLoaded = true;
            console.log("Models loaded successfully");
            return true;
        } catch (err) {
            console.error("Failed to load models:", err);
            return false;
        }
    }
    initFaceAPI();

    function updateHint(icon, text, color) {
        const hintBadge = document.getElementById('face-hint-badge');
        const hintText = document.getElementById('hint-text');
        const hintIcon = document.getElementById('hint-icon');
        
        if (!hintBadge) return;
        
        hintBadge.style.display = 'block';
        hintText.innerText = text;
        hintIcon.setAttribute('data-lucide', icon);
        hintBadge.querySelector('.glass-card').style.borderColor = color;
        hintIcon.style.color = color;
        lucide.createIcons();
    }

    function getBrightness(video) {
        const canvas = document.getElementById('brightness-canvas');
        if (!canvas) return 100;
        const ctx = canvas.getContext('2d');
        try {
            ctx.drawImage(video, 0, 0, 50, 50);
            const imageData = ctx.getImageData(0, 0, 50, 50);
            const data = imageData.data;
            let colorSum = 0;
            for (let x = 0; x < data.length; x += 4) {
                colorSum += Math.floor((data[x] + data[x+1] + data[x+2]) / 3);
            }
            return Math.floor(colorSum / (50 * 50));
        } catch (e) {
            return 100;
        }
    }

    // Start QR Scanner
    function startQRScanner() {
        scanner = new Html5Qrcode("reader");
        scanner.start({ facingMode: "user" }, {
            fps: 15, // Ditingkatkan untuk respon lebih cepat
        }, onScanSuccess).catch(err => {
            alert("Kesalahan Kamera: " + err);
        });
        
        document.getElementById('scan-hint').style.display = 'block';
        document.getElementById('scan-hint').innerHTML = `<span class="glass-card p-3">Tunjukkan <strong style="color: var(--primary);">QR CODE</strong> di mana saja ke Kamera</span>`;
    }

    async function onScanSuccess(decodedText) {
        if (isProcessing) return;
        isProcessing = true;
        
        // Reset variables for new session
        identifiedStudentId = null;
        targetDescriptor = null;
        
        showLoader("Memvalidasi QR...");
        
        try {
            const response = await fetch('/scanner/validate-qr', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ token: decodedText })
            });
            const data = await response.json();

            if (data.success) {
                identifiedStudentId = data.student.id;
                targetDescriptor = new Float32Array(data.face_descriptor);
                
                showStudentInfo(data.student);
                showStatus("QR TERDETEKSI", "Lanjutkan dengan Scan Wajah", "var(--success)");
                
                // Stop QR, Start Face
                await scanner.stop();
                startFaceVerification();
            } else {
                showStatus("QR GAGAL", data.message, "var(--danger)");
                isProcessing = false;
                hideLoader();
            }
        } catch (e) {
            showStatus("Gagal Koneksi", "Periksa server.", "var(--danger)");
            isProcessing = false;
            hideLoader();
        }
    }

    async function startFaceVerification() {
        document.getElementById('reader').style.display = 'none';
        faceVideo.style.display = 'block';
        document.getElementById('scan-hint').style.display = 'block';
        document.getElementById('scan-hint').innerHTML = `<span class="glass-card p-3" style="border-color: var(--warning);">Tatap Kamera untuk <strong style="color: var(--warning);">SCAN WAJAH</strong></span>`;

        // Pastikan model sudah siap
        if (!modelsLoaded) {
            showLoader("Menyiapkan Sensor AI...");
            const ready = await initFaceAPI();
            if (!ready) {
                hideLoader();
                showStatus("Sensor Gagal", "Gagal memuat modul AI wajah.", "var(--danger)");
                return;
            }
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { 
                facingMode: "user",
                width: { ideal: 640 }, 
                height: { ideal: 480 }
            } });
            faceVideo.srcObject = stream;
            
            await new Promise((resolve) => {
                faceVideo.onloadedmetadata = () => {
                    faceVideo.play();
                    resolve();
                };
            });
            
            hideLoader();
        } catch (err) {
            hideLoader();
            showStatus("Gagal Kamera", "Kamera tidak dapat diakses untuk scan wajah.", "var(--danger)");
            console.error(err);
            resetBtn.style.display = 'inline-flex';
            return;
        }

        updateHint("scan", "Menunggu Wajah...", "var(--primary)");

        // Loop for recognition - Optimized Interval (250ms)
        const loop = setInterval(async () => {
            try {
                if (!identifiedStudentId) {
                    clearInterval(loop);
                    return;
                }

                if (faceVideo.paused || faceVideo.ended) return;

                // 1. Check Brightness
                const brightness = getBrightness(faceVideo);
                if (brightness < 35) { // Sedikit dilonggarkan dari 40
                    updateHint("sun-off", "Terlalu Gelap!", "var(--warning)");
                    return;
                }

                // 2. Detect Face - Dilonggarkan scoreThreshold agar lebih sensitif
                const detection = await faceapi.detectSingleFace(
                    faceVideo, 
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 160, scoreThreshold: 0.4 })
                ).withFaceLandmarks().withFaceDescriptor();
                
                if (!detection) {
                    updateHint("user-minus", "Wajah Tidak Terdeteksi", "var(--primary)");
                    return;
                }

                // 3. Check Distance (Face Box size)
                const box = detection.detection.box;
                if (box.width < 100) { // Dilonggarkan dari 120
                    updateHint("maximize-2", "Mendekat ke Kamera", "var(--warning)");
                    return;
                }

                // 4. Compare with target
                if (!targetDescriptor) {
                    updateHint("alert-circle", "Data Wajah Error", "var(--danger)");
                    return;
                }

                const distance = faceapi.euclideanDistance(detection.descriptor, targetDescriptor);
                
                // Menggunakan 0.42 sebagai jalan tengah keamanan & kemudahan
                if (distance < 0.42) { 
                    updateHint("check-circle", "Wajah Cocok!", "var(--success)");
                    clearInterval(loop);
                    submitAttendance(identifiedStudentId);
                } else {
                    updateHint("shield-alert", "Wajah Tidak Cocok", "var(--danger)");
                }
            } catch (err) {
                console.error("Face Loop Error:", err);
                updateHint("alert-triangle", "Sensor Error", "var(--danger)");
            }
        }, 250); // Sedikit diperlambat agar browser tidak berat
    }

    let isSubmitting = false;

    async function submitAttendance(id) {
        if (isSubmitting) return;
        isSubmitting = true;
        
        showLoader("Menyimpan Kehadiran...");
        
        // Stop Camera Streams
        if (faceVideo.srcObject) {
            faceVideo.srcObject.getTracks().forEach(track => track.stop());
        }
        
        const response = await fetch('/scanner/submit-presence', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ student_id: id })
        });
        const data = await response.json();
        
        hideLoader();
        if (data.success) {
            // ... Success Logic ...
            const modal = document.getElementById('success-modal');
            document.getElementById('success-name').innerText = data.student_name;
            document.getElementById('success-class').innerText = document.getElementById('found-class').innerText;
            document.getElementById('success-time').innerText = new Date().toLocaleTimeString('id-id');
            modal.style.display = 'flex';
            
            // Countdown 5s
            let count = 5;
            const timer = document.getElementById('timer');
            const interval = setInterval(() => {
                count--;
                timer.innerText = count;
                if (count <= 0) {
                    clearInterval(interval);
                    location.reload();
                }
            }, 1000);

        } else if (data.message.includes('Sudah Melakukan Absensi')) {
            // Show Warning Modal for already absent
            const modal = document.getElementById('warning-modal');
            document.getElementById('warning-name').innerText = document.getElementById('found-name').innerText || 'Siswa';
            modal.style.display = 'flex';
            
            // Countdown 3s
            let count = 3;
            const timer = document.getElementById('w-timer');
            const interval = setInterval(() => {
                count--;
                timer.innerText = count;
                if (count <= 0) {
                    clearInterval(interval);
                    location.reload();
                }
            }, 1000);
        } else {
            showStatus("GAGAL", data.message, "var(--danger)");
            resetBtn.style.display = 'inline-flex';
            isSubmitting = false; // Allow retry if error other than "already absent"
        }
    }

    // UI Helpers
    function showLoader(text) {
        loaderText.innerText = text;
        loader.style.display = 'flex';
    }
    function hideLoader() { loader.style.display = 'none'; }

    function showStatus(title, desc, color) {
        // Jika status adalah error (merah), tampilkan modal
        if (color === 'var(--danger)' || title.includes('GAGAL') || title.includes('Gagal')) {
            document.getElementById('error-title').innerText = title;
            document.getElementById('error-desc').innerText = desc;
            document.getElementById('error-modal').style.display = 'flex';
            lucide.createIcons();
            return;
        }

        statusMsg.style.display = 'block';
        document.getElementById('msg-title').innerText = title;
        document.getElementById('msg-title').style.color = color;
        document.getElementById('msg-desc').innerText = desc;
    }

    function closeErrorModal() {
        document.getElementById('error-modal').style.display = 'none';
        location.reload(); // Reload untuk reset scanner state
    }

    function showStudentInfo(student) {
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('student-info').style.display = 'block';
        document.getElementById('found-name').innerText = student.name;
        document.getElementById('found-class').innerText = student.class;
        if (student.photo) {
            document.getElementById('found-photo').src = `/storage/${student.photo}`;
            document.getElementById('found-photo').style.display = 'block';
            document.getElementById('photo-placeholder').style.display = 'none';
        }
    }

    // Modal Guru Handlers
    document.getElementById('guru-backup').onclick = () => document.getElementById('guru-modal').style.display = 'flex';
    document.getElementById('close-guru').onclick = () => document.getElementById('guru-modal').style.display = 'none';
    
    document.getElementById('save-manual').onclick = async () => {
        const studentId = document.getElementById('manual-student').value;
        if (!studentId) {
            alert('Pilih siswa terlebih dahulu!');
            return;
        }
        
        document.getElementById('guru-modal').style.display = 'none';
        
        // Use standard success modal path
        // We temporarily set text for the success modal because manual submit 
        // doesn't fill 'found-class' label.
        const selectedOption = document.getElementById('manual-student').options[document.getElementById('manual-student').selectedIndex];
        document.getElementById('found-class').innerText = selectedOption.text;
        
        await submitAttendance(studentId);
    };

    resetBtn.onclick = () => location.reload();

    startQRScanner();
    lucide.createIcons();
</script>
@endpush
