<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invalid Invite - Live Studio</title>

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

        .error-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .error-icon {
            font-size: 4rem;
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .error-title {
            font-size: 2rem;
            color: #374151;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .divider {
            margin: 2rem 0;
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
        }

        .help-text {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 2rem;
            }

            .error-title {
                font-size: 1.7rem;
            }

            .action-buttons {
                gap: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>

        <h1 class="error-title">Invalid Invite</h1>

        <p class="error-message">
            This invite link is no longer valid or has already been used.
            Please contact the room host for a new invitation.
        </p>

        <div class="action-buttons">
            <a href="{{ route('login') }}" class="btn btn-primary">
                Sign In
            </a>

            <a href="{{ route('register') }}" class="btn btn-secondary">
                Create Account
            </a>
        </div>

        <div class="divider"></div>

        <div class="help-text">
            <strong>Need help?</strong><br>
            Invite links can expire or become invalid if:
            <ul style="text-align: left; margin-top: 0.5rem;">
                <li>The link has already been used</li>
                <li>The room has been deleted</li>
                <li>The invite was revoked by the host</li>
            </ul>
        </div>
    </div>
</body>
</html>