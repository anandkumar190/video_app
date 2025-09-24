import {
    Room,
    RoomEvent,
    Track,
    LocalParticipant,
    RemoteParticipant,
    VideoPresets,
    createLocalTracks,
    LocalVideoTrack,
    LocalAudioTrack,
    RemoteTrack
} from 'livekit-client';

export class LiveKitClient {
    constructor() {
        this.room = null;
        this.localVideoTrack = null;
        this.localAudioTrack = null;
        this.currentVideoSource = null;
        this.clipVideo = null;
        this.callbacks = {
            onTrackSubscribed: null,
            onTrackUnsubscribed: null,
            onParticipantConnected: null,
            onParticipantDisconnected: null,
            onConnectionStateChanged: null,
            onMediaDevicesError: null
        };
    }

    async connect(wsUrl, token, options = {}) {
        try {
            this.room = new Room({
                adaptiveStream: options.adaptiveStream ?? true,
                dynacast: options.dynacast ?? true,
                videoCaptureDefaults: {
                    resolution: VideoPresets.h720.resolution,
                    facingMode: 'user'
                },
                publishDefaults: {
                    videoSimulcastLayers: [VideoPresets.h180, VideoPresets.h360, VideoPresets.h720],
                }
            });

            // Set up event handlers
            this.setupEventHandlers();

            // Connect to room
            await this.room.connect(wsUrl, token, {
                autoSubscribe: options.autoSubscribe ?? true,
            });

            console.log('Connected to LiveKit room:', this.room.name);
            return this.room;
        } catch (error) {
            console.error('Failed to connect to LiveKit:', error);
            throw error;
        }
    }

