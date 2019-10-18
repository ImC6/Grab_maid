<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Grabmaid Payment Response</title>
</head>
<body>
    <p>Your payment is {{ $data['payment_status'] == 1 ? 'successful.' : 'failed.' }}</p>
    <a href="{{ route('payment.back-to-app') }}">Return to Application</a>
</body>
<script language="javascript">
</script>
</html>
