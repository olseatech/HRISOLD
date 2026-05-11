<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Document201 extends Model
{
    public const CATEGORIES = [
        'PDS', 'Appointment', 'Service Record', 'Clearance',
        'Certificate', 'ID', 'Others',
    ];

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    public const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

    public function categories(): array { return self::CATEGORIES; }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT d.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_documents_201 d
             JOIN hris_employees e ON e.id = d.employee_id
             WHERE d.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function search(string $query, string $empId, string $category, int $page, int $perPage): array
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $category, $params);
        $offset = ($page - 1) * $perPage;
        return $this->fetchAll(
            "SELECT d.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_documents_201 d
             JOIN hris_employees e ON e.id = d.employee_id
             {$where}
             ORDER BY d.created_at DESC, e.last_name ASC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );
    }

    public function countSearch(string $query, string $empId, string $category): int
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $category, $params);
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM hris_documents_201 d
             JOIN hris_employees e ON e.id = d.employee_id
             {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function countTotal(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS cnt FROM hris_documents_201');
        return (int) ($row['cnt'] ?? 0);
    }

    public function countEmployeesWithDocuments(): int
    {
        $row = $this->fetchOne('SELECT COUNT(DISTINCT employee_id) AS cnt FROM hris_documents_201');
        return (int) ($row['cnt'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function create(array $d): int
    {
        $cat = trim((string) ($d['doc_category'] ?? ''));
        $this->execute(
            'INSERT INTO hris_documents_201
                (employee_id, doc_category, title, description,
                 original_filename, stored_filename, file_size, mime_type, uploaded_by)
             VALUES
                (:employee_id, :doc_category, :title, :description,
                 :original_filename, :stored_filename, :file_size, :mime_type, :uploaded_by)',
            [
                'employee_id'       => (int) ($d['employee_id'] ?? 0),
                'doc_category'      => in_array($cat, self::CATEGORIES, true) ? $cat : 'Others',
                'title'             => trim((string) ($d['title'] ?? '')),
                'description'       => $this->n($d['description'] ?? null),
                'original_filename' => trim((string) ($d['original_filename'] ?? '')),
                'stored_filename'   => trim((string) ($d['stored_filename'] ?? '')),
                'file_size'         => isset($d['file_size']) ? (int) $d['file_size'] : null,
                'mime_type'         => $this->n($d['mime_type'] ?? null),
                'uploaded_by'       => isset($d['uploaded_by']) ? (int) $d['uploaded_by'] : null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM hris_documents_201 WHERE id = :id', ['id' => $id]);
    }

    public function employeeOptions(): array
    {
        return $this->fetchAll(
            "SELECT id, employee_code, first_name, last_name
             FROM hris_employees
             ORDER BY last_name ASC, first_name ASC"
        );
    }

    // -------------------------------------------------------------------------
    // File path helper
    // -------------------------------------------------------------------------

    public static function storageDir(int $employeeId): string
    {
        return rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\')
            . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR . 'documents'
            . DIRECTORY_SEPARATOR . $employeeId;
    }

    public static function storagePath(int $employeeId, string $storedFilename): string
    {
        return static::storageDir($employeeId) . DIRECTORY_SEPARATOR . $storedFilename;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildWhere(string $query, string $empId, string $category, array &$params): string
    {
        $conditions = [];

        if ($query !== '') {
            $conditions[] = '(e.employee_code LIKE :q1 OR e.first_name LIKE :q2 OR e.last_name LIKE :q3 OR d.title LIKE :q4)';
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
            $params['q4'] = '%' . $query . '%';
        }

        if ($empId !== '') {
            $conditions[] = 'd.employee_id = :emp_id';
            $params['emp_id'] = (int) $empId;
        }

        if ($category !== '') {
            $conditions[] = 'd.doc_category = :category';
            $params['category'] = $category;
        }

        return $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
    }

    private function n(?string $v): ?string
    {
        $v = trim((string) $v);
        return $v !== '' ? $v : null;
    }
}
