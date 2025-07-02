<?php

namespace Rashiqulrony\CSVUpload;

use http\Client\Request;

class CSVUpload
{
    public static function upload($file)
    {
        $path = $file->getRealPath();
        $chunkSize = config('csvimport.chunk_size', 1000); // Default to 1000 if not set

        $header = null;
        $dataChunks = [];

        try {
            // Detect encoding
            $originalContent = file_get_contents($path);
            $encoding = mb_detect_encoding($originalContent, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1', 'Windows-1252'], true);

            if ($encoding === false) {
                throw new \Exception('Unable to detect file encoding.');
            }

            // Convert to UTF-8
            $utf8Content = mb_convert_encoding($originalContent, 'UTF-8', $encoding);

            // Save to temporary UTF-8 file
            $tempPath = storage_path('app/temp_utf8_' . uniqid() . '.csv');
            file_put_contents($tempPath, $utf8Content);

            if (($handle = fopen($tempPath, 'r')) === false) {
                return [
                    'status' => false,
                    'message' => 'Upload failed: Could not open the file.'
                ];
            }

            $dataChunk = [];
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $row = array_map(fn($value) => trim(mb_convert_encoding($value, 'UTF-8', 'UTF-8')), $row);

                if (!$header) {
                    $header = array_map(fn($value) => strtolower(trim($value)), $row);

                    // Remove UTF-8 BOM from first column only
                    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

                    continue;
                }

                if (count($row) !== count($header)) {
                    continue; // Skip malformed row
                }

                $dataChunk[] = array_combine($header, $row);

                // When chunk size reached, store and reset
                if (count($dataChunk) >= $chunkSize) {
                    $dataChunks[] = $dataChunk;
                    $dataChunk = [];
                }
            }

            // Add the last chunk if it has remaining data
            if (!empty($dataChunk)) {
                $dataChunks[] = $dataChunk;
            }

            fclose($handle);
            unlink($tempPath);

            if (empty($dataChunks)) {
                return [
                    'status' => false,
                    'message' => 'File Error: Could not extract data.'
                ];
            }

            return $dataChunks;

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }
}
