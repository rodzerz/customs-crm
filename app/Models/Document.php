<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'external_id',
        'case_id',
        'type',        // e.g., invoice, permit, declaration
        'file_path',   // vai URL uz failu
        'uploaded_at',
    ];

    // Relācija uz Case
    public function case()
    {
        return $this->belongsTo(\App\Models\CaseModel::class, 'case_id');
    }
}
