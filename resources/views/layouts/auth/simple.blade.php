<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — SalesSaaS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --p-primary: #198754;
            --p-bg: #f5f9f5;
            --p-success: #198754;
            --p-danger: #ea5455;
        }

        body {
            font-family: 'Public Sans', sans-serif;
            background: #f5f9f5;
            /* Soft green background */
            background-image: radial-gradient(#22c55e33 0.5px, transparent 0.5px);
            background-size: 20px 20px;
        }

        .auth-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 6px 0 rgba(25, 135, 84, 0.12);
        }

        .btn-primary {
            background-color: var(--p-primary);
            border-color: var(--p-primary);
        }

        .btn-primary:hover {
            background-color: #157347;
            border-color: #157347;
        }

        .text-primary {
            color: var(--p-primary) !important;
        }

        .auth-logo-bg {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
    </style>
</head>

<body class="antialiased">
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center py-5">
            <!-- Language Switcher for Auth Pages -->
            <div class="position-absolute top-0 end-0 p-3 d-flex gap-1">
                @foreach(['fr' => ['🇫🇷', 'FR'], 'en' => ['🇬🇧', 'EN'], 'sw' => ['🇹🇿', 'SW']] as $loc => $info)
                    <a href="{{ route('locale.switch', $loc) }}"
                        class="btn btn-sm {{ app()->getLocale() === $loc ? 'btn-primary' : 'btn-outline-secondary' }}"
                        style="font-size:.68rem;padding:.2rem .45rem;">
                        <span class="d-inline">{{ $info[0] }}</span>
                        <span class="d-none d-sm-inline ms-1">{{ $info[1] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                <div class="text-center mb-4">
                    <a href="{{ route('home') }}" class="d-inline-block text-decoration-none">
                        <div class="auth-logo-bg p-3 rounded-circle d-inline-block mb-3 shadow-sm">
                            <x-app-logo-icon class="m-0" style="width: 48px; height: 48px; fill: currentColor;" />
                        </div>
                        <h4 class="fw-bold text-dark mb-0 ls-tight">{{ config('app.name') }}</h4>
                        <small class="text-muted text-uppercase tracking-widest"
                            style="font-size: 0.65rem; letter-spacing: 2px;">{{ __('Gestion Commerciale') }}</small>
                    </a>
                </div>

                <div class="card auth-card overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        {{ $slot }}
                    </div>
                </div>

                <div class="text-center mt-4 pt-2">
                    <p class="text-muted" style="font-size: 0.8rem;">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('Tous droits réservés.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>