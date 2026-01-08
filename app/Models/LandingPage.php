<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'intro_text',
        'package_ids',
        'is_active',
    ];

    protected $casts = [
        'package_ids' => 'array',
        'is_active' => 'boolean',
    ];

    public function getPackagesAttribute()
    {
        return Package::whereIn('id', $this->package_ids ?? [])->get();
    }
}
