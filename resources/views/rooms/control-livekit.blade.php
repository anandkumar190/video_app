<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Room Control Panel - {{ $room->title }}</title>

    @vite(['resources/js/app.js'])

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
            z-index: 10;
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

        .connection-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 0.5rem;
            background: #6b7280;
        }

        .connection-indicator.connected {
            background: #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
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
            position: relative;
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

        .upload-card.playing {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .upload-name {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .upload-meta {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .playback-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #e5e7eb;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
        }

        .playback-progress-bar {
            height: 100%;
            background: #10b981;
            width: 0%;
            transition: width 0.3s;
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .toast.show {
            display: block;
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .control-grid {
                grid-template-columns: 1fr;
            }

            .source-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="room-title">
                    {{ $room->title }}
                    <span id="connectionIndicator" class="connection-indicator"></span>
                </div>
                <div class="status-badge status-{{ $room->status }}">
                    <span id="statusText">{{ $room->status }}</span>
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
                        <video id="programPreview" muted autoplay playsinline></video>
                        <div id="programPlaceholder" class="preview-placeholder">Program Preview</div>
                    </div>
                    <div class="preview-tile">
                        <div class="preview-label">Camera A</div>
                        <video id="camAPreview" muted autoplay playsinline></video>
                        <div id="camAPlaceholder" class="preview-placeholder">Camera A</div>
                    </div>
                    <div class="preview-tile">
                        <div class="preview-label">Camera B</div>
                        <video id="camBPreview" muted autoplay playsinline></video>
                        <div id="camBPlaceholder" class="preview-placeholder">Camera B</div>
                    </div>
                </div>

                <div class="control-panel">
                    <h2 class="panel-title">Stream Controls</h2>

                    <div class="control-buttons">
                        <button id="startBtn" class="btn btn-start" onclick="startBroadcast()">
                            Start Broadcast
                        </button>
                        <button id="stopBtn" class="btn btn-stop" onclick="stopBroadcast()" style="display:none;">
                            Stop Broadcast
                        </button>
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

                    <div class="source-selector">
                        <h3 class="panel-title">Quick Actions</h3>
                        <button class="btn" onclick="testClipPlayback()" style="margin-right: 0.5rem;">
                            Test Clip
                        </button>
                        <button class="btn" onclick="refreshDevices()">
                            Refresh Devices
                        </button>
                    </div>
                </div>
            </div>

            <div class="uploads-section">
                <h2 class="panel-title">Video Clips</h2>

                @if($uploads->count() > 0)
                    <div class="uploads-grid">
                        @foreach($uploads as $upload)
                            <div class="upload-card" data-upload-id="{{ $upload->id }}" data-url="/storage/{{ $upload->stored_filename ?? $upload->path }}">
                                <div class="upload-name">{{ $upload->filename }}</div>
                                <div class="upload-meta">
                                    Duration: {{ gmdate("H:i:s", $upload->duration_sec) }} |
                                    Size: {{ number_format($upload->size_bytes / 1024 / 1024, 1) }} MB
                                </div>
                                <div class="playback-progress">
                                    <div class="playback-progress-bar"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <p>No video clips available.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <div id="toast" class="toast"></div>

    <script type="module">
        import { LiveKitClient } from '/resources/js/livekit-client.js';

        // Global variables
        window.liveKitClient = new LiveKitClient();
        window.isConnected = false;
        window.isBroadcasting = false;
        window.currentSource = 'camA';
        window.selectedClipId = null;
        window.videoDevices = [];
        window.currentClipVideo = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const roomId = '{{ $room->id }}';
        const roomSlug = '{{ $room->slug }}';

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await initializeDevices();
            setupEventListeners();
            await connectToLiveKit();
        });

        async function initializeDevices() {
            try {
                // Request permissions first
                await navigator.mediaDevices.getUserMedia({ video: true, audio: true });

                // Enumerate devices
                const devices = await navigator.mediaDevices.enumerateDevices();
                window.videoDevices = devices.filter(d => d.kind === 'videoinput');

                console.log('Found video devices:', window.videoDevices);

                // Initialize camera previews
                if (window.videoDevices.length >= 1) {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: window.videoDevices[0].deviceId }
                    });
                    document.getElementById('camAPreview').srcObject = stream;
                    document.getElementById('camAPlaceholder').style.display = 'none';
                }

                if (window.videoDevices.length >= 2) {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: window.videoDevices[1].deviceId }
                    });
                    document.getElementById('camBPreview').srcObject = stream;
                    document.getElementById('camBPlaceholder').style.display = 'none';
                }
            } catch (error) {
                console.error('Failed to initialize devices:', error);
                showToast('Failed to access camera devices', 'error');
            }
        }

        async function connectToLiveKit() {
            try {
                // Get LiveKit token from backend
                const response = await fetch(`/api/rooms/${roomId}/token`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        identity: 'host-' + Date.now(),
                        role: 'publisher'
                    })
                });

                if (!response.ok) {
                    // Fallback to WebRTC mode if LiveKit is not available
                    console.log('LiveKit not configured, using WebRTC fallback');
                    return;
                }

                const { token, url } = await response.json();

                // Set up event callbacks
                window.liveKitClient.on('onConnectionStateChanged', (state) => {
                    updateConnectionStatus(state === 'connected');
                });

                window.liveKitClient.on('onTrackSubscribed', (track, publication, participant) => {
                    console.log('New track from participant:', participant.identity);
                });

                // Connect to LiveKit
                await window.liveKitClient.connect(url || 'ws://localhost:7880', token);

                window.isConnected = true;
                updateConnectionStatus(true);
                showToast('Connected to LiveKit', 'success');

            } catch (error) {
                console.error('Failed to connect to LiveKit:', error);
                // Continue without LiveKit - use WebRTC fallback
            }
        }

        window.startBroadcast = async function() {
            try {
                // Start room via API
                const response = await fetch(`/api/rooms/${roomId}/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) throw new Error('Failed to start room');

                // Publish tracks if using LiveKit
                if (window.isConnected) {
                    await window.liveKitClient.publishTracks(true, true);
                }

                window.isBroadcasting = true;
                document.getElementById('startBtn').style.display = 'none';
                document.getElementById('stopBtn').style.display = 'block';
                document.getElementById('statusText').textContent = 'live';
                document.querySelector('.status-badge').className = 'status-badge status-live';

                showToast('Broadcasting started', 'success');
            } catch (error) {
                console.error('Failed to start broadcast:', error);
                showToast('Failed to start broadcast', 'error');
            }
        }

        window.stopBroadcast = async function() {
            try {
                // Stop room via API
                const response = await fetch(`/api/rooms/${roomId}/stop`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) throw new Error('Failed to stop room');

                // Disconnect from LiveKit
                if (window.isConnected) {
                    await window.liveKitClient.disconnect();
                }

                window.isBroadcasting = false;
                document.getElementById('startBtn').style.display = 'block';
                document.getElementById('stopBtn').style.display = 'none';
                document.getElementById('statusText').textContent = 'offline';
                document.querySelector('.status-badge').className = 'status-badge status-offline';

                showToast('Broadcasting stopped', 'success');
            } catch (error) {
                console.error('Failed to stop broadcast:', error);
                showToast('Failed to stop broadcast', 'error');
            }
        }

        window.switchSource = async function(source) {
            try {
                let success = false;

                if (source === 'clip' && !window.selectedClipId) {
                    showToast('Please select a video clip first', 'error');
                    return;
                }

                // Update UI
                document.querySelectorAll('.source-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`[data-source="${source}"]`).classList.add('active');

                // Switch source in LiveKit
                if (window.isConnected && window.isBroadcasting) {
                    if (source === 'camA' || source === 'camB') {
                        const deviceIndex = source === 'camA' ? 0 : 1;
                        const deviceId = window.videoDevices[deviceIndex]?.deviceId;

                        if (deviceId) {
                            await window.liveKitClient.switchVideoSource('camera', { deviceId });
                            success = true;
                        }
                    } else if (source === 'clip') {
                        const clipCard = document.querySelector('.upload-card.selected');
                        if (clipCard) {
                            const videoUrl = clipCard.dataset.url;
                            await playClip(videoUrl);
                            success = true;
                        }
                    }
                }

                // Update program preview
                const programPreview = document.getElementById('programPreview');
                const sourcePreview = document.getElementById(source === 'camA' ? 'camAPreview' :
                                                              source === 'camB' ? 'camBPreview' : null);

                if (sourcePreview && sourcePreview.srcObject) {
                    programPreview.srcObject = sourcePreview.srcObject.clone();
                }

                // Notify backend
                await fetch(`/api/rooms/${roomId}/switch`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        source: source,
                        clip_id: source === 'clip' ? window.selectedClipId : null
                    })
                });

                window.currentSource = source;
                showToast(`Switched to ${source}`, 'success');
            } catch (error) {
                console.error('Failed to switch source:', error);
                showToast('Failed to switch source', 'error');
            }
        }

        async function playClip(videoUrl) {
            try {
                if (window.isConnected) {
                    await window.liveKitClient.playVideoClip(videoUrl);

                    // Update UI to show playing status
                    document.querySelectorAll('.upload-card').forEach(card => {
                        card.classList.remove('playing');
                    });
                    document.querySelector('.upload-card.selected')?.classList.add('playing');
                }
            } catch (error) {
                console.error('Failed to play clip:', error);
                showToast('Failed to play video clip', 'error');
            }
        }

        window.testClipPlayback = async function() {
            const selectedCard = document.querySelector('.upload-card.selected');
            if (!selectedCard) {
                showToast('Please select a video clip first', 'error');
                return;
            }

            const videoUrl = selectedCard.dataset.url;

            // Create test video element
            const testVideo = document.createElement('video');
            testVideo.src = videoUrl;
            testVideo.autoplay = true;
            testVideo.controls = true;
            testVideo.style.cssText = `
                position: fixed;
                bottom: 20px;
                left: 20px;
                width: 320px;
                height: 180px;
                z-index: 1000;
                border: 2px solid #667eea;
                border-radius: 8px;
            `;

            document.body.appendChild(testVideo);

            testVideo.play();

            // Remove after 10 seconds
            setTimeout(() => testVideo.remove(), 10000);

            showToast('Testing clip playback (10s)', 'success');
        }

        window.refreshDevices = async function() {
            await initializeDevices();
            showToast('Devices refreshed', 'success');
        }

        function setupEventListeners() {
            // Upload card selection
            document.querySelectorAll('.upload-card').forEach(card => {
                card.addEventListener('click', () => {
                    document.querySelectorAll('.upload-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    window.selectedClipId = card.dataset.uploadId;
                });
            });
        }

        function updateConnectionStatus(connected) {
            const indicator = document.getElementById('connectionIndicator');
            if (connected) {
                indicator.classList.add('connected');
            } else {
                indicator.classList.remove('connected');
            }
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `toast ${type} show`;

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>