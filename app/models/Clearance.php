<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Clearance extends Model
{
    private const CLEARANCE_TYPES = [
        'Resignation', 'Retirement', 'End of Contract', 'Transfer', 'Others',
    ];

    private const STATUSES = ['Pending', 'Approved', 'Rejected'];

    private const ITEM_STATUSES = ['Pending', 'Cleared', 'Not Applicable'];

    private const DEFAULT_OFFICES = [
        ['office_name' => 'Finance / Accounting',   'sort_order' => 1],
        ['office_name' => 'Property / Supply',       'sort_order' => 2],
        ['office_name' => 'Information Technology',  'sort_order' => 3],
        ['office_name' => 'Human Resources',         'sort_order' => 4],
        ['office_name' => 'Immediate Supervisor',    'sort_order' => 5],
        ['office_name' => 'Administration',          'sort_order' => 6],
    ];

    public function clearanceTypes(): array { return self::CLEARANCE_TYPES; }
    public function statuses(): array       { return self::STATUSES; }
    public function itemStatuses(): array   { return self::ITEM_STATUSES; }

    // -------------------------------------------------------------------------
    // Main clearance queries
    // -------------------------------------------------------------------------

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT c.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last,
                    e.employment_status AS emp_status
             FROM hris_clearances c
             JOIN hris_employees e ON e.id = c.employee_id
             WHERE c.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByEmployee(int $employeeId): array
    {
        return $this->fetchAll(
            'SELECT c.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_clearances c
             JOIN hris_employees e ON e.id = c.employee_id
             WHERE c.employee_id = :eid
             ORDER BY c.request_date DESC',
            ['eid' => $employeeId]
        );
    }

    public function search(string $query, string $empId, string $status, int $page, int $perPage): array
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $status, $params);
        $offset = ($page - 1) * $perPage;
        return $this->fetchAll(
            "SELECT c.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_clearances c
             JOIN hris_employees e ON e.id = c.employee_id
             {$where}
             ORDER BY c.request_date DESC, e.last_name ASC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );
    }

    public function countSearch(string $query, string $empId, string $status): int
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $status, $params);
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM hris_clearances c
             JOIN hris_employees e ON e.id = c.employee_id
             {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function countByStatus(string $status): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM hris_clearances WHERE status = :s',
            ['s' => $status]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Items
    // -------------------------------------------------------------------------

    public function getItems(int $clearanceId): array
    {
        return $this->fetchAll(
            'SELECT * FROM hris_clearance_items
             WHERE clearance_id = :cid
             ORDER BY sort_order ASC, id ASC',
            ['cid' => $clearanceId]
        );
    }

    public function updateItemStatus(int $itemId, string $status, ?string $remarks): bool
    {
        $clearedAt = $status === 'Cleared' ? date('Y-m-d H:i:s') : null;
        return $this->execute(
            'UPDATE hris_clearance_items
             SET status = :status, cleared_at = :cleared_at, remarks = :remarks
             WHERE id = :id',
            ['status' => $status, 'cleared_at' => $clearedAt, 'remarks' => $remarks, 'id' => $itemId]
        );
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO hris_clearances
                (employee_id, clearance_type, purpose, request_date, status, remarks)
             VALUES
                (:employee_id, :clearance_type, :purpose, :request_date, :status, :remarks)',
            $this->mainParams($d)
        );
        $id = (int) $this->db->lastInsertId();

        // Seed default clearance items
        foreach (self::DEFAULT_OFFICES as $office) {
            $this->execute(
                'INSERT INTO hris_clearance_items (clearance_id, office_name, sort_order)
                 VALUES (:cid, :office, :sort)',
                ['cid' => $id, 'office' => $office['office_name'], 'sort' => $office['sort_order']]
            );
        }

        return $id;
    }

    public function updateById(int $id, array $d): bool
    {
        return $this->execute(
            'UPDATE hris_clearances SET
                clearance_type = :clearance_type,
                purpose        = :purpose,
                request_date   = :request_date,
                remarks        = :remarks
             WHERE id = :id',
            [
                'clearance_type' => $d['clearance_type'] ?? '',
                'purpose'        => $this->n($d['purpose'] ?? null),
                'request_date'   => $d['request_date'] ?? '',
                'remarks'        => $this->n($d['remarks'] ?? null),
                'id'             => $id,
            ]
        );
    }

    public function approve(int $id, int $userId): bool
    {
        return $this->execute(
            'UPDATE hris_clearances
             SET status = :s, processed_by = :by, processed_at = :at
             WHERE id = :id',
            ['s' => 'Approved', 'by' => $userId, 'at' => date('Y-m-d H:i:s'), 'id' => $id]
        );
    }

    public function reject(int $id, int $userId, ?string $remarks): bool
    {
        return $this->execute(
            'UPDATE hris_clearances
             SET status = :s, processed_by = :by, processed_at = :at, remarks = :remarks
             WHERE id = :id',
            ['s' => 'Rejected', 'by' => $userId, 'at' => date('Y-m-d H:i:s'), 'remarks' => $remarks, 'id' => $id]
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM hris_clearances WHERE id = :id', ['id' => $id]);
    }

    public function employeeOptions(): array
    {
        return $this->fetchAll(
            "SELECT id, employee_code, first_name, last_name
             FROM hris_employees
             WHERE employment_status NOT IN ('Resigned','Terminated')
             ORDER BY last_name ASC, first_name ASC"
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildWhere(string $query, string $empId, string $status, array &$params): string
    {
        $conditions = [];

        if ($query !== '') {
            $conditions[] = '(e.employee_code LIKE :q1 OR e.first_name LIKE :q2 OR e.last_name LIKE :q3)';
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
        }

        if ($empId !== '') {
            $conditions[] = 'c.employee_id = :emp_id';
            $params['emp_id'] = (int) $empId;
        }

        if ($status !== '') {
            $conditions[] = 'c.status = :status';
            $params['status'] = $status;
        }

        return $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
    }

    private function n(?string $v): ?string
    {
        $v = trim((string) $v);
        return $v !== '' ? $v : null;
    }

    private function mainParams(array $d): array
    {
        $type = trim((string) ($d['clearance_type'] ?? ''));
        return [
            'employee_id'    => (int) ($d['employee_id'] ?? 0),
            'clearance_type' => in_array($type, self::CLEARANCE_TYPES, true) ? $type : 'Others',
            'purpose'        => $this->n($d['purpose'] ?? null),
            'request_date'   => trim((string) ($d['request_date'] ?? '')),
            'status'         => 'Pending',
            'remarks'        => $this->n($d['remarks'] ?? null),
        ];
    }
}
