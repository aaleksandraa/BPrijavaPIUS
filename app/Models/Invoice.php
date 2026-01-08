<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'invoice_number',
        'invoice_date',
        'payment_date',
        'description',
        'net_amount',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'installment_number',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'payment_date' => 'date',
            'net_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');

        // Get all used invoice numbers for this year
        $usedNumbers = self::whereYear('created_at', $year)
            ->pluck('invoice_number')
            ->map(function ($num) {
                $parts = explode('/', $num);
                return (int) end($parts);
            })
            ->toArray();

        // Find the first available number (starting from 1)
        $number = 1;
        while (in_array($number, $usedNumbers)) {
            $number++;
        }

        return sprintf('%s/%03d', $year, $number);
    }

    public static function calculateFromGross(float $grossAmount, float $vatRate = 20): array
    {
        $netAmount = $grossAmount / (1 + ($vatRate / 100));
        $vatAmount = $grossAmount - $netAmount;

        return [
            'net_amount' => round($netAmount, 2),
            'vat_amount' => round($vatAmount, 2),
            'total_amount' => round($grossAmount, 2),
        ];
    }
}
