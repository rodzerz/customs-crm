<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseEvent extends Model
{
    protected $table = 'case_events';

    protected $fillable = [
        'case_id',
        'user_id',
        'event_type',
        'data',
        'description',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
