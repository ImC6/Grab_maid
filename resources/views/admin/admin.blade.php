<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Grabmaid | Admin</title>
    <link rel="stylesheet" href="{{ asset(mix('/css/app.css')) }}">
</head>
<body>
    <div id="root"></div>
    <script>
        var baseUrl = '{{ url("/") }}';
        var baseAPIUrl = baseUrl + '/api';
    </script>
    <script src="{{ asset(mix('/js/manifest.js')) }}"></script>
    <script src="{{ asset(mix('/js/vendor.js')) }}"></script>
    <script src="{{ asset(mix('/js/app.js')) }}"></script>
</body>
</html>
