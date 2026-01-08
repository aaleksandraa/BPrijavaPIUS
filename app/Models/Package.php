<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'payment_type',
        'description',
        'image_url',
        'is_active',
        'show_on_landing',
        'has_contract',
        'duration_days',
        'features',
        'contract_template',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'show_on_landing' => 'boolean',
            'has_contract' => 'boolean',
            'features' => 'array',
        ];
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PackageInstallment::class)->orderBy('installment_number');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'package_type', 'slug');
    }
}
