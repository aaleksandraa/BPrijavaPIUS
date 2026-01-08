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
        }
        .header {
            background: linear-gradient(135deg, #d9a078, #c4956b);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid #d9a078;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PIUS ACADEMY</h1>
        <p>Novi ugovor potpisan</p>
    </div>

    <div class="content">
        <p>Poštovani,</p>
        <p>Novi student je potpisao ugovor za kurs.</p>

        <div class="info-box">
            <h3>Podaci o studentu</h3>
            <p><span class="label">Ime i prezime:</span> <span class="value">{{ $student->first_name }} {{ $student->last_name }}</span></p>
            <p><span class="label">Email:</span> <span class="value">{{ $student->email }}</span></p>
            <p><span class="label">Telefon:</span> <span class="value">{{ $student->phone }}</span></p>
            <p><span class="label">Adresa:</span> <span class="value">{{ $student->address }}, {{ $student->city }}, {{ $student->country }}</span></p>
        </div>

        <div class="info-box">
            <h3>Detalji kursa</h3>
            <p><span class="label">Paket:</span> <span class="value">{{ $student->package_type === 'pius-plus' ? 'PIUS PLUS (1.800€)' : 'PIUS PRO (2.500€)' }}</span></p>
            <p><span class="label">Tip lica:</span> <span class="value">{{ $student->entity_type === 'individual' ? 'Fizičko lice' : 'Pravno lice' }}</span></p>
            <p><span class="label">Način plaćanja:</span> <span class="value">{{ $student->payment_method === 'full' ? 'Plaćanje u cjelosti' : 'Plaćanje na rate' }}</span></p>
        </div>

        @if($student->entity_type === 'company')
        <div class="info-box">
            <h3>Podaci o firmi</h3>
            <p><span class="label">Naziv firme:</span> <span class="value">{{ $student->company_name }}</span></p>
            <p><span class="label">PDV broj:</span> <span class="value">{{ $student->vat_number }}</span></p>
            <p><span class="label">Adresa firme:</span> <span class="value">{{ $student->company_address }}</span></p>
        </div>
        @endif

        <div class="info-box">
            <h3>Informacije o ugovoru</h3>
            <p><span class="label">ID ugovora:</span> <span class="value">{{ $contract->id }}</span></p>
            <p><span class="label">Datum potpisivanja:</span> <span class="value">{{ $contract->signed_at->format('d.m.Y H:i') }}</span></p>
        </div>

        <p>Možete pregledati sve detalje u admin panelu.</p>
    </div>

    <div class="footer">
        <p>PIUS ACADEMY - Automatska notifikacija</p>
    </div>
</body>
</html>
