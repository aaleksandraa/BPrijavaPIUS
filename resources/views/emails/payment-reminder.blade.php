<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zahlungserinnerung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #D4AF37 0%, #B8960C 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #000; margin: 0; font-size: 24px;">Studio PIUS</h1>
        <p style="color: #000; margin: 10px 0 0 0; opacity: 0.8;">Zahlungserinnerung</p>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none;">
        <div style="white-space: pre-wrap; font-family: Arial, sans-serif;">{{ $emailContent }}</div>
    </div>

    <div style="background: #333; color: #999; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; font-size: 12px;">
        <p style="margin: 0 0 10px 0;">
            <strong style="color: #D4AF37;">Studio PIUS</strong><br>
            Schönbrunner Str. 242, 1120 Wien<br>
            Tel: +43 699 10287577
        </p>
        <p style="margin: 10px 0 0 0; color: #666; font-style: italic;">
            Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese Nachricht.
        </p>
    </div>
</body>
</html>
