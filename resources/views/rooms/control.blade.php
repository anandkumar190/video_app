<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Room Control Panel - {{ $room->title }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #374151;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .room-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-live {
            background: rgba(34, 197, 94, 0.8);
        }

        .status-offline {
            background: rgba(107, 114, 128, 0.8);
        }

        .main-content {
            padding: 2rem 0;
        }

        .control-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 1rem;
            height: 600px;
        }

        .preview-tile {
            background: #1f2937;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 3px solid #374151;
            transition: border-color 0.3s;
        }

        .preview-tile.program {
            grid-column: 1 / -1;
            border-color: #ef4444;
        }

        .preview-tile video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-label {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .preview-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .control-panel {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .panel-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }

        .control-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-start {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-stop {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .btn-stop:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .source-selector {
            margin-bottom: 2rem;
        }

        .source-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .source-btn {
            padding: 0.8rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            text-align: center;
        }

        .source-btn.active {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .source-btn:hover:not(.active) {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
        }

        .info-value {
            color: #374151;
        }

        .uploads-section {
            grid-column: 1 / -1;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .uploads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .upload-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .upload-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .upload-card.selected {
            border-color: #667eea;
            background: #f8fafc;
        }

        .upload-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .upload-meta {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .schedule-form {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-schedule {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-schedule:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
        }

        .btn-schedule:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .upload-form {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s;
        }

        .upload-form:hover {
            border-color: #667eea;
            background: #f8fafc;
        }

        .upload-form.dragover {
            border-color: #667eea;
            background: #eff6ff;
        }

        .upload-input {
            margin-top: 1rem;
        }

        .upload-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .control-grid {
                grid-template-columns: 1fr;
            }

            .source-options {
                grid-template-columns: 1fr;
            }

            .control-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="room-title">{{ $room->title }}</div>
                <div class="status-badge status-{{ $room->status }}">
                    {{ $room->status }}
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="control-grid">
                <div class="preview-grid">
                    <div class="preview-tile program">
                        <div class="preview-label">Program Output</div>
                        <video id="programPreview" muted></video>
                        <div id="programPlaceholder" class="preview-placeholder">Program Preview</div>
                    </div>
                    <div class="preview-tile">
                        <div class="preview-label">Camera A</div>
                        <video id="camAPreview" muted></video>
                        <div id="camAPlaceholder" class="preview-placeholder">Camera A</div>
                    </div>
                    <div class="preview-tile">
                        <div class="preview-label">Camera B</div>
                        <video id="camBPreview" muted></video>
                        <div id="camBPlaceholder" class="preview-placeholder">Camera B</div>
                    </div>
                </div>

                <!-- Hidden video element for clip playback -->
                <video id="clipVideo" style="display: none;" autoplay muted="false"></video>

                <div class="control-panel">
                    <h2 class="panel-title">Stream Controls</h2>

                    <div class="control-buttons">
                        @if($room->status === 'offline')
                            <button id="startBtn" class="btn btn-start" onclick="startRoom()">
                                Start Stream
                            </button>
                        @else
                            <button id="stopBtn" class="btn btn-stop" onclick="stopRoom()">
                                Stop Stream
                            </button>
                        @endif
                    </div>

                    <div class="source-selector">
                        <h3 class="panel-title">Video Source</h3>
                        <div class="source-options">
                            <button class="source-btn active" data-source="camA" onclick="switchSource('camA')">
                                Camera A
                            </button>
                            <button class="source-btn" data-source="camB" onclick="switchSource('camB')">
                                Camera B
                            </button>
                            <button class="source-btn" data-source="clip" onclick="switchSource('clip')">
                                Video Clip
                            </button>
                        </div>
                    </div>
                </div>

                <div class="sidebar">
                    <div class="info-card">
                        <h3 class="panel-title">Room Info</h3>
                        <div class="info-item">
                            <span class="info-label">Room ID</span>
                            <span class="info-value">{{ $room->id }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Slug</span>
                            <span class="info-value">{{ $room->slug }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="info-value">{{ ucfirst($room->status) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created</span>
                            <span class="info-value">{{ $room->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uploads-section">
                <h2 class="panel-title">Upload Management</h2>

                <div class="upload-form" id="uploadForm">
                    <div>Drop MP4 files here or click to select</div>
                    <input type="file" id="fileInput" class="upload-input" accept="video/mp4" multiple style="display:none;">
                    <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click()">
                        Select Files
                    </button>
                </div>

                <h3 class="panel-title" style="margin-top: 2rem;">Available Uploads</h3>

                @if($uploads->count() > 0)
                    <div class="uploads-grid">
                        @foreach($uploads as $upload)
                            <div class="upload-card"
                                 data-upload-id="{{ $upload->id }}"
                                 data-filename="{{ $upload->stored_filename ?? $upload->filename }}"
                                 onclick="selectUpload({{ $upload->id }})">
                                <div class="upload-name">{{ $upload->filename }}</div>
                                <div class="upload-meta">
                                    Duration: {{ gmdate("H:i:s", $upload->duration_sec) }} |
                                    Size: {{ number_format($upload->size_bytes / 1024 / 1024, 1) }} MB
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <p>No uploads available. Upload some MP4 files to get started.</p>
                    </div>
                @endif

                <div class="schedule-form">
                    <h3 class="panel-title">Schedule Upload</h3>
                    <form id="scheduleForm" onsubmit="scheduleUpload(event)">
                        <input type="hidden" id="selectedUploadId" name="upload_id" required>

                        <div class="form-group">
                            <label for="startTime" class="form-label">Start Time</label>
                            <input type="datetime-local" id="startTime" name="start_at" class="form-input" required
                                   min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}">
                        </div>

                        <button type="submit" class="btn-schedule" disabled id="scheduleBtn">
                            Schedule Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        let selectedUpload = null;
        let camAStream = null;
        let camBStream = null;
        let programSender = null;
        let audioSender = null;
        let currentProgramSource = 'camA';
        let videoDevices = [];
        let audioInputs = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        async function enumerateDevices() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                videoDevices = devices.filter(device => device.kind === 'videoinput');
                audioInputs = devices.filter(device => device.kind === 'audioinput');

                console.log(`Found ${videoDevices.length} video devices:`, videoDevices);
                console.log(`Found ${audioInputs.length} audio devices:`, audioInputs);

                if (videoDevices.length < 2) {
                    console.warn('Less than 2 video devices found. Some features may not work.');
                    showDeviceWarning();
                }

                await initializeCameras();
            } catch (error) {
                console.error('Error enumerating devices:', error);
                showDeviceWarning();
            }
        }

        function showDeviceWarning() {
            const warningEl = document.createElement('div');
            warningEl.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #fbbf24;
                color: #92400e;
                padding: 1rem;
                border-radius: 8px;
                font-weight: bold;
                z-index: 1000;
                max-width: 300px;
            `;
            warningEl.textContent = 'Warning: Less than 2 cameras detected. Some features may be limited.';
            document.body.appendChild(warningEl);

            setTimeout(() => warningEl.remove(), 5000);
        }

        async function initializeCameras() {
            try {
                if (videoDevices.length >= 1) {
                    camAStream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: videoDevices[0].deviceId },
                        audio: false
                    });
                    document.getElementById('camAPreview').srcObject = camAStream;
                    document.getElementById('camAPreview').style.display = 'block';
                    document.getElementById('camAPlaceholder').style.display = 'none';
                }

                if (videoDevices.length >= 2) {
                    camBStream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: videoDevices[1].deviceId },
                        audio: false
                    });
                    document.getElementById('camBPreview').srcObject = camBStream;
                    document.getElementById('camBPreview').style.display = 'block';
                    document.getElementById('camBPlaceholder').style.display = 'none';
                }

                switchProgramSource('camA');
            } catch (error) {
                console.error('Error initializing cameras:', error);
            }
        }

        async function switchProgramSource(source) {
            try {
                const programVideo = document.getElementById('programPreview');
                const programPlaceholder = document.getElementById('programPlaceholder');

                let sourceStream = null;

                switch (source) {
                    case 'camA':
                        if (camAStream) {
                            sourceStream = camAStream;
                        }
                        break;
                    case 'camB':
                        if (camBStream) {
                            sourceStream = camBStream;
                        }
                        break;
                    case 'clip':
                        sourceStream = await initializeClipPlayback();
                        if (!sourceStream) {
                            console.error('Failed to initialize clip playback');
                            return;
                        }
                        break;
                }

                if (sourceStream) {
                    programVideo.srcObject = sourceStream;
                    programVideo.style.display = 'block';
                    programPlaceholder.style.display = 'none';
                    currentProgramSource = source;

                    // Replace track if we have an active WebRTC connection
                    if (programSender && sourceStream.getVideoTracks().length > 0) {
                        const videoTrack = sourceStream.getVideoTracks()[0];
                        await programSender.replaceTrack(videoTrack);
                        console.log(`Program source switched to ${source}`);
                    }

                    console.log(`Switched program source to: ${source}`);

                    const sourceEvent = new CustomEvent('sourceChanged', {
                        detail: { source: source, stream: sourceStream }
                    });
                    document.dispatchEvent(sourceEvent);
                }
            } catch (error) {
                console.error('Error switching program source:', error);
            }
        }

        async function initializeClipPlayback() {
            try {
                if (!selectedUpload) {
                    alert('Please select a video clip first');
                    return null;
                }

                const clipVideo = document.getElementById('clipVideo');
                const uploadCard = document.querySelector(`[data-upload-id="${selectedUpload}"]`);

                if (!uploadCard) {
                    console.error('Upload card not found');
                    return null;
                }

                // Get the video URL from storage
                const videoUrl = `/storage/uploads/${uploadCard.dataset.filename || 'clip.mp4'}`;

                // Set video source and wait for metadata to load
                return new Promise((resolve, reject) => {
                    clipVideo.src = videoUrl;
                    clipVideo.loop = true; // Loop the video for continuous playback

                    clipVideo.onloadedmetadata = () => {
                        // Create a canvas to capture the video
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = clipVideo.videoWidth || 1280;
                        canvas.height = clipVideo.videoHeight || 720;

                        // Function to draw video frames to canvas
                        const drawFrame = () => {
                            if (!clipVideo.paused && !clipVideo.ended) {
                                ctx.drawImage(clipVideo, 0, 0, canvas.width, canvas.height);
                                requestAnimationFrame(drawFrame);
                            }
                        };

                        // Start playing and drawing
                        clipVideo.play().then(() => {
                            drawFrame();
                            const stream = canvas.captureStream(30); // 30 FPS
                            resolve(stream);
                        }).catch(reject);
                    };

                    clipVideo.onerror = () => {
                        console.error('Failed to load video');
                        reject(new Error('Failed to load video'));
                    };
                });
            } catch (error) {
                console.error('Error initializing clip playback:', error);
                return null;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            enumerateDevices();

            const uploadForm = document.getElementById('uploadForm');
            const fileInput = document.getElementById('fileInput');

            uploadForm.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadForm.classList.add('dragover');
            });

            uploadForm.addEventListener('dragleave', () => {
                uploadForm.classList.remove('dragover');
            });

            uploadForm.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadForm.classList.remove('dragover');
                const files = Array.from(e.dataTransfer.files).filter(file =>
                    file.type === 'video/mp4'
                );
                if (files.length > 0) {
                    uploadFiles(files);
                }
            });

            uploadForm.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                const files = Array.from(e.target.files);
                if (files.length > 0) {
                    uploadFiles(files);
                }
            });
        });

        async function uploadFiles(files) {
            for (const file of files) {
                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch('/api/uploads', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    });

                    if (response.ok) {
                        console.log(`Uploaded: ${file.name}`);
                    } else {
                        console.error(`Failed to upload: ${file.name}`);
                    }
                } catch (error) {
                    console.error(`Error uploading ${file.name}:`, error);
                }
            }

            setTimeout(() => location.reload(), 1000);
        }

        function startRoom() {
            fetch(`/api/rooms/{{ $room->id }}/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start room');
            });
        }

        function stopRoom() {
            fetch(`/api/rooms/{{ $room->id }}/stop`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to stop room');
            });
        }

        async function switchSource(source) {
            const clipId = source === 'clip' ? selectedUpload : null;

            await switchProgramSource(source);

            fetch(`/api/rooms/{{ $room->id }}/switch`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    source: source,
                    clip_id: clipId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    document.querySelectorAll('.source-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.querySelector(`[data-source="${source}"]`).classList.add('active');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to switch source');
            });
        }

        function selectUpload(uploadId) {
            selectedUpload = uploadId;
            document.getElementById('selectedUploadId').value = uploadId;
            document.getElementById('scheduleBtn').disabled = false;

            document.querySelectorAll('.upload-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`[data-upload-id="${uploadId}"]`).classList.add('selected');
        }

        function scheduleUpload(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const data = {
                upload_id: formData.get('upload_id'),
                start_at: formData.get('start_at')
            };

            fetch(`/api/rooms/{{ $room->id }}/schedule`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    event.target.reset();
                    selectedUpload = null;
                    document.getElementById('scheduleBtn').disabled = true;
                    document.querySelectorAll('.upload-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to schedule upload');
            });
        }
    </script>
</body>
</html>