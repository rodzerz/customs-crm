<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    protected $table = 'document_files';

    protected $fillable = [
        'document_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'signed_url_token',
        'signed_url_expires_at',
        'downloaded_at',
    ];

    protected $casts = [
        'signed_url_expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function isSignedUrlValid()
    {
        return $this->signed_url_expires_at && $this->signed_url_expires_at->isFuture();
    }
}
