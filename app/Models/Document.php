<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'external_id',
        'case_id',
        'type',
        'description',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // Relācija uz Case
    public function case()
    {
        return $this->belongsTo(\App\Models\CaseModel::class, 'case_id');
    }

    // Document files (multiple versions/uploads)
    public function files()
    {
        return $this->hasMany(DocumentFile::class);
    }

    public function getLatestFile()
    {
        return $this->files()->latest()->first();
    }
}
