<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Dashboard</title>

    <!-- Styles -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            text-transform: capitalize;
        }
        .role-badge.host {
            background: rgba(34, 197, 94, 0.8);
        }
        .role-badge.guest {
            background: rgba(59, 130, 246, 0.8);
        }
        .main-content {
            padding: 2rem 0;
        }
        .welcome-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .welcome-title {
            font-size: 2rem;
            color: #374151;
            margin-bottom: 1rem;
        }
        .welcome-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #667eea;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #374151;
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .logout-form {
            margin: 0;
        }
        .logout-btn {
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: rgba(239, 68, 68, 1);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    {{ config('app.name', 'Live Studio') }}
                </div>
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <span class="role-badge {{ Auth::user()->role }}">
                        {{ Auth::user()->role }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-btn">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="welcome-card">
                <h1 class="welcome-title">
                    Welcome back, {{ Auth::user()->name }}!
                </h1>
                <p class="welcome-subtitle">
                    You're logged in as a <strong>{{ Auth::user()->role }}</strong>.
                    @if(Auth::user()->role === 'host')
                        You can create rooms, manage streams, and invite guests.
                    @else
                        You can join rooms when invited by hosts.
                    @endif
                </p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Live Rooms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Streams</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Scheduled Events</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ Auth::user()->role === 'host' ? 'Host' : 'Guest' }}</div>
                    <div class="stat-label">Your Role</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>