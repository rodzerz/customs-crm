<?php

namespace App\Services;

use App\Models\DocumentFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    const SIGNED_URL_EXPIRATION_HOURS = 24;

    public static function storeDocument($file, $documentId)
    {
        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = "documents/{$documentId}/{$fileName}";

        Storage::disk('private')->put($path, $file->getContent());

        $token = Str::random(60);

        $documentFile = DocumentFile::create([
            'document_id' => $documentId,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'signed_url_token' => $token,
            'signed_url_expires_at' => now()->addHours(self::SIGNED_URL_EXPIRATION_HOURS),
        ]);

        return $documentFile;
    }

    public static function getSignedUrl(DocumentFile $documentFile)
    {
        if (!$documentFile->isSignedUrlValid()) {
            // Regenerate expired URL
            $documentFile->update([
                'signed_url_token' => Str::random(60),
                'signed_url_expires_at' => now()->addHours(self::SIGNED_URL_EXPIRATION_HOURS),
            ]);
        }

        return route('documents.download', $documentFile->signed_url_token);
    }

    public static function getFileByToken($token)
    {
        return DocumentFile::where('signed_url_token', $token)
            ->where('signed_url_expires_at', '>', now())
            ->first();
    }

    public static function downloadFile(DocumentFile $documentFile)
    {
        $documentFile->update(['downloaded_at' => now()]);

        return Storage::disk('private')->download($documentFile->file_path);
    }
}
