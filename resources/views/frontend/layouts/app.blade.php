
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'হকার ব্যবস্থাপনা সিস্টেম')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        @font-face {
            font-family: 'Kalpurush';
            src: url('/fonts/kalpurush.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --primary: #075e54;
            --primary-dark: #064b43;
            --secondary: #eaf5f2;
            --text: #263238;
        }

        body {
            font-family: 'Kalpurush', sans-serif;
            color: var(--text);
            background: #f7f9fc;
            font-size: 18px;
        }

        .navbar {
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            background: var(--secondary);
            color: var(--primary);
            border-radius: 12px;
            font-size: 25px;
        }

        .navbar-brand {
            color: var(--primary);
            font-weight: bold;
        }

        .nav-link {
            color: #344054;
            padding: 10px 16px !important;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary);
        }

        .btn-primary-custom {
            background: var(--primary);
            color: #fff;
            border: 1px solid var(--primary);
            padding: 11px 24px;
            border-radius: 8px;
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            color: #fff;
        }

        .hero {
            background: linear-gradient(120deg, #075e54, #0b8172);
            color: #fff;
            padding: 85px 0;
            overflow: hidden;
        }

        .hero h1 {
            font-size: clamp(30px, 5vw, 48px);
            font-weight: bold;
            line-height: 1.5;
        }

        .hero p {
            font-size: 20px;
            line-height: 1.8;
            color: #e2f5f1;
        }

        .hero-card {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 18px;
            padding: 28px;
        }

        .stat-card,
        .content-card {
            background: #fff;
            border: 1px solid #edf0f3;
            border-radius: 14px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0,0,0,.025);
        }

        .stat-number {
            color: var(--primary);
            font-size: 32px;
            font-weight: bold;
        }

        .section-title {
            color: var(--primary);
            font-weight: bold;
        }

        .notice-item {
            padding: 16px 0;
            border-bottom: 1px solid #edf0f3;
        }

        .notice-item:last-child {
            border-bottom: 0;
        }

        footer {
            background: #102e2a;
            color: #d5e5e2;
            padding: 35px 0;
        }

        footer a {
            color: #fff;
            text-decoration: none;
        }

        @media (max-width: 767px) {
            .hero {
                padding: 55px 0;
                text-align: center;
            }

            .hero-card {
                margin-top: 30px;
            }

            .stat-card {
                padding: 18px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center gap-2"
               href="{{ route('home') }}">
                <span class="brand-icon"><img src="{{asset('images/logo.png')}}" alt="" srcset="" height="48px"></span>
                <span>
                    <span class="d-block">হকার ব্যবস্থাপনা সিস্টেম</span>
                    <small class="text-secondary fw-normal">
                        আবেদনকারী সেবা পোর্টাল
                    </small>
                </span>
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar"
                    aria-expanded="false"
                    aria-label="মেনু খুলুন">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                           href="{{ route('home') }}">
                            হোম
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('frontend.rules') ? 'active' : '' }}"
                           href="{{ route('frontend.rules') }}">
                            আবেদনের নিয়মাবলি
                        </a>
                    </li>
                </ul>

                <a href="{{ route('home') }}#new-application"
                   class="btn btn-primary-custom ms-lg-3 mt-2 mt-lg-0">
                    নতুন আবেদন
                </a>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <div class="row gy-3">
                <div class="col-md-8">
                    <h5>হকার ব্যবস্থাপনা সিস্টেম</h5>
                    <p class="mb-0">
                        আবেদন, বিটি বরাদ্দ এবং দৈনিক ভাড়া ব্যবস্থাপনার
                        অনলাইন সেবা পোর্টাল।
                    </p>
                </div>

                <div class="col-md-4 text-md-end">
                    <p class="mb-1">সর্বস্বত্ব সংরক্ষিত</p>
                    <small>© {{ date('Y') }} Plot Management System</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