    setupEventHandlers() {
        if (!this.room) return;

        this.room
            .on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
                console.log('Track subscribed:', track.kind, 'from', participant.identity);
                if (this.callbacks.onTrackSubscribed) {
                    this.callbacks.onTrackSubscribed(track, publication, participant);
                }
            })
            .on(RoomEvent.TrackUnsubscribed, (track, publication, participant) => {
                console.log('Track unsubscribed:', track.kind, 'from', participant.identity);
                if (this.callbacks.onTrackUnsubscribed) {
                    this.callbacks.onTrackUnsubscribed(track, publication, participant);
                }
            })
            .on(RoomEvent.ParticipantConnected, (participant) => {
                console.log('Participant connected:', participant.identity);
                if (this.callbacks.onParticipantConnected) {
                    this.callbacks.onParticipantConnected(participant);
                }
            })
            .on(RoomEvent.ParticipantDisconnected, (participant) => {
                console.log('Participant disconnected:', participant.identity);
                if (this.callbacks.onParticipantDisconnected) {
                    this.callbacks.onParticipantDisconnected(participant);
                }
            })
            .on(RoomEvent.ConnectionStateChanged, (state) => {
                console.log('Connection state changed:', state);
                if (this.callbacks.onConnectionStateChanged) {
                    this.callbacks.onConnectionStateChanged(state);
                }
            })
            .on(RoomEvent.MediaDevicesError, (error) => {
                console.error('Media devices error:', error);
                if (this.callbacks.onMediaDevicesError) {
                    this.callbacks.onMediaDevicesError(error);
                }
            });
    }

    async publishTracks(video = true, audio = true) {
        try {
            const tracks = await createLocalTracks({
                video: video ? {
                    resolution: VideoPresets.h720.resolution,
                } : false,
                audio: audio ? {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                } : false,
            });

            for (const track of tracks) {
                await this.room.localParticipant.publishTrack(track);
                if (track.kind === Track.Kind.Video) {
                    this.localVideoTrack = track;
                } else if (track.kind === Track.Kind.Audio) {
                    this.localAudioTrack = track;
                }
            }

            console.log('Published tracks:', tracks.map(t => t.kind));
            return tracks;
        } catch (error) {
            console.error('Failed to publish tracks:', error);
            throw error;
        }
    }

    async switchVideoSource(source, options = {}) {
        if (!this.room || !this.room.localParticipant) {
            console.error('Not connected to room');
            return;
        }

        try {
            let newVideoTrack = null;

            switch (source) {
                case 'camera':
                case 'camA':
                case 'camB':
                    // Switch between cameras
                    const deviceId = options.deviceId || undefined;
                    newVideoTrack = await this.createCameraTrack(deviceId);
                    break;

                case 'screen':
                    // Screen share
                    newVideoTrack = await this.createScreenShareTrack();
                    break;

                case 'clip':
                    // Video file playback
                    if (options.videoElement) {
                        newVideoTrack = await this.createVideoFileTrack(options.videoElement);
                    }
                    break;

                default:
                    console.error('Unknown video source:', source);
                    return;
            }

            if (newVideoTrack) {
                // Unpublish current video track
                if (this.localVideoTrack) {
                    await this.room.localParticipant.unpublishTrack(this.localVideoTrack);
                    this.localVideoTrack.stop();
                }

                // Publish new video track
                await this.room.localParticipant.publishTrack(newVideoTrack);
                this.localVideoTrack = newVideoTrack;
                this.currentVideoSource = source;

                console.log('Switched video source to:', source);
            }
        } catch (error) {
            console.error('Failed to switch video source:', error);
            throw error;
        }
    }

    async createCameraTrack(deviceId) {
        const constraints = {
            video: {
                deviceId: deviceId ? { exact: deviceId } : undefined,
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: deviceId ? undefined : 'user'
            }
        };

        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        const videoTrack = stream.getVideoTracks()[0];

        return new LocalVideoTrack(videoTrack, undefined, {});
    }

    async createScreenShareTrack() {
        const stream = await navigator.mediaDevices.getDisplayMedia({
            video: {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                frameRate: { ideal: 30 }
            },
            audio: false
        });

        const videoTrack = stream.getVideoTracks()[0];
        return new LocalVideoTrack(videoTrack, undefined, {});
    }

    async createVideoFileTrack(videoElement) {
        // Use canvas and captureStream for video file playback
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        canvas.width = videoElement.videoWidth || 1280;
        canvas.height = videoElement.videoHeight || 720;

        // Start drawing video to canvas
        const drawVideo = () => {
            if (!videoElement.paused && !videoElement.ended) {
                ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                requestAnimationFrame(drawVideo);
            }
        };

        videoElement.addEventListener('play', drawVideo);
        videoElement.play();

        const stream = canvas.captureStream(30); // 30 FPS
        const videoTrack = stream.getVideoTracks()[0];

        return new LocalVideoTrack(videoTrack, undefined, {});
    }

    async replaceVideoTrack(newTrack) {
        if (!this.room || !this.localVideoTrack) {
            console.error('No local video track to replace');
            return;
        }

        try {
            const publication = this.room.localParticipant.videoTracks.values().next().value;
            if (publication) {
                await publication.track.replaceTrack(newTrack);
                console.log('Replaced video track');
            }
        } catch (error) {
            console.error('Failed to replace video track:', error);
            throw error;
        }
    }

    async playVideoClip(videoUrl, startTime = 0) {
        if (!this.clipVideo) {
            this.clipVideo = document.createElement('video');
            this.clipVideo.autoplay = true;
            this.clipVideo.muted = false;
            this.clipVideo.style.display = 'none';
            document.body.appendChild(this.clipVideo);
        }

        return new Promise((resolve, reject) => {
            this.clipVideo.src = videoUrl;
            this.clipVideo.currentTime = startTime;

            this.clipVideo.onloadedmetadata = async () => {
                try {
                    await this.switchVideoSource('clip', { videoElement: this.clipVideo });
                    resolve(this.clipVideo);
                } catch (error) {
                    reject(error);
                }
            };

            this.clipVideo.onerror = (error) => {
                reject(error);
            };

            this.clipVideo.play().catch(reject);
        });
    }

    async stopVideoClip() {
        if (this.clipVideo) {
            this.clipVideo.pause();
            this.clipVideo.src = '';
        }

        // Switch back to camera
        if (this.currentVideoSource === 'clip') {
            await this.switchVideoSource('camera');
        }
    }

    setAudioEnabled(enabled) {
        if (this.localAudioTrack) {
            this.localAudioTrack.mute = !enabled;
            console.log('Audio', enabled ? 'enabled' : 'disabled');
        }
    }

    setVideoEnabled(enabled) {
        if (this.localVideoTrack) {
            this.localVideoTrack.mute = !enabled;
            console.log('Video', enabled ? 'enabled' : 'disabled');
        }
    }

    async disconnect() {
        if (this.room) {
            await this.room.disconnect(true);
            this.room = null;
            this.localVideoTrack = null;
            this.localAudioTrack = null;
            console.log('Disconnected from LiveKit room');
        }

        if (this.clipVideo) {
            this.clipVideo.remove();
            this.clipVideo = null;
        }
    }

    getParticipants() {
        if (!this.room) return [];

        const participants = [];
        this.room.participants.forEach((participant) => {
            participants.push({
                identity: participant.identity,
                sid: participant.sid,
                isSpeaking: participant.isSpeaking,
                videoTracks: Array.from(participant.videoTracks.values()),
                audioTracks: Array.from(participant.audioTracks.values())
            });
        });

        return participants;
    }

    attachTrackToElement(track, element) {
        if (track.kind === Track.Kind.Video || track.kind === Track.Kind.Audio) {
            track.attach(element);
        }
    }

    detachTrackFromElement(track, element) {
        if (track.kind === Track.Kind.Video || track.kind === Track.Kind.Audio) {
            track.detach(element);
        }
    }

    on(event, callback) {
        if (this.callbacks.hasOwnProperty(event)) {
            this.callbacks[event] = callback;
        }
    }
}

export default LiveKitClient;