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
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #d9a078, #c4956b);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #d9a078;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            color: #d9a078;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            color: #666;
            min-width: 140px;
        }
        .value {
            color: #333;
        }
        .highlight-box {
            background: linear-gradient(135deg, #d9a078, #c4956b);
            color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .highlight-box h3 {
            margin: 0 0 10px 0;
        }
        .bank-details {
            background: #fff3e6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border: 1px solid #d9a078;
        }
        .bank-details h3 {
            margin: 0 0 15px 0;
            color: #d9a078;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #666;
            font-size: 12px;
        }
        .attachment-note {
            background: #e8f5e9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
        }
        .attachment-note strong {
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PIUS ACADEMY</h1>
            <p>Hvala vam na povjerenju!</p>
        </div>

        <div class="content">
            <p class="greeting">Poštovani/a {{ $student->first_name }},</p>

            <p>Čestitamo na uspješnom potpisivanju ugovora za kurs <strong>{{ $student->package_type === 'pius-plus' ? 'PIUS PLUS' : 'PIUS PRO' }}</strong>!</p>

            <div class="attachment-note">
                <strong>📎 U prilogu ovog emaila nalazi se vaš potpisani ugovor u PDF formatu.</strong>
                <br>Molimo vas da ga sačuvate za svoju evidenciju.
            </div>

            <div class="info-box">
                <h3>Detalji vašeg kursa</h3>
                <div class="info-row">
                    <span class="label">Paket:</span>
                    <span class="value">{{ $student->package_type === 'pius-plus' ? 'PIUS PLUS' : 'PIUS PRO' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Cijena:</span>
                    <span class="value">{{ $student->package_type === 'pius-plus' ? '1.800€' : '2.500€' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Način plaćanja:</span>
                    <span class="value">{{ $student->payment_method === 'full' ? 'Plaćanje u cjelosti' : 'Plaćanje na rate' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Broj ugovora:</span>
                    <span class="value">{{ $contract->contract_number ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Datum potpisa:</span>
                    <span class="value">{{ $contract->signed_at->format('d.m.Y H:i') }}</span>
                </div>
            </div>

            @if($student->payment_method === 'installments')
            <div class="highlight-box">
                <h3>⏰ Važno - Prva rata</h3>
                <p style="margin: 0;">Molimo vas da prvu ratu uplatite u roku od <strong>24 sata</strong> od potpisivanja ugovora.</p>
            </div>

            <div class="info-box">
                <h3>Plan plaćanja</h3>
                @if($student->package_type === 'pius-plus')
                <div class="info-row">
                    <span class="label">Prva rata:</span>
                    <span class="value">400€ (u roku od 24h)</span>
                </div>
                <div class="info-row">
                    <span class="label">Druga rata:</span>
                    <span class="value">500€ (do 01.11.2025)</span>
                </div>
                <div class="info-row">
                    <span class="label">Treća rata:</span>
                    <span class="value">900€ (do 01.12.2025)</span>
                </div>
                @else
                <div class="info-row">
                    <span class="label">Prva rata:</span>
                    <span class="value">500€ (u roku od 24h)</span>
                </div>
                <div class="info-row">
                    <span class="label">Druga rata:</span>
                    <span class="value">1.000€ (do 01.11.2025)</span>
                </div>
                <div class="info-row">
                    <span class="label">Treća rata:</span>
                    <span class="value">1.000€ (do 01.12.2025)</span>
                </div>
                @endif
            </div>
            @endif

            <div class="bank-details">
                <h3>🏦 Podaci za uplatu</h3>
                <div class="info-row">
                    <span class="label">Primalac:</span>
                    <span class="value">Željka Radičanin</span>
                </div>
                <div class="info-row">
                    <span class="label">Banka:</span>
                    <span class="value">Raiffeisen Regionalbank Mödling eGen (mbH)</span>
                </div>
                <div class="info-row">
                    <span class="label">IBAN:</span>
                    <span class="value"><strong>AT31 3225 0000 0196 4659</strong></span>
                </div>
                <div class="info-row">
                    <span class="label">BIC:</span>
                    <span class="value">RLNWATWWGTD</span>
                </div>
                <div class="info-row">
                    <span class="label">Svrha uplate:</span>
                    <span class="value">{{ $student->first_name }} {{ $student->last_name }} - {{ $student->package_type === 'pius-plus' ? 'PIUS PLUS' : 'PIUS PRO' }}</span>
                </div>
            </div>

            <p>Ako imate bilo kakvih pitanja, slobodno nas kontaktirajte putem emaila ili telefona.</p>

            <p>Radujemo se zajedničkom radu!</p>

            <p>Srdačan pozdrav,<br>
            <strong>PIUS ACADEMY tim</strong></p>
        </div>

        <div class="footer">
            <p>PIUS ACADEMY | Schönbrunner Str. 242, 1120 Beč, Austrija</p>
            <p>Email: studiopius@yahoo.com | Tel: +43 699 10287577</p>
        </div>
    </div>
</body>
</html>
