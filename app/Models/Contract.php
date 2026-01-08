<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'contract_number',
        'contract_type',
        'contract_content',
        'signature_data',
        'signed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public static function generateContractNumber(): string
    {
        $year = date('Y');
        $lastContract = self::whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastContract && $lastContract->contract_number) {
            $parts = explode('-', $lastContract->contract_number);
            $number = (int) end($parts) + 1;
        } else {
            $number = 1;
        }

        return sprintf('PIUS-%s-%04d', $year, $number);
    }
}
