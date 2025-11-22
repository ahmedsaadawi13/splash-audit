<?php
// FILE: /app/helpers/FileUpload.php

class FileUpload {
    private $allowedExtensions;
    private $maxSize;
    private $uploadPath;
    private $errors = [];

    public function __construct() {
        $this->allowedExtensions = explode(',', ALLOWED_EXTENSIONS);
        $this->maxSize = MAX_UPLOAD_SIZE;
        $this->uploadPath = UPLOAD_PATH;
    }

    /**
     * Upload file
     */
    public function upload($file, $subFolder = 'evidence') {
        // Reset errors
        $this->errors = [];

        // Validate file
        if (!$this->validate($file)) {
            return false;
        }

        // Create upload directory if not exists
        $uploadDir = $this->uploadPath . $subFolder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $storedName = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $storedName;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'original_name' => $file['name'],
                'stored_name' => $storedName,
                'file_path' => $filePath,
                'relative_path' => $subFolder . '/' . $storedName,
                'mime_type' => $file['type'],
                'size_bytes' => $file['size']
            ];
        }

        $this->errors[] = 'Failed to upload file.';
        return false;
    }

    /**
     * Validate file
     */
    private function validate($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'No file was uploaded.';
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'File upload error: ' . $file['error'];
            return false;
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed size of ' . ($this->maxSize / 1048576) . 'MB.';
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions);
            return false;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/vnd.ms-excel',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            $this->errors[] = 'Invalid file type.';
            return false;
        }

        return true;
    }

    /**
     * Delete file
     */
    public function delete($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Get errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error
     */
    public function getError() {
        return $this->errors[0] ?? '';
    }

    /**
     * Download file
     */
    public static function download($filePath, $originalName) {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found.');
        }

        // Get file info
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Output file
        readfile($filePath);
        exit;
    }

    /**
     * Calculate folder size
     */
    public static function getFolderSize($path) {
        $size = 0;

        if (!is_dir($path)) {
            return 0;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}
