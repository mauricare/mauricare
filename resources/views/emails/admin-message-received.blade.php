<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Mauricare message</title>
</head>
<body style="margin: 0; padding: 0; background: #f1f5f9; font-family: Arial, sans-serif; color: #0f172a;">
    <div style="max-width: 620px; margin: 0 auto; padding: 32px 16px;">
        <div style="overflow: hidden; border-radius: 16px; background: #ffffff; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);">
            <div style="background: #117d73; padding: 24px 28px; color: #ffffff;">
                <h1 style="margin: 0; font-size: 22px;">New message for Mauricare</h1>
            </div>
            <div style="padding: 28px;">
                <p style="margin: 0 0 8px; color: #64748b; font-size: 13px; font-weight: bold; text-transform: uppercase;">
                    Sent by
                </p>
                <p style="margin: 0; font-size: 18px; font-weight: bold;">{{ $sender->name }}</p>
                <p style="margin: 4px 0 24px; color: #64748b;">{{ $sender->email }}</p>

                <div style="border-left: 4px solid #117d73; border-radius: 8px; background: #f8fafc; padding: 18px;">
                    <p style="margin: 0; white-space: pre-wrap; line-height: 1.6;">{{ $receivedMessage->body }}</p>
                </div>

                <a
                    href="{{ $messagesUrl }}"
                    style="display: inline-block; margin-top: 24px; border-radius: 8px; background: #117d73; padding: 12px 20px; color: #ffffff; font-size: 14px; font-weight: bold; text-decoration: none;"
                >
                    Open conversation
                </a>
            </div>
        </div>
    </div>
</body>
</html>
