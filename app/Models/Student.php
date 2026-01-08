<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasUuids;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'postal_code',
        'city',
        'country',
        'phone',
        'email',
        'id_document_number',
        'entity_type',
        'payment_method',
        'package_type',
        'company_name',
        'vat_number',
        'company_address',
        'company_postal_code',
        'company_city',
        'company_country',
        'company_registration',
        'status',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getPaidInstallmentsCountAttribute(): int
    {
        return $this->payments()->where('status', 'paid')->count();
    }

    public function getTotalInstallmentsAttribute(): int
    {
        return $this->payment_method === 'installments' ? 3 : 1;
    }

    public function getPaymentStatusAttribute(): string
    {
        $paid = $this->paid_installments_count;
        $total = $this->total_installments;
        return "{$paid}/{$total}";
    }
}
