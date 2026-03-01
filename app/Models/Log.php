<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Log extends Model
{
    protected $table = 'logs';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'timestamp',
        'action',
        'category',
        'severity',
        'message',
        'description',
        'entity_type',
        'entity_id',
        'user_id',
        'changes',
        'metadata',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'changes' => 'array',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Log $log) {
            if (!$log->id) {
                $log->id = 'log_' . now()->format('YmdHis') . '_' . Str::random(5);
            }
            if (!$log->timestamp) {
                $log->timestamp = now();
            }
            if (!$log->severity) {
                $log->severity = 'info';
            }
            if (!$log->metadata) {
                $log->metadata = [];
            }
        });

        static::created(function () {
            // Cap at 10,000 entries — trim oldest when exceeded
            $count = Log::count();
            if ($count > 10000) {
                $excess = $count - 10000;
                Log::orderBy('timestamp', 'asc')
                    ->limit($excess)
                    ->delete();
            }
        });
    }
}
