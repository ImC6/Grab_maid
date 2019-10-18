<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="x-apple-disable-message-reformatting">
    <title>Grabmaid: Password Reset</title>
</head>

<body style="margin:0 auto">
    <table align="center" width="600" border="0" cellpadding="0" cellspacing="0" style="font-family: 'Arial Rounded MT Bold', sans-serif; font-weight: 400">
        <tr>
            <td>
                <p>You requested to reset password on Grabmaid using {{ $email }}</p>
                <p>Click on this <a href="{{ route('password.reset', [$token, 'email' => $email]) }}" target="_blank">link</a> to reset your password.</p>
            </td>
        </tr>
    </table>
</body>
</html>
