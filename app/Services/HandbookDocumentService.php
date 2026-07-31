<?php

namespace App\Services;

use App\Models\Handbook;
use App\Support\AttachmentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class HandbookDocumentService
{
    /**
     * Store an uploaded handbook document and return metadata.
     *
     * @return array{file_path: string, file_name: string, file_size: int}
     */
    public function storeUploadedFile(UploadedFile $file): array
    {
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'handbook';
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $fileName = time().'_'.$safeName.'.'.$extension;
        $disk = (string) config('filesystems.attachments_disk', 'local');
        $filePath = $file->storeAs('handbooks', $fileName, $disk);

        return [
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => (int) $file->getSize(),
        ];
    }

    public function deleteStoredFile(?string $filePath): void
    {
        if (! $filePath) {
            return;
        }

        // Legacy rows may store external URLs in attachment; never delete remote URLs as paths.
        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return;
        }

        try {
            AttachmentStorage::disk()->delete($filePath);
        } catch (\Throwable $e) {
            Log::warning('Failed to delete handbook file: '.$e->getMessage(), ['path' => $filePath]);
        }
    }

    public function extractTextFromStoredPdf(Handbook $handbook): string
    {
        if (! $handbook->file_path) {
            return '';
        }

        $extension = strtolower(pathinfo($handbook->file_name ?: $handbook->file_path, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return '';
        }

        try {
            $disk = AttachmentStorage::disk();
            if (! $disk->exists($handbook->file_path)) {
                return '';
            }

            $binary = $disk->get($handbook->file_path);
            if (! is_string($binary) || $binary === '') {
                return '';
            }

            $parser = new PdfParser;
            $pdf = $parser->parseContent($binary);
            $text = trim((string) $pdf->getText());

            // Keep indexing payloads bounded.
            return Str::limit($text, 50000, '');
        } catch (\Throwable $e) {
            Log::warning('Handbook PDF text extraction failed: '.$e->getMessage(), [
                'handbook_id' => $handbook->id,
                'path' => $handbook->file_path,
            ]);

            return '';
        }
    }

    /**
     * Full text used by Nexus AI indexing (typed content + extracted PDF text).
     */
    public function indexableText(Handbook $handbook): string
    {
        $parts = array_filter([
            trim((string) $handbook->content),
            $this->extractTextFromStoredPdf($handbook),
        ]);

        return trim(implode("\n\n", $parts));
    }
}
