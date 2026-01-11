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
            text-align: left;
            line-height: 1.6;
            white-space: pre-line;
        }
        .contract-content b {
            font-weight: bold;
            color: #333;
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
        script {
            display: none;
        }
    </style>
</head>
<body>
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">PIUS ACADEMY</div>
            <div class="footer-center">
                <script type="text/php">
                    if (isset($pdf)) {
                        $text = "Strana {PAGE_NUM} od {PAGE_COUNT}";
                        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                        $size = 8;
                        $pageWidth = $pdf->get_width();
                        $pageHeight = $pdf->get_height();
                        $textWidth = $fontMetrics->get_text_width($text, $font, $size);
                        $x = ($pageWidth - $textWidth) / 2;
                        $y = $pageHeight - 15;
                        $pdf->text($x, $y, $text, $font, $size);
                    }
                </script>
            </div>
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
                    <td>{{ $package->name ?? strtoupper(str_replace('-', ' ', $student->package_type)) }} ({{ number_format($package->price ?? 0, 2, ',', '.') }}€)</td>
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
            @php
                $content = $contract->contract_content;

                // Bold za naslove članaka i sekcija
                $content = preg_replace('/^(Član \d+[a-z]?\..*)/m', '**$1**', $content);

                // Bold za "Obaveze Prodavca:" i "Obaveze Kupca:"
                $content = preg_replace('/(Obaveze Prodavca:|Obaveze Kupca.*:)/m', '**$1**', $content);

                // Bold za važne rečenice (bullet points sa važnim obavezama)
                $content = preg_replace('/(• Ne distribuirati materijale kursa trećim licima bez saglasnosti Prodavca\.)/m', '**$1**', $content);

                // Bold za naslove ugovora
                $content = preg_replace('/^(UGOVOR O.*)/m', '**$1**', $content);
                $content = preg_replace('/^(Zaključen dana:.*)/m', '**$1**', $content);
                $content = preg_replace('/^(Između:)/m', '**$1**', $content);
                $content = preg_replace('/^(Prodavca:.*)/m', '**$1**', $content);
                $content = preg_replace('/^(Kupca \(.*\):.*)/m', '**$1**', $content);
                $content = preg_replace('/^(Ugovorne odredbe:)/m', '**$1**', $content);

                // Escape HTML ali zadrži bold markere
                $content = e($content);

                // Konvertuj ** u <b> tagove
                $content = preg_replace('/\*\*(.*?)\*\*/s', '<b>$1</b>', $content);

                // Konvertuj nove linije u <br>
                $content = nl2br($content);
            @endphp
            {!! $content !!}
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
