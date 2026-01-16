<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Customs CRM</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                min-height: 100vh;
                color: #fff;
            }
            header {
                padding: 2rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            nav {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .logo {
                font-size: 1.5rem;
                font-weight: 700;
                letter-spacing: -0.5px;
            }
            .nav-links {
                display: flex;
                gap: 2rem;
                list-style: none;
            }
            .nav-links a {
                text-decoration: none;
                color: inherit;
                transition: opacity 0.2s;
            }
            .nav-links a:hover {
                opacity: 0.7;
            }
            main {
                max-width: 1200px;
                margin: 0 auto;
                padding: 4rem 2rem;
                text-align: center;
            }
            .hero h1 {
                font-size: 3rem;
                margin-bottom: 1rem;
                font-weight: 700;
            }
            .hero p {
                font-size: 1.25rem;
                opacity: 0.8;
                margin-bottom: 2rem;
            }
            .cta-buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
            }
            .btn {
                padding: 0.75rem 2rem;
                border: none;
                border-radius: 0.375rem;
                cursor: pointer;
                font-size: 1rem;
                text-decoration: none;
                transition: all 0.3s;
                display: inline-block;
            }
            .btn-primary {
                background: #3b82f6;
                color: white;
            }
            .btn-primary:hover {
                background: #2563eb;
            }
            .btn-secondary {
                background: transparent;
                color: white;
                border: 1px solid white;
            }
            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
            }
        </style>
    </head>
    <body>
        <header>
            <nav>
                <div class="logo">Customs CRM</div>
                <ul class="nav-links">
                    @auth
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; font: inherit;">Logout</button>
                            </form>
                        </li>
                    @endauth
                    @guest
                        <li><a href="{{ route('login') }}">Login</a></li>
                        <li><a href="{{ route('register') }}">Register</a></li>
                    @endguest
                </ul>
            </nav>
        </header>

        <main>
            <div class="hero">
                <h1>Welcome to Customs CRM</h1>
                <p>Streamline your customs operations and case management</p>
                @guest
                    <div class="cta-buttons">
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary">Create Account</a>
                    </div>
                @endauth
            </div>
        </main>
    </body>
</html>
