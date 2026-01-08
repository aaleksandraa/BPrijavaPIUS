<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ugovor - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #d9a078;
            color: white;
            padding: 12px;
            margin-bottom: 15px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9pt;
        }
        .section {
            margin-bottom: 12px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #d9a078;
            margin-bottom: 8px;
            border-bottom: 2px solid #d9a078;
            padding-bottom: 4px;
        }
        .contract-content {
            background-color: white;
            padding: 12px;
            border: 1px solid #ddd;
            margin-top: 12px;
            text-align: justify;
            line-height: 1.5;
        }
        .signature-section {
            margin-top: 15px;
            padding: 12px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            page-break-inside: avoid;
        }
        .signature-image {
            max-width: 180px;
            max-height: 70px;
            border: 1px solid #ddd;
            background-color: white;
            padding: 8px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20mm;
            background-color: #f8f9fa;
            border-top: 1px solid #ddd;
            padding: 8px 15mm;
            font-size: 8pt;
            color: #666;
        }
        .footer-content {
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
            vertical-align: middle;
        }
        .footer-center {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }
        .page-number:before {
            content: "Strana " counter(page) " od " counter(pages);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 3px 0;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 40%;
        }
        .content-wrapper {
            margin-bottom: 25mm;
        }
    </style>
</head>
<body>
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">PIUS ACADEMY</div>
            <div class="footer-center"><span class="page-number"></span></div>
            <div class="footer-right">Generisano: {{ now()->format('d.m.Y') }}</div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="header">
            <h1>PIUS ACADEMY</h1>
            <p>Ugovor br. {{ $contract->contract_number ?? 'N/A' }}</p>
        </div>

        <div class="section">
            <div class="section-title">PODACI O STUDENTU</div>
            <table>
                <tr>
                    <td class="label">Ime i prezime:</td>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $student->email }}</td>
                </tr>
                <tr>
                    <td class="label">Telefon:</td>
                    <td>{{ $student->phone }}</td>
                </tr>
                <tr>
                    <td class="label">Adresa:</td>
                    <td>{{ $student->address }}, {{ $student->postal_code }} {{ $student->city }}, {{ $student->country }}</td>
                </tr>
                <tr>
                    <td class="label">Broj ličnog dokumenta:</td>
                    <td>{{ $student->id_document_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Paket:</td>
                    <td>{{ $student->package_type === 'pius-plus' ? 'PIUS PLUS (1.800€)' : 'PIUS PRO (2.500€)' }}</td>
                </tr>
                <tr>
                    <td class="label">Tip lica:</td>
                    <td>{{ $student->entity_type === 'individual' ? 'Fizičko lice' : 'Pravno lice' }}</td>
                </tr>
                <tr>
                    <td class="label">Način plaćanja:</td>
                    <td>{{ $student->payment_method === 'full' ? 'Plaćanje u cjelosti' : 'Plaćanje na rate' }}</td>
                </tr>
            </table>
        </div>

        @if($student->entity_type === 'company')
        <div class="section">
            <div class="section-title">PODACI O FIRMI</div>
            <table>
                <tr>
                    <td class="label">Naziv firme:</td>
                    <td>{{ $student->company_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">PDV broj:</td>
                    <td>{{ $student->vat_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Adresa firme:</td>
                    <td>{{ $student->company_address ?? 'N/A' }}, {{ $student->company_postal_code ?? '' }} {{ $student->company_city ?? '' }}, {{ $student->company_country ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Registracijski broj:</td>
                    <td>{{ $student->company_registration ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        @endif

        @if($student->payment_method === 'installments')
        <div class="section" style="background: #f5ece5;">
            <div class="section-title">PLAN PLAĆANJA</div>
            <table>
                @if($student->package_type === 'pius-plus')
                <tr>
                    <td class="label">Prva rata:</td>
                    <td>400€ (uplata u roku od 24h)</td>
                </tr>
                <tr>
                    <td class="label">Druga rata:</td>
                    <td>500€ (do 01.11.2025)</td>
                </tr>
                <tr>
                    <td class="label">Treća rata:</td>
                    <td>900€ (do 01.12.2025)</td>
                </tr>
                <tr>
                    <td class="label">UKUPNO:</td>
                    <td><strong>1.800€</strong></td>
                </tr>
                @else
                <tr>
                    <td class="label">Prva rata:</td>
                    <td>500€ (uplata u roku od 24h)</td>
                </tr>
                <tr>
                    <td class="label">Druga rata:</td>
                    <td>1.000€ (do 01.11.2025)</td>
                </tr>
                <tr>
                    <td class="label">Treća rata:</td>
                    <td>1.000€ (do 01.12.2025)</td>
                </tr>
                <tr>
                    <td class="label">UKUPNO:</td>
                    <td><strong>2.500€</strong></td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <div class="section">
            <div class="section-title">PODACI ZA UPLATU</div>
            <table>
                <tr>
                    <td class="label">Primalac:</td>
                    <td>Željka Radičanin</td>
                </tr>
                <tr>
                    <td class="label">Banka:</td>
                    <td>Raiffeisen Regionalbank Mödling eGen (mbH)</td>
                </tr>
                <tr>
                    <td class="label">IBAN:</td>
                    <td>AT31 3225 0000 0196 4659</td>
                </tr>
                <tr>
                    <td class="label">BIC:</td>
                    <td>RLNWATWWGTD</td>
                </tr>
            </table>
        </div>

        <div class="contract-content">
            <div class="section-title">SADRŽAJ UGOVORA</div>
            {!! nl2br(e($contract->contract_content)) !!}
        </div>

        @if($contract->signature_data)
        <div class="signature-section">
            <div class="section-title">DIGITALNI POTPIS</div>
            <img src="{{ $contract->signature_data }}" class="signature-image" alt="Potpis">
            <p style="margin: 8px 0 0 0; font-size: 9pt;">Potpisano: {{ $contract->signed_at?->format('d.m.Y H:i') }}</p>
        </div>
        @endif
    </div>
</body>
</html>
