<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Register</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-title {
            font-size: 2rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .register-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
            flex: 1;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .role-option {
            flex: 1;
            position: relative;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-label {
            display: block;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .role-option input[type="radio"]:checked + .role-label {
            border-color: #667eea;
            background: #f8faff;
            color: #667eea;
            font-weight: bold;
        }

        .role-title {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .role-description {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .register-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1 class="register-title">Create Account</h1>
            <p class="register-subtitle">Join Live Studio to start streaming</p>
        </div>

        @if ($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                @foreach ($errors->all() as $error)
                    <p class="form-error" style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" name="name" id="name" class="form-input"
                       value="{{ old('name') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-input"
                       value="{{ old('email') }}" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Choose Your Role</label>
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" name="role" id="role_host" value="host" {{ old('role') == 'host' ? 'checked' : '' }} required>
                        <label for="role_host" class="role-label">
                            <div class="role-title">Host</div>
                            <div class="role-description">Create and manage streams</div>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="role" id="role_guest" value="guest" {{ old('role') == 'guest' || !old('role') ? 'checked' : '' }} required>
                        <label for="role_guest" class="role-label">
                            <div class="role-title">Guest</div>
                            <div class="role-description">Join streams as participant</div>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="register-btn">
                Create Account
            </button>
        </form>

        <div class="register-links">
            <p>Already have an account?
                <a href="{{ route('login') }}" class="register-link">Sign in here</a>
            </p>
        </div>
    </div>
</body>
</html>