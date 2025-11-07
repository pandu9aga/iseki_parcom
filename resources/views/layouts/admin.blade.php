<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Iseki Parcom - Part Comparator</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}" />
        <!-- Font Awesome icons (free version)-->
        <script src="{{ asset('assets/js/all.js') }}" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        {{-- <link href="https://fonts.googleapis.com/css?family=Saira+Extra+Condensed:500,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Muli:400,400i,800,800i" rel="stylesheet" type="text/css" /> --}}
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
        @yield('style')
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" id="sideNav">
            <a class="navbar-brand js-scroll-trigger" href="{{ route('dashboard.admin') }}">
                <span class="d-block d-lg-none">Iseki Parcom</span>
                <span class="d-none d-lg-block"><img class="img-fluid img-profile rounded-circle mx-auto mb-2" src="{{ asset('assets/img/profile.png') }}" alt="Iseki Parcom" /></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav">
                    <li class="nav-item"><span class="nav-link js-scroll-trigger active" href="#">Part Comparator</span></li>
                    <li class="nav-item"><a class="nav-link js-scroll-trigger {{ $page === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.admin') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link js-scroll-trigger {{ $page === 'record' ? 'active' : '' }}" href="{{ route('record.admin', ['Id_Comparison' => 1]) }}">Record</a></li>
                    <li class="nav-item"><a class="nav-link js-scroll-trigger {{ $page === 'user' ? 'active' : '' }}" href="{{ route('user') }}">User</a></li>
                    <li class="nav-item"><a class="nav-link js-scroll-trigger {{ $page === 'model' ? 'active' : '' }}" href="{{ route('model') }}">Model</a></li>
                    <li class="nav-item"><a class="nav-link js-scroll-trigger {{ $page === 'logout' ? 'active' : '' }}" href="{{ route('logout') }}">Logout</a></li>
                </ul>
            </div>
        </nav>
        <!-- Page Content-->
        @yield('content')

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <p class="text-dark my-4 text-sm font-weight-normal">
                                © <script>
                                    document.write(new Date().getFullYear())
                                </script>,
                                <span class="text-primary">PT. Iseki Indonesia</span> - Part Comparator
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('assets/js/scripts.js') }}"></script>
        @yield('script')
    </body>
</html>
