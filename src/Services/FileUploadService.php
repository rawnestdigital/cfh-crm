<?php

namespace App\Services;

use Exception;

/**
 * File Upload Service
 * Secure file upload handling with validation
 */
class FileUploadService
{
    private const UPLOAD_DIR = '/var/www/secure_uploads';
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];

    private const MIME_SIGNATURES = [
        'image/jpeg' => "\xFF\xD8\xFF",
        'image/png' => "\x89PNG\r\n\x1a\n",
        'image/gif' => ['GIF87a', 'GIF89a'],
        'image/webp' => "RIFF",
        'application/pdf' => "%PDF",
    ];

    /**
     * Handle file upload safely
     */
    public static function upload(array $file, array $options = []): array
    {
        try {
            // Validate file exists
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                throw new Exception('Invalid file upload');
            }

            // Validate file size
            if ($file['size'] > self::MAX_SIZE) {
                throw new Exception('File size exceeds limit');
            }

            // Get MIME type
            $mimeType = mime_content_type($file['tmp_name']);
            if (!isset(self::ALLOWED_TYPES[$mimeType])) {
                throw new Exception('File type not allowed');
            }

            // Validate file content
            if (!self::validateFileContent($file['tmp_name'], $mimeType)) {
                throw new Exception('File content validation failed');
            }

            // Generate unique filename
            $extension = self::ALLOWED_TYPES[$mimeType];
            $filename = bin2hex(random_bytes(16)) . '.' . $extension;
            $uploadDir = $options['upload_dir'] ?? self::UPLOAD_DIR;
            $filepath = $uploadDir . '/' . $filename;

            // Create directory if needed
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0750, true);
            }

            // Save current umask
            $oldUmask = umask(0077);

            // Move file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }

            // Restore umask
            umask($oldUmask);

            // Set file permissions (read-only for web server)
            chmod($filepath, 0644);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'mime_type' => $mimeType,
                'size' => $file['size'],
                'url' => '/downloads/' . $filename,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate file content by magic bytes
     */
    private static function validateFileContent(string $filepath, string $mimeType): bool
    {
        if (!isset(self::MIME_SIGNATURES[$mimeType])) {
            return true; // Skip validation for unknown types
        }

        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 12);
        fclose($handle);

        $signature = self::MIME_SIGNATURES[$mimeType];

        // Handle multiple possible signatures
        if (is_array($signature)) {
            foreach ($signature as $sig) {
                if (strpos($header, $sig) === 0) {
                    return true;
                }
            }
            return false;
        }

        return strpos($header, $signature) === 0;
    }

    /**
     * Download file safely
     */
    public static function download(string $filename): void
    {
        try {
            // Prevent directory traversal
            $filename = basename($filename);

            if (empty($filename)) {
                throw new Exception('Invalid filename');
            }

            $filepath = self::UPLOAD_DIR . '/' . $filename;

            // Check if file exists
            if (!file_exists($filepath)) {
                http_response_code(404);
                die('File not found');
            }

            // Check if file is within upload directory
            if (realpath($filepath) === false || strpos(realpath($filepath), realpath(self::UPLOAD_DIR)) !== 0) {
                http_response_code(403);
                die('Access denied');
            }

            // Get file info
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filepath);
            finfo_close($finfo);

            // Set headers
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Pragma: no-cache');
            header('Expires: 0');

            // Read and output file
            readfile($filepath);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            die('Download error: ' . $e->getMessage());
        }
    }

    /**
     * Delete file safely
     */
    public static function delete(string $filename): array
    {
        try {
            $filename = basename($filename);
            $filepath = self::UPLOAD_DIR . '/' . $filename;

            if (!file_exists($filepath)) {
                throw new Exception('File not found');
            }

            if (!unlink($filepath)) {
                throw new Exception('Failed to delete file');
            }

            return ['success' => true, 'message' => 'File deleted successfully'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
