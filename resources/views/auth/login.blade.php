<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Administrator - Radar Tulungagung Geometric Agent Radar</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('RadarTulungagung.png') }}">

    <!-- Google Fonts & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-4" style="background: linear-gradient(135deg, #002244 0%, #004B87 50%, #0073E6 100%);">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                
                <!-- Main Login Card -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <!-- Card Header / Brand -->
                    <div class="p-4 text-center text-white" style="background: var(--radar-blue-dark);">
                        <img src="{{ asset('RadarTulungagung.png')}}" alt="Radar Tulungagung" class="img-fluid" style="width: 70px; height: 70px; border-radius: 50%;">
                        <h4 class="font-heading fw-bold mb-1">ADMIN CONSOLE</h4>
                        <div class="text-white-50 font-mono" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                            JAWA POS RADAR TULUNGAGUNG
                        </div>
                        <p class="text-white-50 small mb-0 mt-1">Masuk untuk mengelola data agen spasial dan konfigurasi radar</p>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-4 p-md-5 bg-white">

                        <!-- Flash / Error Alerts -->
                        @if(session('error'))
                            <div class="alert alert-danger d-flex align-items-center gap-2 border-0 shadow-sm py-2 mb-3" role="alert">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <div class="small fw-medium">{{ session('error') }}</div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success d-flex align-items-center gap-2 border-0 shadow-sm py-2 mb-3" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                <div class="small fw-medium">{{ session('success') }}</div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login.post') }}" novalidate>
                            @csrf

                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="email" class="form-label font-heading fw-semibold small text-secondary">
                                    <i class="bi bi-envelope me-1"></i> Alamat Email
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="nama@gmail.com" 
                                       required 
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback small font-mono">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">
                                <label for="password" class="form-label font-heading fw-semibold small text-secondary">
                                    <i class="bi bi-key me-1"></i> Kata Sandi (Password)
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Masukkan kata sandi..." 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback small font-mono">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember Me Checkbox -->
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label small text-muted" for="remember">
                                        Ingat Sesi Saya
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-lg w-100 font-heading fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm py-2">
                                <i class="bi bi-box-arrow-in-right fs-5"></i>
                                <span>MASUK KE ADMIN CONSOLE</span>
                            </button>
                        </form>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- Back to Radar Map Link -->
                        <div class="text-center">
                            <a href="{{ route('radar.index') }}" class="text-decoration-none small text-secondary font-heading d-inline-flex align-items-center gap-1 hover-primary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Tampilan Peta Radar Publik
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Footer note -->
                <div class="text-center mt-3 text-white-50 small font-mono">
                    &copy; {{ date('Y') }} Radar Tulungagung • Geometric Agent Radar
                </div>

            </div>
        </div>
    </div>

</body>
</html>
