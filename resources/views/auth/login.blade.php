<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Login - {{ mySetting()->name_app != '' ? mySetting()->name_app : env('APP_NAME') }}</title>
  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{ asset('template/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('template/node_modules/@fortawesome/fontawesome-free/css/all.css') }}">
  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('template/assets/css/components.css') }}">
  <link rel="icon" type="image/x-icon" href="{{ mySetting()->favicon_app != '' ? asset('img/app/'.mySetting()->favicon_app) : asset('template/assets/img/logo.png') }}">
  <style>
    .custom-bg {
      background-image: url("{{ asset('asset/tmp/bg.png') }}");
      height: 100%;
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
</head>

<body class="custom-bg">
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="login-brand">
              <img src="{{ mySetting()->logo_app != '' ? asset('img/app/'.mySetting()->logo_app) : asset('template/assets/img/logo.png') }}" alt="logo" width="250">
            </div>

            <div class="card card-primary">
              <div class="card-header d-block text-center">
                <h4>Login Admin - {{ mySetting()->name_app != '' ? mySetting()->name_app : env('APP_NAME') }}</h4>
              </div>

              <div class="card-body">
                @if (session()->get('error'))
                <div class="alert alert-danger alert-dismissible show fade">
                  <div class="alert-body">
                    <button class="close" data-dismiss="alert">
                      <span>×</span>
                    </button>
                    {{ session()->get('error') }}
                  </div>
                </div>
                @endif
                <form method="POST" action="{{ url('login-process') }}" class="needs-validation" novalidate="">
                  @csrf
                  <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" type="username" class="form-control" name="username" tabindex="1" required autofocus>
                    <div class="invalid-feedback">
                      Masukkan username
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="d-block">
                      <label for="password" class="control-label">Password</label>
                    </div>
                    <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
                    <div class="invalid-feedback">
                      Masukkan password
                    </div>
                  </div>
                  <div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">Login</button>
                  </div>
                </form>

              </div>
            </div>

            <div class="mt-5 text-center text-white">
              Don't have an invitation? <a href="{{ url('register') }}" target="_blank" class="text-warning">Create One</a>
            </div>

            <div class="simple-footer text-muted">
              Copyright &copy; 2024 - BenDev
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- General JS Scripts -->
  <script src="{{ asset('template/node_modules/jquery/dist/jquery.min.js') }}"></script>
  <script src="{{ asset('template/node_modules/popper.js/dist/umd/popper.min.js') }}"></script>
  <script src="{{ asset('template/node_modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('template/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
  <script src="{{ asset('template/assets/js/stisla.js') }}"></script>
  <!-- Template JS File -->
  <script src="{{ asset('template/assets/js/scripts.js') }}"></script>

</body>

</html>