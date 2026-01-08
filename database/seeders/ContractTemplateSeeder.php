<?php

namespace Database\Seeders;

use App\Models\ContractTemplate;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // PIUS PLUS - Fizičko lice
        ContractTemplate::updateOrCreate(
            ['template_type' => 'individual', 'package_type' => 'pius-plus'],
            ['content' => $this->getIndividualPiusPlusTemplate()]
        );

        // PIUS PLUS - Pravno lice
        ContractTemplate::updateOrCreate(
            ['template_type' => 'company', 'package_type' => 'pius-plus'],
            ['content' => $this->getCompanyPiusPlusTemplate()]
        );

        // PIUS PRO - Fizičko lice
        ContractTemplate::updateOrCreate(
            ['template_type' => 'individual', 'package_type' => 'pius-pro'],
            ['content' => $this->getIndividualPiusProTemplate()]
        );

        // PIUS PRO - Pravno lice
        ContractTemplate::updateOrCreate(
            ['template_type' => 'company', 'package_type' => 'pius-pro'],
            ['content' => $this->getCompanyPiusProTemplate()]
        );
    }

    private function getIndividualPiusPlusTemplate(): string
    {
        return <<<'EOT'
UGOVOR O PRODAJI KURSA NA RATE

Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Beč, Austrija
ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (fizičko lice):

Ime i prezime: {ime} {prezime}
Adresa prebivališta: {adresa}, {postanskiBroj} {mjesto}, {drzava}
Broj ličnog dokumenta: {brojLicnogDokumenta}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PLUS u trajanju od 60 dana/2 mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa i izdavanje sertifikata po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 1.800 EUR (slovima: hiljadu osamsto evra). Kupac se obavezuje da plati ukupnu cijenu u sljedećim ratama:

• Akontacija: 400 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 500 EUR – dospijeva do 01.11.2025
• Treća rata: 900 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250

Član 3. Obaveze Prodavca
Prodavac se obavezuje da:
• Omogući Kupcu pristup svim materijalima kursa PIUS PLUS
• Pruži podršku putem WhatsApp grupe tokom trajanja kursa
• Organizuje sedmične grupne pozive
• Izda digitalni sertifikat po završetku kursa i ispunjenju svih obaveza

Član 4. Obaveze Kupca
Kupac se obavezuje da:
• Uplati sve rate u skladu sa ugovorenim rokovima
• Aktivno učestvuje u kursu
• Ne dijeli materijale kursa sa trećim licima
• Poštuje pravila ponašanja u grupi

Član 5. Raskid ugovora
U slučaju da Kupac ne izvrši uplatu bilo koje rate u roku od 7 dana od datuma dospijeća, Prodavac ima pravo da raskine ovaj ugovor bez prethodne najave. U tom slučaju, Kupac gubi pravo na povrat do tada uplaćenih sredstava.

Član 6. Završne odredbe
Ovaj ugovor stupa na snagu danom potpisivanja od strane obje ugovorne strane. Za sve sporove koji mogu nastati iz ovog ugovora nadležan je sud u Beču, Austrija.

Ugovor je sačinjen u dva istovjetna primjerka, po jedan za svaku ugovornu stranu.

U Beču, {datum}

_______________________          _______________________
Prodavac                         Kupac
Željka Radičanin                 {ime} {prezime}
EOT;
    }

    private function getCompanyPiusPlusTemplate(): string
    {
        return <<<'EOT'
UGOVOR O PRODAJI KURSA NA RATE

Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Beč, Austrija
ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (pravno lice):

Naziv firme: {nazivFirme}
PDV broj: {pdvBroj}
Adresa firme: {adresaFirme}, {postanskiBrojFirme} {mjestoFirme}, {drzavaFirme}
Registracijski broj: {registracijaFirme}
Zastupnik: {ime} {prezime}
Broj ličnog dokumenta zastupnika: {brojLicnogDokumenta}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PLUS u trajanju od 60 dana/2 mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa i izdavanje sertifikata po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 1.800 EUR (slovima: hiljadu osamsto evra). Kupac se obavezuje da plati ukupnu cijenu u sljedećim ratama:

• Akontacija: 400 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 500 EUR – dospijeva do 01.11.2025
• Treća rata: 900 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250

Član 3. Obaveze Prodavca
Prodavac se obavezuje da:
• Omogući Kupcu pristup svim materijalima kursa PIUS PLUS
• Pruži podršku putem WhatsApp grupe tokom trajanja kursa
• Organizuje sedmične grupne pozive
• Izda digitalni sertifikat po završetku kursa i ispunjenju svih obaveza

Član 4. Obaveze Kupca
Kupac se obavezuje da:
• Uplati sve rate u skladu sa ugovorenim rokovima
• Aktivno učestvuje u kursu
• Ne dijeli materijale kursa sa trećim licima
• Poštuje pravila ponašanja u grupi

Član 5. Raskid ugovora
U slučaju da Kupac ne izvrši uplatu bilo koje rate u roku od 7 dana od datuma dospijeća, Prodavac ima pravo da raskine ovaj ugovor bez prethodne najave. U tom slučaju, Kupac gubi pravo na povrat do tada uplaćenih sredstava.

Član 6. Završne odredbe
Ovaj ugovor stupa na snagu danom potpisivanja od strane obje ugovorne strane. Za sve sporove koji mogu nastati iz ovog ugovora nadležan je sud u Beču, Austrija.

Ugovor je sačinjen u dva istovjetna primjerka, po jedan za svaku ugovornu stranu.

U Beču, {datum}

_______________________          _______________________
Prodavac                         Kupac
Željka Radičanin                 {nazivFirme}
                                 Zastupnik: {ime} {prezime}
EOT;
    }

    private function getIndividualPiusProTemplate(): string
    {
        return <<<'EOT'
UGOVOR O PRODAJI KURSA NA RATE

Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Beč, Austrija
ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (fizičko lice):

Ime i prezime: {ime} {prezime}
Adresa prebivališta: {adresa}, {postanskiBroj} {mjesto}, {drzava}
Broj ličnog dokumenta: {brojLicnogDokumenta}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PRO u trajanju od 60 dana/2 mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa, izdavanje sertifikata i isporuku startnog paketa za rad po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 2.500 EUR (slovima: dvije hiljade petsto evra). Kupac se obavezuje da plati ukupnu cijenu u sljedećim ratama:

• Akontacija: 500 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 1.000 EUR – dospijeva do 01.11.2025
• Treća rata: 1.000 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250

Član 3. Obaveze Prodavca
Prodavac se obavezuje da:
• Omogući Kupcu pristup svim materijalima kursa PIUS PRO
• Pruži podršku putem WhatsApp grupe tokom trajanja kursa
• Organizuje sedmične grupne pozive
• Izda digitalni sertifikat po završetku kursa i ispunjenju svih obaveza
• Isporuči startni paket za rad koji uključuje: Mašinu za PMU, 20 igala, 3 boje, mjerač za obrve, 10 posuda za boju, lateks za vježbanje

Član 4. Obaveze Kupca
Kupac se obavezuje da:
• Uplati sve rate u skladu sa ugovorenim rokovima
• Aktivno učestvuje u kursu
• Ne dijeli materijale kursa sa trećim licima
• Poštuje pravila ponašanja u grupi

Član 5. Raskid ugovora
U slučaju da Kupac ne izvrši uplatu bilo koje rate u roku od 7 dana od datuma dospijeća, Prodavac ima pravo da raskine ovaj ugovor bez prethodne najave. U tom slučaju, Kupac gubi pravo na povrat do tada uplaćenih sredstava i startnog paketa.

Član 6. Završne odredbe
Ovaj ugovor stupa na snagu danom potpisivanja od strane obje ugovorne strane. Za sve sporove koji mogu nastati iz ovog ugovora nadležan je sud u Beču, Austrija.

Ugovor je sačinjen u dva istovjetna primjerka, po jedan za svaku ugovornu stranu.

U Beču, {datum}

_______________________          _______________________
Prodavac                         Kupac
Željka Radičanin                 {ime} {prezime}
EOT;
    }

    private function getCompanyPiusProTemplate(): string
    {
        return <<<'EOT'
UGOVOR O PRODAJI KURSA NA RATE

Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Beč, Austrija
ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (pravno lice):

Naziv firme: {nazivFirme}
PDV broj: {pdvBroj}
Adresa firme: {adresaFirme}, {postanskiBrojFirme} {mjestoFirme}, {drzavaFirme}
Registracijski broj: {registracijaFirme}
Zastupnik: {ime} {prezime}
Broj ličnog dokumenta zastupnika: {brojLicnogDokumenta}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PRO u trajanju od 60 dana/2 mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa, izdavanje sertifikata i isporuku startnog paketa za rad po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 2.500 EUR (slovima: dvije hiljade petsto evra). Kupac se obavezuje da plati ukupnu cijenu u sljedećim ratama:

• Akontacija: 500 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 1.000 EUR – dospijeva do 01.11.2025
• Treća rata: 1.000 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250

Član 3. Obaveze Prodavca
Prodavac se obavezuje da:
• Omogući Kupcu pristup svim materijalima kursa PIUS PRO
• Pruži podršku putem WhatsApp grupe tokom trajanja kursa
• Organizuje sedmične grupne pozive
• Izda digitalni sertifikat po završetku kursa i ispunjenju svih obaveza
• Isporuči startni paket za rad koji uključuje: Mašinu za PMU, 20 igala, 3 boje, mjerač za obrve, 10 posuda za boju, lateks za vježbanje

Član 4. Obaveze Kupca
Kupac se obavezuje da:
• Uplati sve rate u skladu sa ugovorenim rokovima
• Aktivno učestvuje u kursu
• Ne dijeli materijale kursa sa trećim licima
• Poštuje pravila ponašanja u grupi

Član 5. Raskid ugovora
U slučaju da Kupac ne izvrši uplatu bilo koje rate u roku od 7 dana od datuma dospijeća, Prodavac ima pravo da raskine ovaj ugovor bez prethodne najave. U tom slučaju, Kupac gubi pravo na povrat do tada uplaćenih sredstava i startnog paketa.

Član 6. Završne odredbe
Ovaj ugovor stupa na snagu danom potpisivanja od strane obje ugovorne strane. Za sve sporove koji mogu nastati iz ovog ugovora nadležan je sud u Beču, Austrija.

Ugovor je sačinjen u dva istovjetna primjerka, po jedan za svaku ugovornu stranu.

U Beču, {datum}

_______________________          _______________________
Prodavac                         Kupac
Željka Radičanin                 {nazivFirme}
                                 Zastupnik: {ime} {prezime}
EOT;
    }
}
