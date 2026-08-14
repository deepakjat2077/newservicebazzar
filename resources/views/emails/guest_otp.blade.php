<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your verification code</title>
</head>
<body>
    <p>Hello {{ $guestName }},</p>
    <p>Your verification code is:</p>
    <h2 style="margin: 12px 0; letter-spacing: 2px;">{{ $code }}</h2>
    <p>This code will expire in 10 minutes.</p>
    <p>If you did not request this code, you can ignore this email.</p>
</body>
</html>
