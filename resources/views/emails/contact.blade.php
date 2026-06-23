<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New Mauricare contact enquiry</title>
</head>
<body style="font-family: Arial, sans-serif; color: #263238; line-height: 1.6;">
    <h1 style="font-size: 22px; color: #119bd3;">New contact enquiry</h1>

    <p><strong>Name:</strong> {{ $data['name'] }}</p>
    <p><strong>Phone:</strong> {{ $data['phone'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] ?: 'Not provided' }}</p>

    <p><strong>Message:</strong></p>
    <p>{{ $data['message'] }}</p>
</body>
</html>
