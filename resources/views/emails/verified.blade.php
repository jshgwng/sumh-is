<html>

<head>
    <title>Verification Code</title>
</head>

<body>
    <h1>Email Verified for {{ env('APP_NAME') }}</h1>
    <p>Hi {{ $name }},</p>
    <p>Thank you for registering with {{ env('APP_NAME') }}. Your email has been verified.</p>
    <p>
        You can now login to {{ env('APP_NAME') }} and start using the application.
    </p>
    If you did not register with {{ env('APP_NAME') }}, please ignore this email.
    </p>
    <p>Thank you!</p>
</body>

</html>
