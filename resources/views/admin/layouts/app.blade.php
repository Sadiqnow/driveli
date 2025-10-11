<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Drivelink')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    @stack('styles')
</head>
<body>
    <div class="container">
        <header class="my-3">
            <h1 class="h4">@yield('header', 'Drivelink Admin')</h1>
        </header>

        <main id="main-content">
            @yield('content')
        </main>

        <footer class="mt-5 text-muted small">&copy; {{ date('Y') }} Drivelink</footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
