<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') | GRABMAID</title>

    <link rel="stylesheet" href="{{ asset(mix('/css/app.css')) }}">
    @stack('styles')
</head>
<body>
    @yield('header')

    <div class="page-wrapper">
        @yield('content')
    </div>

    @yield('footer')
</body>
</html>
