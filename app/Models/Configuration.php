<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configurations';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'autorisation_bancaire',
    ];

    protected $casts = [
        'autorisation_bancaire' => 'decimal:2',
    ];

    /**
     * Get or create the singleton configuration row (id = 1).
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['autorisation_bancaire' => 200000.00]
        );
    }
}
