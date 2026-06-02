<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktura - PIUS ACADEMY</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #D4AF37 0%, #B8960C 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #000; margin: 0; font-size: 24px;">PIUS ACADEMY</h1>
        <p style="color: #000; margin: 10px 0 0 0; opacity: 0.8;">Faktura</p>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none;">
        <p>Postovana/i {{ $student->first_name }} {{ $student->last_name }},</p>

        <p>
            U prilogu se nalazi faktura broj <strong>{{ $invoice->invoice_number }}</strong>
            za uplacenu ratu.
        </p>

        <p>
            Hvala Vam na ukazanom povjerenju.
        </p>

        <p style="margin-top: 30px;">
            S postovanjem,<br>
            <strong>Zeljka Radicanin</strong><br>
            PIUS ACADEMY
        </p>
    </div>

    <div style="background: #333; color: #999; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; font-size: 12px;">
        <p style="margin: 0;">
            <strong style="color: #D4AF37;">PIUS ACADEMY</strong><br>
            SchÃ¶nbrunner Str. 242, 1120 Wien<br>
            Tel: +43 699 10287577
        </p>
    </div>
</body>
</html>
