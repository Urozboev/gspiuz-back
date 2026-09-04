<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="/assets/img/gspi-logo.png" type="image/png" />

    <!-- Map CSS -->
    <link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.css" />

    <!-- Libs CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/libs.bundle.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.bundle.css') }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <!-- Title -->
    <title>Tizimga kirish | {{ data_get($site_info, 'title.uz') ?? 'Guliston davlat pedagogika instituti' }}</title>
</head>

<body class="d-flex align-items-center bg-auth border-top border-top-2 border-primary">

    <!-- CONTENT
    ================================================== -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-5 col-xl-4 my-5">

                <!-- Heading -->
                <h1 class="display-4 text-center mb-3">
                    Tizimga kirish
                </h1>

                <!-- Subheading -->
                <!-- <p class="text-muted text-center mb-5">
            Free access to our dashboard.
          </p> -->

                <!-- Form -->
                <form action="{{ route('login') }}" method="post">
                    @csrf
                    <!-- Email address -->
                    <div class="form-group">

                        <!-- Label -->
                        <label class="form-label">
                            Foydalanuvchi nomi
                        </label>

                        <!-- Input -->
                        <input name="username" type="text" class="form-control @error('username') is-invalid @enderror" placeholder="Foydalanuvchi nomini kiriting" autofocus>
                        @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <div class="row">
                            <div class="col">

                                <!-- Label -->
                                <label class="form-label">
                                    Parol
                                </label>

                            </div>
                        </div>

                        <!-- Input group -->
                        <div class="input-group input-group-merge">

                            <!-- Input -->
                            <input name="password" class="form-control @error('password') is-invalid @enderror" type="password" placeholder="Parolni kiriting">
                            @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                            <!-- Icon -->
                            <!-- <span class="input-group-text">
                  <i class="fe fe-eye"></i>
                </span> -->

                        </div>
                    </div>

                    <!-- Submit -->
                    <button class="btn btn-lg w-100 btn-primary mb-3">
                        Kirish
                    </button>

                </form>

            </div>
        </div> <!-- / .row -->
    </div> <!-- / .container -->

    <!-- JAVASCRIPT -->
    <!-- Map JS -->
    <script src='https://api.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.js'></script>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/js/vendor.bundle.js') }}"></script>

    <!-- Theme JS -->
    <script src="{{ asset('assets/js/theme.bundle.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    @if (session()->has('success') && session('success') == false)
    <script type="text/javascript">
        const notyf = new Notyf({
            position: {
                x: 'right',
                y: 'top',
            },
            types: [{
                type: 'error',
                background: '#b82c46',
                icon: {
                    className: 'fe fe-x',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: false
            }]
        });
        notyf.open({
            type: 'error',
            message: <?= json_encode(session('message')) ?>
        });
    </script>
    @endif

    @if (session()->has('success') && session('success') == true)
    <script type="text/javascript">
        const notyf = new Notyf({
            position: {
                x: 'right',
                y: 'top',
            },
            types: [{
                type: 'success',
                background: '#00ae65',
                icon: {
                    className: 'fe fe-check-circle',
                    tagName: 'span',
                    color: '#fff'
                },
                dismissible: false
            }]
        });
        notyf.open({
            type: 'success',
            message: <?= json_encode(session('message')) ?>
        });
    </script>
    @endif

</body>

</html>