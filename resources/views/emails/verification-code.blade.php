<html>

<head>
    <title>Verification Code</title>
</head>

<body>
    <h1>Verification Code for {{ env('APP_NAME') }}</h1>
    <p>Hi {{ $name }},</p>
    <p>Thank you for registering with {{ env('APP_NAME') }}. Please use the following verification code to verify your
        account.</p>
    <p><strong>{{ $verificationCode }}</strong></p>
    <p>
        Please note that this verification code will expire in 30 minutes.
    </p>
    <p>
        If you did not register with {{ env('APP_NAME') }}, please ignore this email.
    </p>
    <p>Thank you!</p>
</body>

</html>
