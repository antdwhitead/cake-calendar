<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CakeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CakeDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'cake_type',
        'names',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'cake_type' => CakeType::class,
            'names' => 'array',
        ];
    }
}
