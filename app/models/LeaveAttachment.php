<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class LeaveAttachment extends Model
{
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    public const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

    public function findByRequest(int $leaveRequestId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_leave_attachments WHERE leave_request_id = :id ORDER BY id ASC',
            ['id' => $leaveRequestId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM hris_leave_attachments WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO hris_leave_attachments
                (leave_request_id, original_filename, stored_filename, file_size, mime_type, uploaded_by)
             VALUES
                (:leave_request_id, :original_filename, :stored_filename, :file_size, :mime_type, :uploaded_by)',
            [
                'leave_request_id' => (int) ($data['leave_request_id'] ?? 0),
                'original_filename' => trim((string) ($data['original_filename'] ?? '')),
                'stored_filename'   => trim((string) ($data['stored_filename'] ?? '')),
                'file_size'         => isset($data['file_size']) ? (int) $data['file_size'] : null,
                'mime_type'         => isset($data['mime_type']) && $data['mime_type'] !== '' ? $data['mime_type'] : null,
                'uploaded_by'       => isset($data['uploaded_by']) ? (int) $data['uploaded_by'] : null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function deleteById(int $id): bool
    {
        $row = $this->find($id);
        if ($row) {
            $path = static::storagePath((int) ($row['leave_request_id'] ?? 0), (string) ($row['stored_filename'] ?? ''));
            if ($path !== '' && file_exists($path)) {
                @unlink($path);
            }
        }
        return $this->execute('DELETE FROM hris_leave_attachments WHERE id = :id', ['id' => $id]);
    }

    public static function storageDir(int $leaveRequestId): string
    {
        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\')
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'leave'
            . DIRECTORY_SEPARATOR . $leaveRequestId;
    }

    public static function storagePath(int $leaveRequestId, string $storedFilename): string
    {
        return static::storageDir($leaveRequestId) . DIRECTORY_SEPARATOR . $storedFilename;
    }
}
