<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rechnung {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #000000;
        }
        .container {
            width: 100%;
            padding: 0;
        }
        .header-table {
            width: 100%;
            margin-bottom: 5mm;
            padding-bottom: 5mm;
        }
        .logo-cell {
            vertical-align: bottom;
        }
        .logo {
            width: 200px;
            height: auto;
        }
        .company-info-cell {
            vertical-align: bottom;
            padding-left: 10px;
        }
        .company-info {
            font-size: 10pt;
        }
        .divider {
            border: 0;
            border-top: 1px solid #000000;
            margin: 10px 0;
        }
        .recipient-table {
            width: 100%;
            margin-bottom: 5mm;
            font-size: 11pt;
        }
        .recipient-left {
            width: 48%;
            vertical-align: top;
        }
        .recipient-right {
            width: 48%;
            vertical-align: top;
            text-align: right;
            padding-top: 20px;
        }
        .recipient-left h2 {
            font-size: 12pt;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        .recipient-left p {
            margin: 0;
            line-height: 1.5;
        }
        .invoice-title {
            text-align: center;
            margin-bottom: 5mm;
        }
        .invoice-title h1 {
            font-size: 16pt;
            margin: 0;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
            font-size: 10pt;
        }
        .invoice-table th {
            background-color: #000000;
            color: #ffffff;
            padding: 8px;
            text-align: left;
            font-weight: normal;
        }
        .invoice-table td {
            border: 1px solid #000000;
            padding: 8px;
        }
        .invoice-table .col-num {
            width: 5%;
            text-align: center;
        }
        .invoice-table .col-desc {
            width: 35%;
        }
        .invoice-table .col-qty {
            width: 10%;
            text-align: center;
        }
        .invoice-table .col-price {
            width: 16%;
            text-align: right;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 5mm 10mm;
            font-size: 9pt;
        }
        .footer-note {
            margin-bottom: 5px;
        }
        .footer-divider {
            border: 0;
            border-top: 1px solid #000000;
            margin: 5px 0;
        }
        .footer-table {
            width: 100%;
        }
        .footer-col-small {
            width: 25%;
            vertical-align: top;
        }
        .footer-col-medium {
            width: 30%;
            vertical-align: top;
            text-align: right;
        }
        .footer-col-large {
            width: 45%;
            vertical-align: top;
            text-align: right;
        }
        .footer p {
            margin: 0;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Logo -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoPath)
                        <img src="{{ $logoPath }}" class="logo" alt="Studio PIUS Logo">
                    @else
                        <div style="font-size: 20pt; font-weight: bold;">Studio PIUS</div>
                    @endif
                </td>
                <td class="company-info-cell">
                    <div class="company-info">
                        <p>Zeljka Radicanin<br>Studio PIUS<br>UID: ATU65267579</p>
                    </div>
                </td>
            </tr>
        </table>

        <hr class="divider">

        <!-- Recipient Info -->
        <table class="recipient-table">
            <tr>
                <td class="recipient-left">
                    <h2>Empfänger:</h2>
                    @if($student->entity_type === 'company')
                        <p>Firma</p>
                        <p>
                            {{ $student->company_name }}<br>
                            @if($student->vat_number)UID: {{ $student->vat_number }}<br>@endif
                            {{ $student->company_address ?? $student->address }}<br>
                            {{ $student->company_postal_code }} {{ $student->company_city }}<br>
                            {{ $student->company_country }}<br>
                            {{ $student->email }}<br>
                            <br>
                            Ansprechpartner: {{ $student->first_name }} {{ $student->last_name }}<br>
                            @if($student->id_document_number)ID: {{ $student->id_document_number }}@endif
                        </p>
                    @else
                        <p>Privat</p>
                        <p>
                            {{ $student->first_name }} {{ $student->last_name }}<br>
                            {{ $student->address }}<br>
                            {{ $student->postal_code }} {{ $student->city }}<br>
                            {{ $student->country }}<br>
                            {{ $student->email }}<br>
                            @if($student->id_document_number)ID: {{ $student->id_document_number }}@endif
                        </p>
                    @endif
                </td>
                <td class="recipient-right">
                    <p>
                        Rechnung Nr. {{ $invoice->invoice_number }}<br>
                        Rechnungsdatum: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.m.Y') }}
                    </p>
                </td>
            </tr>
        </table>

        <!-- Invoice Title -->
        <div class="invoice-title">
            <h1>Rechnung Nr. {{ $invoice->invoice_number }}</h1>
        </div>

        <!-- Invoice Details Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th class="col-desc">Beschreibung</th>
                    <th class="col-qty">Menge</th>
                    <th class="col-price">Netto Preis</th>
                    <th class="col-price">MwSt. ({{ number_format($invoice->vat_rate, 0) }}%)</th>
                    <th class="col-price">Gesamt Preis</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-num">01</td>
                    <td class="col-desc">{{ $invoice->description }}</td>
                    <td class="col-qty">1</td>
                    <td class="col-price">€{{ number_format($invoice->net_amount, 2, ',', '.') }}</td>
                    <td class="col-price">€{{ number_format($invoice->vat_amount, 2, ',', '.') }}</td>
                    <td class="col-price">€{{ number_format($invoice->total_amount, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-note">* Rechnungsdatum ist gleich Liefer- bzw. Leistungsdatum</p>
            <hr class="footer-divider">
            <table class="footer-table">
                <tr>
                    <td class="footer-col-small">
                        <p>Zeljka Radicanin<br>Studio PIUS<br>UID: ATU65267579</p>
                    </td>
                    <td class="footer-col-medium">
                        <p>Schönbrunner Str. 242<br>1120 Wien, Österreich<br>Tel: +43 699 10287577</p>
                    </td>
                    <td class="footer-col-large">
                        <p>Raiffeisen Regionalbank Mödling eGen (mbH)<br>BLZ: 32250; BIC: RLNWATWWGTD<br>IBAN: AT31 3225 0000 0196 4659</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
