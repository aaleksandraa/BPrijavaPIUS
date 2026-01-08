<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'template_type',
        'package_type',
        'content',
    ];

    public function generateContent(array $data): string
    {
        $replacements = [
            '{datum}' => now()->format('d.m.Y'),
            '{ime}' => $data['ime'] ?? '',
            '{prezime}' => $data['prezime'] ?? '',
            '{adresa}' => $data['adresa'] ?? '',
            '{postanskiBroj}' => $data['postanskiBroj'] ?? '',
            '{mjesto}' => $data['mjesto'] ?? '',
            '{drzava}' => $data['drzava'] ?? '',
            '{brojLicnogDokumenta}' => $data['brojLicnogDokumenta'] ?? '',
            '{telefon}' => $data['telefon'] ?? '',
            '{email}' => $data['email'] ?? '',
            '{nazivFirme}' => $data['nazivFirme'] ?? '',
            '{pdvBroj}' => $data['pdvBroj'] ?? '',
            '{adresaFirme}' => $data['adresaFirme'] ?? '',
            '{postanskiBrojFirme}' => $data['postanskiBrojFirme'] ?? '',
            '{mjestoFirme}' => $data['mjestoFirme'] ?? '',
            '{drzavaFirme}' => $data['drzavaFirme'] ?? '',
            '{registracijaFirme}' => $data['registracijaFirme'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->content);
    }
}
