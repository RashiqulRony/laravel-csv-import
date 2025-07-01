<?php

namespace Rashiqulrony\CSVUpload;

use http\Client\Request;

class CSVUpload
{
    public static function upload($file)
    {
        $path = $file->getRealPath();
        $chunkSize = config('csvimport.chunk_size');

        $header = null;
        $dataChunk = [];

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
            $tempPath = storage_path('app/temp_utf8.csv');
            file_put_contents($tempPath, $utf8Content);

            if (($handle = fopen($tempPath, 'r')) !== false) {
                while (($row = fgetcsv($handle, 200, ',')) !== false) {
                    $row = array_map(function ($value) {
                        return trim(mb_convert_encoding($value, 'UTF-8', 'UTF-8'));
                    }, $row);

                    // Set and clean header
                    if (!$header) {
                        $header = array_map(function ($value) {
                            return strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $value)));
                        }, $row);
                        continue;
                    }

                    if (count($row) !== count($header)) {
                        continue;
                    }

                    $rowData = array_combine($header, $row);

                    if (empty($rowData)) {
                        continue;
                    }

                    $dataChunk[] = $rowData;

                    if (count($dataChunk) === $chunkSize) {
                        fclose($handle);
                        unlink($tempPath);
                        return $dataChunk;
                    }
                }

                fclose($handle);
                unlink($tempPath);

                if (!empty($dataChunk)) {
                    return $dataChunk;
                } else {
                    return [
                        'status' => false,
                        'message' => 'File Error: ' . 'File will be UTF-8 format.'
                    ];
                }
            }

            return [
                'status' => false,
                'message' => 'Upload failed: ' . 'Could not open the file.'
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => 'Upload failed: ' . $exception->getMessage()
            ];
        }
    }
}
