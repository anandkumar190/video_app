<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Join Room - {{ $room->title }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
        }

        .join-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .room-title {
            font-size: 1.8rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .room-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .join-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            text-align: left;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f9fafb;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .join-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .join-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .join-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .room-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin: 2rem 0;
            border-left: 4px solid #667eea;
        }

        .room-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }

        .status-live {
            background: rgba(34, 197, 94, 0.2);
            color: #059669;
        }

        .status-offline {
            background: rgba(107, 114, 128, 0.2);
            color: #6b7280;
        }

        .login-link {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            color: #059669;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }

        @media (max-width: 480px) {
            .join-container {
                padding: 2rem;
            }

            .room-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="join-container">
        <div class="logo">Live Studio</div>

        <h1 class="room-title">{{ $room->title }}</h1>
        <p class="room-subtitle">You've been invited to join this room</p>

        <div class="room-info">
            <div>Room Status:
                <span class="room-status status-{{ $room->status }}">
                    {{ $room->status }}
                </span>
            </div>
        </div>

        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <form class="join-form" id="joinForm" onsubmit="joinAsGuest(event)">
            <div class="form-group">
                <label for="guestName" class="form-label">Your Name</label>
                <input
                    type="text"
                    id="guestName"
                    name="name"
                    class="form-input"
                    placeholder="Enter your name"
                    required
                    maxlength="50"
                >
            </div>

            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="acceptTerms" required style="margin: 0;">
                    I accept the terms of service and privacy policy
                </label>
            </div>

            <button type="submit" class="join-btn" id="joinBtn">
                Join Room as Guest
            </button>
        </form>

        <div class="login-link">
            Already have an account?
            <a href="{{ route('login') }}">Sign in here</a>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showError(message) {
            const errorEl = document.getElementById('errorMessage');
            errorEl.textContent = message;
            errorEl.style.display = 'block';

            const successEl = document.getElementById('successMessage');
            successEl.style.display = 'none';
        }

        function showSuccess(message) {
            const successEl = document.getElementById('successMessage');
            successEl.textContent = message;
            successEl.style.display = 'block';

            const errorEl = document.getElementById('errorMessage');
            errorEl.style.display = 'none';
        }

        function joinAsGuest(event) {
            event.preventDefault();

            const joinBtn = document.getElementById('joinBtn');
            const guestName = document.getElementById('guestName').value;

            if (!guestName.trim()) {
                showError('Please enter your name');
                return;
            }

            joinBtn.disabled = true;
            joinBtn.textContent = 'Joining...';

            fetch(`/api/join/{{ $token }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    name: guestName.trim()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message && data.room) {
                    showSuccess('Successfully joined the room!');

                    setTimeout(() => {
                        window.location.href = `/rooms/${data.room.slug}/viewer?guest=${encodeURIComponent(data.guest_name)}&token={{ $token }}`;
                    }, 2000);
                } else {
                    showError(data.message || 'Failed to join room');
                    joinBtn.disabled = false;
                    joinBtn.textContent = 'Join Room as Guest';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to join room. Please try again.');
                joinBtn.disabled = false;
                joinBtn.textContent = 'Join Room as Guest';
            });
        }

        document.getElementById('guestName').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                joinAsGuest(e);
            }
        });
    </script>
</body>
</html>