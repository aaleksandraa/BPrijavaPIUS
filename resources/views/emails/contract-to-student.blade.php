<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Vaš ugovor - PIUS ACADEMY</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            border-bottom: 2px solid #d9a078;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .content p {
            margin: 15px 0;
        }
        .info-section {
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-left: 3px solid #d9a078;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
        }
        .info-row {
            margin: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .important {
            background: #fff3cd;
            padding: 15px;
            margin: 20px 0;
            border-left: 3px solid #ffc107;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PIUS ACADEMY</h1>
        </div>

        <div class="content">
            <p>Poštovani/a {{ $student->first_name }},</p>

            <p>Hvala vam na povjerenju! Vaš ugovor za kurs je uspješno potpisan.</p>

            <p><strong>U prilogu ovog emaila nalazi se vaš potpisani ugovor u PDF formatu.</strong></p>

            <div class="info-section">
                <h3>Detalji kursa</h3>
                <div class="info-row">
                    <span class="label">Paket:</span> {{ $package->name ?? strtoupper(str_replace('-', ' ', $student->package_type)) }}
                </div>
                <div class="info-row">
                    <span class="label">Cijena:</span> {{ number_format($package->price ?? 0, 2, ',', '.') }}€
                </div>
                <div class="info-row">
                    <span class="label">Način plaćanja:</span> {{ $student->payment_method === 'full' ? 'Plaćanje u cjelosti' : 'Plaćanje na rate' }}
                </div>
                <div class="info-row">
                    <span class="label">Broj ugovora:</span> {{ $contract->contract_number ?? 'N/A' }}
                </div>
                <div class="info-row">
                    <span class="label">Datum potpisa:</span> {{ $contract->signed_at->format('d.m.Y H:i') }}
                </div>
            </div>

            @if($student->payment_method === 'installments')
            <div class="important">
                <strong>Važno - Prva rata</strong>
                <p style="margin: 10px 0 0 0;">Molimo vas da prvu ratu uplatite u roku od <strong>48 sati</strong> od potpisivanja ugovora.</p>
            </div>

            <div class="info-section">
                <h3>Podaci za uplatu</h3>
                <div class="info-row">
                    <span class="label">Primalac:</span> Željka Radičanin
                </div>
                <div class="info-row">
                    <span class="label">Banka:</span> Raiffeisen Regionalbank Mödling eGen (mbH)
                </div>
                <div class="info-row">
                    <span class="label">IBAN:</span> <strong>AT31 3225 0000 0196 4659</strong>
                </div>
                <div class="info-row">
                    <span class="label">BIC:</span> RLNWATWWGTD
                </div>
                <div class="info-row">
                    <span class="label">Svrha uplate:</span> {{ $student->first_name }} {{ $student->last_name }} - {{ strtoupper(str_replace('-', ' ', $student->package_type)) }}
                </div>
            </div>
            @endif

            <p>Ako imate bilo kakvih pitanja, slobodno nas kontaktirajte.</p>

            <p>Srdačan pozdrav,<br>
            <strong>PIUS ACADEMY tim</strong></p>
        </div>

        <div class="footer">
            <p>PIUS ACADEMY<br>
            Schönbrunner Str. 242, 1120 Beč, Austrija<br>
            Email: studiopius@yahoo.com | Tel: +43 699 10287577</p>
        </div>
    </div>
</body>
</html>
