<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Novi ugovor potpisan</title>
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
            font-size: 20px;
            color: #333;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-row {
            margin: 8px 0;
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #666;
            display: inline-block;
            width: 180px;
        }
        .value {
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Novi ugovor potpisan - PIUS ACADEMY</h1>
        </div>

        <p>Novi student je potpisao ugovor. Detalji u nastavku:</p>

        <div class="info-section">
            <h3>Podaci o studentu</h3>
            <div class="info-row">
                <span class="label">Ime i prezime:</span>
                <span class="value">{{ $student->first_name }} {{ $student->last_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value">{{ $student->email }}</span>
            </div>
            <div class="info-row">
                <span class="label">Telefon:</span>
                <span class="value">{{ $student->phone }}</span>
            </div>
            <div class="info-row">
                <span class="label">Adresa:</span>
                <span class="value">{{ $student->address }}, {{ $student->postal_code }} {{ $student->city }}, {{ $student->country }}</span>
            </div>
            <div class="info-row">
                <span class="label">Broj ličnog dokumenta:</span>
                <span class="value">{{ $student->id_document_number ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="info-section">
            <h3>Detalji kursa</h3>
            <div class="info-row">
                <span class="label">Paket:</span>
                <span class="value">{{ $package->name ?? strtoupper(str_replace('-', ' ', $student->package_type)) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Cijena:</span>
                <span class="value">{{ number_format($package->price ?? 0, 2, ',', '.') }}€</span>
            </div>
            <div class="info-row">
                <span class="label">Tip lica:</span>
                <span class="value">{{ $student->entity_type === 'individual' ? 'Fizičko lice' : 'Pravno lice' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Način plaćanja:</span>
                <span class="value">{{ $student->payment_method === 'full' ? 'Plaćanje u cjelosti' : 'Plaćanje na rate' }}</span>
            </div>
        </div>

        @if($student->entity_type === 'company')
        <div class="info-section">
            <h3>Podaci o firmi</h3>
            <div class="info-row">
                <span class="label">Naziv firme:</span>
                <span class="value">{{ $student->company_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">PDV broj:</span>
                <span class="value">{{ $student->vat_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Adresa firme:</span>
                <span class="value">{{ $student->company_address }}, {{ $student->company_postal_code }} {{ $student->company_city }}, {{ $student->company_country }}</span>
            </div>
            <div class="info-row">
                <span class="label">Registracijski broj:</span>
                <span class="value">{{ $student->company_registration }}</span>
            </div>
        </div>
        @endif

        <div class="info-section">
            <h3>Informacije o ugovoru</h3>
            <div class="info-row">
                <span class="label">Broj ugovora:</span>
                <span class="value">{{ $contract->contract_number ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Datum potpisivanja:</span>
                <span class="value">{{ $contract->signed_at->format('d.m.Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="label">IP adresa:</span>
                <span class="value">{{ $contract->ip_address ?? 'N/A' }}</span>
            </div>
        </div>

        <p><strong>Potpisani ugovor se nalazi u prilogu ovog emaila.</strong></p>

        <div class="footer">
            <p>PIUS ACADEMY - Automatska notifikacija</p>
        </div>
    </div>
</body>
</html>
