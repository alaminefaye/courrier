<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Upload un fichier pour un courrier
     */
    public function uploadFile(UploadedFile $file, $courrier, bool $encrypt = false): string
    {
        $directory = 'courriers/' . ($courrier instanceof \App\Models\CourrierEntrant ? 'entrants' : 'sortants');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs($directory, $filename, 'private');
        
        // Si le fichier doit être crypté (confidentiel/secret défense)
        if ($encrypt) {
            $this->encryptFile($path);
        }
        
        return $path;
    }

    /**
     * Supprime un fichier
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->delete($path);
        }
        
        return false;
    }

    /**
     * Récupère l'URL publique du fichier (si nécessaire)
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk('private')->url($path);
    }

    /**
     * Crypte un fichier
     */
    public function encryptFile(string $path): string
    {
        $content = Storage::disk('private')->get($path);
        $encrypted = Crypt::encrypt($content);
        
        Storage::disk('private')->put($path, $encrypted);
        
        return $path;
    }

    /**
     * Décrypte un fichier
     */
    public function decryptFile(string $path): string
    {
        $encrypted = Storage::disk('private')->get($path);
        
        try {
            $decrypted = Crypt::decrypt($encrypted);
            return $decrypted;
        } catch (\Exception $e) {
            // Si le fichier n'est pas crypté, retourner le contenu tel quel
            return $encrypted;
        }
    }

    /**
     * Télécharge un fichier décrypté
     */
    public function downloadFile(string $path, string $originalName = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $content = $this->decryptFile($path);
        
        return Storage::disk('private')->download($path, $originalName);
    }
}

