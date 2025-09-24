import './bootstrap';

async function getIceServers() {
    try {
        const response = await fetch('/api/rtc/ice-config', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const config = await response.json();
        console.log('ICE Configuration:', config);
        return config.iceServers || config;
    } catch (error) {
        console.error('Failed to fetch ICE configuration:', error);
        return [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ];
    }
}

window.getIceServers = getIceServers;
