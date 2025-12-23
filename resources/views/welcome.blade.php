<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            :root {
                --background: #ffffff;
                --foreground: #1d1d1d;
                --muted: #888888;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --background: #1d1d1d;
                    --foreground: #e2e2e2;
                    --muted: #aaaaaa;
                }
            }

            html, body {
                min-height: 100vh;
                margin: 0;
                padding: 0;
                background-color: var(--background);
                color: var(--foreground);
                font-family: 'Figtree', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .container {
                text-align: center;
                max-width: 900px;
                padding: 2rem;
            }

            .logo {
                font-size: 4rem;
                margin-bottom: 1rem;
            }

            .header {
                font-size: 2.5rem;
                font-weight: 600;
                margin-bottom: 1rem;
            }

            .subtitle {
                font-size: 1.2rem;
                color: var(--muted);
                margin-bottom: 2rem;
            }

            .links {
                display: flex;
                gap: 1rem;
                justify-content: center;
                margin-bottom: 2rem;
            }

            .link {
                display: inline-block;
                padding: 0.75rem 1.5rem;
                background-color: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 0.5rem;
                transition: background-color 0.3s;
            }

            .link:hover {
                background-color: #2563eb;
            }

            .footer {
                margin-top: 2rem;
                color: var(--muted);
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">
                🏠
            </div>
            <h1 class="header">
                Welcome to Apartment Booking Platform
            </h1>
            <p class="subtitle">
                A Laravel-powered platform for booking apartments
            </p>
            <div class="links">
                <a href="/api/documentation" class="link">API Documentation</a>
                <a href="/admin" class="link">Admin Panel</a>
            </div>
            <div class="footer">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </div>
        </div>
    </body>
</html>