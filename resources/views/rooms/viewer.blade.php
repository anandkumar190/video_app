<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Viewing - {{ $room->title }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #111827;
            color: white;
            overflow: hidden;
        }

        .viewer-container {
            height: 100vh;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 1rem;
            padding: 1rem;
        }

        .main-view {
            background: #1f2937;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 3px solid #374151;
        }

        .main-view.program {
            border-color: #ef4444;
        }

        .main-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            font-size: 2rem;
            font-weight: bold;
        }

        .main-label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .guest-controls {
            background: #1f2937;
            border-radius: 12px;
            padding: 1.5rem;
            border: 2px solid #374151;
        }

        .control-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #f3f4f6;
            border-bottom: 2px solid #374151;
            padding-bottom: 0.5rem;
        }

        .guest-info {
            margin-bottom: 1.5rem;
        }

        .guest-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 0.5rem;
        }

        .guest-status {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .media-controls {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .control-btn {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid #374151;
            background: #374151;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .control-btn.active {
            background: #10b981;
            border-color: #10b981;
        }

        .control-btn.muted {
            background: #ef4444;
            border-color: #ef4444;
        }

        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .participants-grid {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            background: #1f2937;
            border-radius: 12px;
            padding: 1rem;
            border: 2px solid #374151;
            overflow-y: auto;
        }

        .participant-tile {
            background: #111827;
            border-radius: 8px;
            position: relative;
            height: 120px;
            overflow: hidden;
            border: 2px solid #374151;
            transition: border-color 0.3s;
        }

        .participant-tile.self {
            border-color: #10b981;
        }

        .participant-tile video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .participant-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #9ca3af;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .participant-label {
            position: absolute;
            bottom: 0.5rem;
            left: 0.5rem;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .participant-status {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.2rem;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #6b7280;
        }

        .status-indicator.mic-on {
            background: #10b981;
        }

        .status-indicator.cam-on {
            background: #3b82f6;
        }

        .leave-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .leave-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        .connection-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .status-connecting {
            color: #fbbf24;
        }

        .status-connected {
            color: #10b981;
        }

        .status-disconnected {
            color: #ef4444;
        }

        @media (max-width: 768px) {
            .viewer-container {
                grid-template-columns: 1fr;
                grid-template-rows: 1fr 200px;
                gap: 0.5rem;
                padding: 0.5rem;
            }

            .main-label {
                font-size: 1rem;
                padding: 0.4rem 0.8rem;
            }

            .participants-grid {
                flex-direction: row;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .participant-tile {
                min-width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <div class="main-view program">
            <div class="main-label">Program Stream</div>
            <video id="programVideo" class="main-video" autoplay muted playsinline></video>
            <div id="programPlaceholder" class="main-placeholder">Waiting for Program Stream...</div>
            <div class="connection-status status-connecting" id="connectionStatus">Connecting...</div>
        </div>

        <div class="sidebar">
            <div class="guest-controls">
                <h3 class="control-title">Guest Controls</h3>

                <div class="guest-info">
                    <div class="guest-name" id="guestName">{{ $guestName ?? 'Guest' }}</div>
                    <div class="guest-status" id="guestStatus">Viewer</div>
                </div>

                <div class="media-controls">
                    <button id="micBtn" class="control-btn" onclick="toggleMic()">
                        <span id="micIcon">🎤</span>
                        <span id="micText">Mic Off</span>
                    </button>
                    <button id="camBtn" class="control-btn" onclick="toggleCam()">
                        <span id="camIcon">📹</span>
                        <span id="camText">Cam Off</span>
                    </button>
                </div>

                <button class="leave-btn" onclick="leaveRoom()">
                    Leave Room
                </button>
            </div>

            <div class="participants-grid">
                <h4 class="control-title">Participants</h4>
                <div id="participantsList"></div>
            </div>
        </div>
    </div>

    <script>
        let localStream = null;
        let peerConnection = null;
        let programStream = null;
        let participants = new Map();
        let micEnabled = false;
        let camEnabled = false;
        let roomToken = null;
        let ws = null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const roomSlug = '{{ $room->slug }}';
        const guestName = '{{ $guestName ?? "Guest" }}';

        document.addEventListener('DOMContentLoaded', async () => {
            await initializeConnection();
        });

        async function initializeConnection() {
            try {
                updateConnectionStatus('connecting', 'Connecting...');

                // Get ICE configuration
                const iceConfig = await getIceServers();
                console.log('ICE Configuration:', iceConfig);

                // Get room token for guest access
                roomToken = await getRoomToken();
                console.log('Room token obtained');

                // Initialize WebRTC peer connection
                await initializePeerConnection(iceConfig);

                // Connect to signaling server (WebSocket)
                await connectToSignaling();

                updateConnectionStatus('connected', 'Connected');
            } catch (error) {
                console.error('Connection initialization failed:', error);
                updateConnectionStatus('disconnected', 'Connection Failed');
            }
        }

        async function getIceServers() {
            const response = await fetch('/api/rtc/ice-config', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to get ICE configuration');
            }

            const config = await response.json();
            return config.iceServers || [{ urls: 'stun:stun.l.google.com:19302' }];
        }

        async function getRoomToken() {
            // For guests, we might need a different token endpoint or pass guest info
            // This is a placeholder - adjust based on your auth system
            return 'guest-token-' + Date.now();
        }

        async function initializePeerConnection(iceServers) {
            peerConnection = new RTCPeerConnection({
                iceServers: iceServers
            });

            // Handle incoming streams
            peerConnection.ontrack = (event) => {
                console.log('Received track:', event.track.kind);

                if (event.track.kind === 'video') {
                    // Check if this is the program stream based on track label or metadata
                    const [stream] = event.streams;
                    if (stream.id.includes('program') || event.track.label.includes('program')) {
                        displayProgramStream(stream);
                    } else {
                        // Participant stream
                        displayParticipantStream(stream, event.track.label);
                    }
                }
            };

            // Handle ICE candidates
            peerConnection.onicecandidate = (event) => {
                if (event.candidate && ws && ws.readyState === WebSocket.OPEN) {
                    ws.send(JSON.stringify({
                        type: 'ice-candidate',
                        candidate: event.candidate
                    }));
                }
            };

            console.log('Peer connection initialized');
        }

        async function connectToSignaling() {
            const wsUrl = 'ws://localhost:8080/ws'; // Adjust based on your WebSocket server
            ws = new WebSocket(wsUrl);

            ws.onopen = () => {
                console.log('WebSocket connected');

                // Join room as subscriber
                ws.send(JSON.stringify({
                    type: 'join',
                    room: roomSlug,
                    role: 'subscriber',
                    name: guestName,
                    token: roomToken
                }));
            };

            ws.onmessage = async (event) => {
                const message = JSON.parse(event.data);
                await handleSignalingMessage(message);
            };

            ws.onclose = () => {
                console.log('WebSocket disconnected');
                updateConnectionStatus('disconnected', 'Disconnected');
            };

            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                updateConnectionStatus('disconnected', 'Connection Error');
            };
        }

        async function handleSignalingMessage(message) {
            switch (message.type) {
                case 'offer':
                    await peerConnection.setRemoteDescription(message.offer);
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);

                    ws.send(JSON.stringify({
                        type: 'answer',
                        answer: answer
                    }));
                    break;

                case 'ice-candidate':
                    if (message.candidate) {
                        await peerConnection.addIceCandidate(message.candidate);
                    }
                    break;

                case 'participant-joined':
                    addParticipant(message.participant);
                    break;

                case 'participant-left':
                    removeParticipant(message.participantId);
                    break;

                case 'participants-list':
                    updateParticipantsList(message.participants);
                    break;
            }
        }

        function displayProgramStream(stream) {
            const programVideo = document.getElementById('programVideo');
            const programPlaceholder = document.getElementById('programPlaceholder');

            programVideo.srcObject = stream;
            programVideo.style.display = 'block';
            programPlaceholder.style.display = 'none';

            console.log('Program stream displayed');
        }

        function displayParticipantStream(stream, participantId) {
            const participant = participants.get(participantId);
            if (participant) {
                participant.stream = stream;
                updateParticipantTile(participantId);
            }
        }

        function addParticipant(participant) {
            participants.set(participant.id, participant);
            updateParticipantsList();
        }

        function removeParticipant(participantId) {
            participants.delete(participantId);
            updateParticipantsList();
        }

        function updateParticipantsList(participantsList = null) {
            const container = document.getElementById('participantsList');
            container.innerHTML = '';

            // Add self first
            const selfTile = createParticipantTile({
                id: 'self',
                name: guestName,
                isSelf: true,
                micEnabled: micEnabled,
                camEnabled: camEnabled,
                stream: localStream
            });
            container.appendChild(selfTile);

            // Add other participants
            const participantsToShow = participantsList || Array.from(participants.values());
            participantsToShow.forEach(participant => {
                if (participant.id !== 'self') {
                    const tile = createParticipantTile(participant);
                    container.appendChild(tile);
                }
            });
        }

        function createParticipantTile(participant) {
            const tile = document.createElement('div');
            tile.className = `participant-tile ${participant.isSelf ? 'self' : ''}`;
            tile.id = `participant-${participant.id}`;

            const video = document.createElement('video');
            video.className = 'participant-video';
            video.autoplay = true;
            video.muted = participant.isSelf;
            video.playsInline = true;

            const placeholder = document.createElement('div');
            placeholder.className = 'participant-placeholder';
            placeholder.textContent = participant.name;

            const label = document.createElement('div');
            label.className = 'participant-label';
            label.textContent = participant.name;

            const statusDiv = document.createElement('div');
            statusDiv.className = 'participant-status';

            const micIndicator = document.createElement('div');
            micIndicator.className = `status-indicator ${participant.micEnabled ? 'mic-on' : ''}`;

            const camIndicator = document.createElement('div');
            camIndicator.className = `status-indicator ${participant.camEnabled ? 'cam-on' : ''}`;

            statusDiv.appendChild(micIndicator);
            statusDiv.appendChild(camIndicator);

            tile.appendChild(video);
            tile.appendChild(placeholder);
            tile.appendChild(label);
            tile.appendChild(statusDiv);

            if (participant.stream) {
                video.srcObject = participant.stream;
                video.style.display = 'block';
                placeholder.style.display = 'none';
            }

            return tile;
        }

        async function toggleMic() {
            try {
                if (!micEnabled) {
                    if (!localStream) {
                        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                    }

                    // Add audio track to peer connection
                    const audioTrack = localStream.getAudioTracks()[0];
                    if (audioTrack && peerConnection) {
                        peerConnection.addTrack(audioTrack, localStream);
                    }
                }

                micEnabled = !micEnabled;

                if (localStream) {
                    localStream.getAudioTracks().forEach(track => {
                        track.enabled = micEnabled;
                    });
                }

                updateMicButton();
                notifyMediaChange();
            } catch (error) {
                console.error('Error toggling microphone:', error);
            }
        }

        async function toggleCam() {
            try {
                if (!camEnabled) {
                    if (!localStream || localStream.getVideoTracks().length === 0) {
                        const videoStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });

                        if (!localStream) {
                            localStream = videoStream;
                        } else {
                            videoStream.getVideoTracks().forEach(track => {
                                localStream.addTrack(track);
                            });
                        }
                    }

                    // Add video track to peer connection
                    const videoTrack = localStream.getVideoTracks()[0];
                    if (videoTrack && peerConnection) {
                        peerConnection.addTrack(videoTrack, localStream);
                    }
                }

                camEnabled = !camEnabled;

                if (localStream) {
                    localStream.getVideoTracks().forEach(track => {
                        track.enabled = camEnabled;
                    });
                }

                updateCamButton();
                updateSelfTile();
                notifyMediaChange();
            } catch (error) {
                console.error('Error toggling camera:', error);
            }
        }

        function updateMicButton() {
            const micBtn = document.getElementById('micBtn');
            const micIcon = document.getElementById('micIcon');
            const micText = document.getElementById('micText');

            if (micEnabled) {
                micBtn.classList.add('active');
                micBtn.classList.remove('muted');
                micIcon.textContent = '🎤';
                micText.textContent = 'Mic On';
            } else {
                micBtn.classList.remove('active');
                micBtn.classList.add('muted');
                micIcon.textContent = '🚫';
                micText.textContent = 'Mic Off';
            }
        }

        function updateCamButton() {
            const camBtn = document.getElementById('camBtn');
            const camIcon = document.getElementById('camIcon');
            const camText = document.getElementById('camText');

            if (camEnabled) {
                camBtn.classList.add('active');
                camIcon.textContent = '📹';
                camText.textContent = 'Cam On';
            } else {
                camBtn.classList.remove('active');
                camIcon.textContent = '📹❌';
                camText.textContent = 'Cam Off';
            }
        }

        function updateSelfTile() {
            const selfTile = document.getElementById('participant-self');
            if (selfTile) {
                const video = selfTile.querySelector('video');
                const placeholder = selfTile.querySelector('.participant-placeholder');

                if (localStream && camEnabled) {
                    video.srcObject = localStream;
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                } else {
                    video.style.display = 'none';
                    placeholder.style.display = 'flex';
                }

                // Update status indicators
                const micIndicator = selfTile.querySelector('.status-indicator:first-child');
                const camIndicator = selfTile.querySelector('.status-indicator:last-child');

                micIndicator.className = `status-indicator ${micEnabled ? 'mic-on' : ''}`;
                camIndicator.className = `status-indicator ${camEnabled ? 'cam-on' : ''}`;
            }
        }

        function notifyMediaChange() {
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'media-change',
                    micEnabled: micEnabled,
                    camEnabled: camEnabled
                }));
            }
        }

        function updateConnectionStatus(status, text) {
            const statusEl = document.getElementById('connectionStatus');
            statusEl.className = `connection-status status-${status}`;
            statusEl.textContent = text;
        }

        function leaveRoom() {
            if (confirm('Are you sure you want to leave the room?')) {
                // Clean up connections
                if (localStream) {
                    localStream.getTracks().forEach(track => track.stop());
                }

                if (peerConnection) {
                    peerConnection.close();
                }

                if (ws) {
                    ws.close();
                }

                // Redirect back to join page
                window.location.href = '/join/{{ $token }}';
            }
        }

        // Handle browser close/refresh
        window.addEventListener('beforeunload', () => {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            if (ws) {
                ws.close();
            }
        });
    </script>
</body>
</html>