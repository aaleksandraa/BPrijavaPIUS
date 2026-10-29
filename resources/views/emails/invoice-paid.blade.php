Postovana/i {{ $student->first_name }} {{ $student->last_name }},

U prilogu se nalazi faktura broj {{ $invoice->invoice_number }} za kurs {{ $student->package?->name ?? $student->package_type }}.
Opis: {{ $invoice->description }}

Hvala Vam na ukazanom povjerenju.

S postovanjem,
Zeljka Radicanin
PIUS ACADEMY
