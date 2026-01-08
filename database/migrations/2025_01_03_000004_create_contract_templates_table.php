<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('template_type', ['individual', 'company']);
            $table->enum('package_type', ['pius-plus', 'pius-pro']);
            $table->longText('content');
            $table->timestamps();

            $table->unique(['template_type', 'package_type']);
        });

        $this->seedTemplates();
    }

    private function seedTemplates(): void
    {
        $templates = [
            ['individual', 'pius-plus', $this->getPiusPlusIndividualTemplate()],
            ['company', 'pius-plus', $this->getPiusPlusCompanyTemplate()],
            ['individual', 'pius-pro', $this->getPiusProIndividualTemplate()],
            ['company', 'pius-pro', $this->getPiusProCompanyTemplate()],
        ];

        foreach ($templates as [$type, $package, $content]) {
            DB::table('contract_templates')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'template_type' => $type,
                'package_type' => $package,
                'content' => $content,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getPiusPlusIndividualTemplate(): string
    {
        return 'UGOVOR O PRODAJI KURSA NA RATE
Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Wien, Austrija, UID: ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (fizičko lice):

Ime i prezime: {ime} {prezime}
Adresa prebivališta: {adresa}, {mjesto}, {drzava}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PLUS, čije trajanje je dva mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema planu i programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa i izdavanje sertifikata po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 1.800 EUR (slovima: hiljadu osamsto evra). Kupac se obavezuje da plati ukupnu cijenu u sledećim ratama:

• Akontacija: 400 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 500 EUR – dospijeva do 01.11.2025
• Treća rata: 900 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250
BIC: RLNWATWWGTD

U slučaju kašnjenja u uplati, zatezna kamata se obračunava u skladu sa austrijskim Zakonom o kamatama.

Član 3. Trajanje kursa
Kurs traje ukupno dva mjeseca. Nastava se održava prema rasporedu definisanom od strane Prodavca, koji će biti dostavljen Kupcu prije početka kursa.

Član 4. Sertifikat o završenom kursu
Kupac stiče pravo na sertifikat o završenom kursu PIUS PLUS samo nakon što su sve ugovorene rate u potpunosti plaćene. Prodavac zadržava pravo da ne izda sertifikat u slučaju neizmirenih obaveza.

Član 5. Prava i obaveze strana

Obaveze Prodavca:
• Omogućiti Kupcu pohađanje kursa prema programu i rasporedu
• Izvršiti izdavanje sertifikata nakon ispunjenja svih finansijskih obaveza

Obaveze Kupca:
• Plaćati rate u skladu sa Članom 2
• Poštovati raspored kursa i pravila ponašanja na nastavi
• Ne distribuirati materijale kursa trećim licima bez saglasnosti Prodavca

Član 6. Raskid ugovora
U slučaju kašnjenja u plaćanju rata dužeg od 5 dana, Prodavac ima pravo da obustavi učešće Kupca na kursu do izmirenja svih obaveza. Ukoliko Kupac ne izmiri dvije uzastopne rate, Prodavac zadržava pravo da raskine ugovor i zadrži uplaćeni iznos do tog trenutka.

Član 7. Mjerodavno pravo i rešavanje sporova
Ovaj ugovor je sačinjen u skladu sa austrijskim pravom, posebno u skladu sa odredbama Opšteg građanskog zakonika (Allgemeines Bürgerliches Gesetzbuch - ABGB). Sve sporove koji proisteknu iz ovog ugovora strane će rešavati sporazumno, a u slučaju neuspjeha, nadležan je sud u Beču.

Član 8. Završne odredbe
1. Sve izmjene i dopune ovog ugovora važeće su samo ako su sačinjene u pisanom obliku i potpisane od strane obe ugovorne strane.
2. Ovaj ugovor stupa na snagu danom potpisivanja.
3. Ugovor je sačinjen u dva istovjetna primjerka, od kojih svaka strana zadržava po jedan.

Datum potpisivanja: {datum}

Potpis kupca: _________________________';
    }

    private function getPiusPlusCompanyTemplate(): string
    {
        return 'UGOVOR O PRODAJI KURSA NA RATE
Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Wien, Austrija, UID: ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (pravno lice):

Naziv firme: {nazivFirme}
PDV broj: {pdvBroj}
Adresa firme: {adresaFirme}
Registracijski broj: {registracijaFirme}
Kontakt osoba: {ime} {prezime}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PLUS, čije trajanje je dva mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema planu i programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa i izdavanje sertifikata po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 1.800 EUR (slovima: hiljadu osamsto evra). Kupac se obavezuje da plati ukupnu cijenu u sledećim ratama:

• Akontacija: 400 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 500 EUR – dospijeva do 01.11.2025
• Treća rata: 900 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250
BIC: RLNWATWWGTD

U slučaju kašnjenja u uplati, zatezna kamata se obračunava u skladu sa austrijskim Zakonom o kamatama.

Član 3. Trajanje kursa
Kurs traje ukupno dva mjeseca. Nastava se održava prema rasporedu definisanom od strane Prodavca, koji će biti dostavljen Kupcu prije početka kursa.

Član 4. Sertifikat o završenom kursu
Kupac stiče pravo na sertifikat o završenom kursu PIUS PLUS samo nakon što su sve ugovorene rate u potpunosti plaćene. Prodavac zadržava pravo da ne izda sertifikat u slučaju neizmirenih obaveza.

Član 5. Prava i obaveze strana

Obaveze Prodavca:
• Omogućiti Kupcu pohađanje kursa prema programu i rasporedu
• Izvršiti izdavanje sertifikata nakon ispunjenja svih finansijskih obaveza

Obaveze Kupca:
• Plaćati rate u skladu sa Članom 2
• Poštovati raspored kursa i pravila ponašanja na nastavi
• Ne distribuirati materijale kursa trećim licima bez saglasnosti Prodavca

Član 6. Raskid ugovora
U slučaju kašnjenja u plaćanju rata dužeg od 5 dana, Prodavac ima pravo da obustavi učešće Kupca na kursu do izmirenja svih obaveza. Ukoliko Kupac ne izmiri dvije uzastopne rate, Prodavac zadržava pravo da raskine ugovor i zadrži uplaćeni iznos do tog trenutka.

Član 7. Mjerodavno pravo i rešavanje sporova
Ovaj ugovor je sačinjen u skladu sa austrijskim pravom, posebno u skladu sa odredbama Opšteg građanskog zakonika (Allgemeines Bürgerliches Gesetzbuch - ABGB). Sve sporove koji proisteknu iz ovog ugovora strane će rešavati sporazumno, a u slučaju neuspjeha, nadležan je sud u Beču.

Član 8. Završne odredbe
1. Sve izmjene i dopune ovog ugovora važeće su samo ako su sačinjene u pisanom obliku i potpisane od strane obe ugovorne strane.
2. Ovaj ugovor stupa na snagu danom potpisivanja.
3. Ugovor je sačinjen u dva istovjetna primjerka, od kojih svaka strana zadržava po jedan.

Datum potpisivanja: {datum}

Potpis kupca: _________________________';
    }

    private function getPiusProIndividualTemplate(): string
    {
        return 'UGOVOR O PRODAJI KURSA NA RATE
Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Wien, Austrija, UID: ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (fizičko lice):

Ime i prezime: {ime} {prezime}
Adresa prebivališta: {adresa}, {mjesto}, {drzava}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PRO, čije trajanje je dva mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema planu i programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa i izdavanje sertifikata po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 2.500 EUR (slovima: dvije hiljade petsto evra). Kupac se obavezuje da plati ukupnu cijenu u sledećim ratama:

• Akontacija: 500 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 1.000 EUR – dospijeva do 01.11.2025
• Treća rata: 1.000 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250
BIC: RLNWATWWGTD

U slučaju kašnjenja u uplati, zatezna kamata se obračunava u skladu sa austrijskim Zakonom o kamatama.

Član 3. Trajanje kursa
Kurs traje ukupno dva mjeseca. Nastava se održava prema rasporedu definisanom od strane Prodavca, koji će biti dostavljen Kupcu prije početka kursa.

Član 4. Sertifikat o završenom kursu
Kupac stiče pravo na sertifikat o završenom kursu PIUS PRO samo nakon što su sve ugovorene rate u potpunosti plaćene. Prodavac zadržava pravo da ne izda sertifikat u slučaju neizmirenih obaveza.

Član 5. Prava i obaveze strana

Obaveze Prodavca:
• Omogućiti Kupcu pohađanje kursa prema programu i rasporedu
• Izvršiti izdavanje sertifikata nakon ispunjenja svih finansijskih obaveza
• Isporučiti mašinicu za rad i boje na kućnu adresu kupca

Obaveze Kupca:
• Plaćati rate u skladu sa Članom 2
• Poštovati raspored kursa i pravila ponašanja na nastavi
• Ne distribuirati materijale kursa trećim licima bez saglasnosti Prodavca

Član 6. Raskid ugovora
U slučaju kašnjenja u plaćanju rata dužeg od 5 dana, Prodavac ima pravo da obustavi učešće Kupca na kursu do izmirenja svih obaveza. Ukoliko Kupac ne izmiri dvije uzastopne rate, Prodavac zadržava pravo da raskine ugovor i zadrži uplaćeni iznos do tog trenutka.

Član 7. Mjerodavno pravo i rešavanje sporova
Ovaj ugovor je sačinjen u skladu sa austrijskim pravom, posebno u skladu sa odredbama Opšteg građanskog zakonika (Allgemeines Bürgerliches Gesetzbuch - ABGB). Sve sporove koji proisteknu iz ovog ugovora strane će rešavati sporazumno, a u slučaju neuspjeha, nadležan je sud u Beču.

Član 8. Završne odredbe
1. Sve izmjene i dopune ovog ugovora važeće su samo ako su sačinjene u pisanom obliku i potpisane od strane obe ugovorne strane.
2. Ovaj ugovor stupa na snagu danom potpisivanja.
3. Ugovor je sačinjen u dva istovjetna primjerka, od kojih svaka strana zadržava po jedan.

Datum potpisivanja: {datum}

Potpis kupca: _________________________';
    }

    private function getPiusProCompanyTemplate(): string
    {
        return 'UGOVOR O PRODAJI KURSA NA RATE
Zaključen dana: {datum}, u Beču, Austrija

Između:

Prodavca: Željka Radičanin, Studio PIUS, Schönbrunner Str. 242, 1120 Wien, Austrija, UID: ATU65267579, Email: studiopius@yahoo.com, Tel: +43 699 10287577

i

Kupca (pravno lice):

Naziv firme: {nazivFirme}
PDV broj: {pdvBroj}
Adresa firme: {adresaFirme}
Registracijski broj: {registracijaFirme}
Kontakt osoba: {ime} {prezime}
Kontakt telefon: {telefon}
E-mail: {email}

Ugovorne odredbe:

Član 1. Predmet ugovora
Predmet ovog ugovora je prodaja i pohađanje kursa pod nazivom PIUS PRO, čije trajanje je dva mjeseca, počevši od datuma uplate prve rate. Kurs se sprovodi prema planu i programu definisanom od strane Prodavca. Kupac se obavezuje da uplati ukupnu cijenu kursa, dok Prodavac garantuje pohađanje kursa i izdavanje sertifikata po ispunjenju svih obaveza.

Član 2. Cijena i uslovi plaćanja
Ukupna cijena kursa iznosi 2.500 EUR (slovima: dvije hiljade petsto evra). Kupac se obavezuje da plati ukupnu cijenu u sledećim ratama:

• Akontacija: 500 EUR – dospijeva na dan potpisivanja ugovora (najkasnije do 01.10.)
• Druga rata: 1.000 EUR – dospijeva do 01.11.2025
• Treća rata: 1.000 EUR – dospijeva do 01.12.2025

Uplate se vrše na račun Prodavca:
Raiffeisen Regionalbank Mödling eGen (mbH)
IBAN: AT31 3225 0000 0196 4659
BLZ: 32250
BIC: RLNWATWWGTD

U slučaju kašnjenja u uplati, zatezna kamata se obračunava u skladu sa austrijskim Zakonom o kamatama.

Član 3. Trajanje kursa
Kurs traje ukupno dva mjeseca. Nastava se održava prema rasporedu definisanom od strane Prodavca, koji će biti dostavljen Kupcu prije početka kursa.

Član 4. Sertifikat o završenom kursu
Kupac stiče pravo na sertifikat o završenom kursu PIUS PRO samo nakon što su sve ugovorene rate u potpunosti plaćene. Prodavac zadržava pravo da ne izda sertifikat u slučaju neizmirenih obaveza.

Član 5. Prava i obaveze strana

Obaveze Prodavca:
• Omogućiti Kupcu pohađanje kursa prema programu i rasporedu
• Izvršiti izdavanje sertifikata nakon ispunjenja svih finansijskih obaveza
• Isporučiti mašinicu za rad i boje na kućnu adresu kupca

Obaveze Kupca:
• Plaćati rate u skladu sa Članom 2
• Poštovati raspored kursa i pravila ponašanja na nastavi
• Ne distribuirati materijale kursa trećim licima bez saglasnosti Prodavca

Član 6. Raskid ugovora
U slučaju kašnjenja u plaćanju rata dužeg od 5 dana, Prodavac ima pravo da obustavi učešće Kupca na kursu do izmirenja svih obaveza. Ukoliko Kupac ne izmiri dvije uzastopne rate, Prodavac zadržava pravo da raskine ugovor i zadrži uplaćeni iznos do tog trenutka.

Član 7. Mjerodavno pravo i rešavanje sporova
Ovaj ugovor je sačinjen u skladu sa austrijskim pravom, posebno u skladu sa odredbama Opšteg građanskog zakonika (Allgemeines Bürgerliches Gesetzbuch - ABGB). Sve sporove koji proisteknu iz ovog ugovora strane će rešavati sporazumno, a u slučaju neuspjeha, nadležan je sud u Beču.

Član 8. Završne odredbe
1. Sve izmjene i dopune ovog ugovora važeće su samo ako su sačinjene u pisanom obliku i potpisane od strane obe ugovorne strane.
2. Ovaj ugovor stupa na snagu danom potpisivanja.
3. Ugovor je sačinjen u dva istovjetna primjerka, od kojih svaka strana zadržava po jedan.

Datum potpisivanja: {datum}

Potpis kupca: _________________________';
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
