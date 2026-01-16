<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'url',
        'event',
        'secret',
        'active',
        'retry_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }

    public function generateSignature($payload)
    {
        return 'v1=' . hash_hmac('sha256', json_encode($payload), $this->secret);
    }
}
