<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ mySetting()->name_app != '' ? mySetting()->name_app : env('APP_NAME') }}</title>
    <meta name="description" content="{{ mySetting()->description_app != '' ? mySetting()->description_app : 'Aplikasi E-Invitation' }}">
    <meta name="keywords" content="{{ mySetting()->keywords_app != '' ? mySetting()->keywords_app : 'e-invitation, aplikasi undangan, undangan digital, registrasi event' }}">
    <meta name="author" content="{{ mySetting()->author_app != '' ? mySetting()->author_app : 'BenDev' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">




    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('template/node_modules/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/node_modules/@fortawesome/fontawesome-free/css/all.css') }}">
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
    <link rel="stylesheet" href="{{ asset('template/node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <link rel="stylesheet" href="{{ asset('template/node_modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/node_modules/izitoast/dist/css/iziToast.min.css') }}">
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('template/assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/custom.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ mySetting()->favicon_app != '' ? asset('img/app/'.mySetting()->favicon_app) : asset('template/assets/img/logo.png') }}">
</head>

<body>
    <script src="{{ asset('template/node_modules/jquery/dist/jquery.min.js') }}"></script>

    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                </form>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="{{ url('/register') }}" target="_blank" class="nav-link nav-link-lg nav-link-user">
                            <img alt="image" src="{{ mySetting()->favicon_app != '' ? asset('img/app/'.mySetting()->favicon_app) : asset('template/assets/img/logo.png') }}" class="rounded-circle mr-1">
                            <div class="d-sm-none d-lg-inline-block">Link Pendaftaran</div>
                        </a>

                    </li>
                </ul>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="{{ asset('template/assets/img/avatar/avatar-4.png') }}" class="rounded-circle mr-1">
                            <div class="d-sm-none d-lg-inline-block">{{ auth()->user()->name }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="{{ url('user-profile') }}" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> Profile
                            </a>
                            {{-- <a href="{{ url('change-password') }}" class="dropdown-item has-icon"><i class="fas fa-cog"></i> Ubah Password</a> --}}
                            <div class="dropdown-divider"></div>
                            <a href="{{ url('logout') }}" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand" style="padding: 20px; 10px; 10px; 10px; margin-bottom: 40px;">
                        <a href="{{ url('/dashboard') }}">
                            <img src="{{ mySetting()->logo_app != '' ? asset('img/app/'.mySetting()->logo_app) : asset('template/assets/img/logo.png') }}" alt="logo" width="200">
                        </a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ url('/dashboard') }}"><i class="fa fa-paper-plane"></i></a>
                    </div>
                    <ul class="sidebar-menu">

                        <li class="menu-header">Dashboard</li>
                        <li class="{{ request()->segment(1) == 'dashboard' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('/dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="menu-header">Tamu</li>
                        @if (auth()->user()->role == 1)
                        <li class="{{ request()->segment(1) == 'guest' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('guest') }}">
                                <i class="fas fa-users"></i> <span>Tamu</span>
                            </a>
                        </li>
                        <li class="{{ request()->segment(1) == 'invite' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('invite') }}">
                                <i class="fas fa-envelope"></i> <span>Undangan</span>
                            </a>
                        </li>
                        @endif
                        <li class="{{ request()->segment(1) == 'arrival-log' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('arrival-log') }}">
                                <i class="fas fa-list-ul"></i> <span>Log Kedatangan</span>
                            </a>
                        </li>
                        <li class="menu-header">Setting</li>
                        @if (auth()->user()->role == 1)
                        <li class="{{ request()->segment(1) == 'event' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('event') }}">
                                <i class="fas fa-calendar-check"></i> <span>Acara</span>
                            </a>
                        </li>
                        <li class="{{ request()->segment(1) == 'setting' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('setting') }}">
                                <i class="fas fa-cog"></i> <span>Aplikasi</span>
                            </a>
                        </li>
                        <li class="{{ request()->segment(1) == 'user' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('user') }}">
                                <i class="fas fa-user"></i> <span>User</span>
                            </a>
                        </li>
                        @endif
                        <li class="menu-header">Scan</li>
                        {{-- <li class="nav-item dropdown">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-qrcode"></i><span>Proses Scan</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" target="_blank" href="{{ url('scan/in') }}">Scan In</a></li>
                        <li><a class="nav-link" target="_blank" href="{{ url('scan/out') }}">Scan Out</a></li>
                    </ul>
                    </li> --}}
                    <li>
                        <a class="nav-link" href="{{ url('scan/scanRfid') }}" target="_blank">
                            <i class="fas fa-id-card-alt"></i> <span>Check-In RFID</span>
                        </a>
                    </li>

                    <li>
                        <a class="nav-link" href="{{ url('scan/in') }}" target="_blank">
                            <i class="fas fa-qrcode"></i> <span>Check-In QR</span>
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="{{ url('scan/out') }}" target="_blank">
                            <i class="fas fa-qrcode"></i> <span>Souvenir</span>
                        </a>
                    </li>
                    <li class="{{ request()->segment(1) == 'arrived-manually' ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('arrived-manually') }}">
                            <i class="fas fa-pencil-alt"></i> <span>Scan Manual</span>
                        </a>
                    </li>

                    </ul>
                </aside>
            </div>

            @yield('content')

            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; {{ date('Y') }}
                    <div class="bullet"></div>
                    {{ env('APP_NAME', 'e-invitation') }} by <a href="https://gmsconsolidate.id">BenDev</a>
                </div>
                <div class="footer-right">v1.3</div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('template/node_modules/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('template/node_modules/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('template/node_modules/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('template/node_modules/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('template/node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>


    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>






    <script src="{{ asset('template/assets/js/stisla.js') }}"></script>
    <!-- JS Libraies -->
    <script src="{{ asset('template/node_modules/izitoast/dist/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('template/node_modules/select2/dist/js/select2.full.min.js') }}"></script>
    <!-- Template JS File -->
    <script src="{{ asset('template/assets/js/scripts.js') }}"></script>
    <script src="{{ asset('template/assets/js/custom.js') }}"></script>
    <script>
        $(document).ready(function() {
            toast("{{ session()->get('success') }}", "{{ session()->get('warning') }}",
                "{{ session()->get('error') }}");
        });
    </script>

</body>

</html>