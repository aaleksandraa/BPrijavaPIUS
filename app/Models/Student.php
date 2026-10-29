<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_type', 'slug');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getPaidInstallmentsCountAttribute(): int
    {
        if ($this->payment_method === 'installments') {
            return count($this->paidInstallmentNumbers());
        }

        return $this->invoices->where('status', 'paid')->isNotEmpty()
            || $this->payments->whereNull('invoice_id')->where('status', 'paid')->isNotEmpty() ? 1 : 0;
    }

    public function installmentNumbers(): array
    {
        if ($this->payment_method !== 'installments') {
            return [1];
        }
        $numbers = $this->package?->installments->pluck('installment_number')
            ->map(fn ($number) => (int) $number)->unique()->sort()->values()->all();

        // Older registrations without an explicit payment plan used three rates.
        return $numbers ?: [1, 2, 3];
    }

    public function paidInstallmentNumbers(): array
    {
        return $this->invoices->where('status', 'paid')->pluck('installment_number')
            ->merge($this->payments->whereNull('invoice_id')->where('status', 'paid')->pluck('installment_number'))
            ->filter(fn ($number) => in_array((int) $number, $this->installmentNumbers(), true))
            ->map(fn ($number) => (int) $number)->unique()->sort()->values()->all();
    }

    public function getTotalInstallmentsAttribute(): int
    {
        return count($this->installmentNumbers());
    }

    public function paymentSummary(): array
    {
        // Every student ID is one enrollment. Never aggregate money by email.
        // Legacy manual payments have no invoice; linked payments are already
        // represented by their invoice and must not be counted twice.
        $paid = round((float) $this->invoices->where('status', 'paid')->sum('total_amount')
            + (float) $this->payments->whereNull('invoice_id')->where('status', 'paid')->sum('amount'), 2);
        $total = (float) ($this->package?->discount_price ?? $this->package?->price ?? 0);

        return [
            'paid_installments_count' => $this->paid_installments_count,
            'total_installments' => $this->total_installments,
            'payment_status' => $this->payment_status,
            'paid_amount' => $paid,
            'course_total' => $total,
            'remaining_amount' => max(0, round($total - $paid, 2)),
        ];
    }

    public function getPaymentStatusAttribute(): string
    {
        $paid = $this->paid_installments_count;
        $total = $this->total_installments;

        return "{$paid}/{$total}";
    }
}
