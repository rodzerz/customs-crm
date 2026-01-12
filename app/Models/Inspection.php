<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'external_id',
        'case_id',
        'type',       // document, RTG, physical
        'status',     // released, hold, reject
        'comment',
        'performed_at',
    ];

    // Relācija uz case
    public function case()
    {
        return $this->belongsTo(\App\Models\CaseModel::class, 'case_id');
    }
}
