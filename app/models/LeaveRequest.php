<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class LeaveRequest extends Model
{
    public function countByStatus(string $status): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_leave_requests
             WHERE status = :status',
            ['status' => $status]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function listFiltered(string $status = '', string $query = '', int $page = 1, int $perPage = 10, ?int $employeeId = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where = [];

        if ($employeeId !== null) {
            $where[] = 'lr.employee_id = :employee_id';
            $params['employee_id'] = $employeeId;
        }

        if ($status !== '') {
            $where[] = 'lr.status = :status';
            $params['status'] = $status;
        }

        if ($query !== '') {
            $where[] = '(e.employee_code LIKE :query OR e.first_name LIKE :query OR e.last_name LIKE :query OR lt.type_name LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        $whereClause = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        return $this->fetchAll(
            'SELECT lr.id, lr.employee_id, lr.leave_type_id, lr.start_date, lr.end_date, lr.total_days, lr.reason,
                    lr.status, lr.review_remarks, lr.created_at,
                    e.employee_code, e.first_name, e.last_name,
                    lt.type_name
             FROM hris_leave_requests lr
             INNER JOIN hris_employees e ON e.id = lr.employee_id
             INNER JOIN hris_leave_types lt ON lt.id = lr.leave_type_id
             ' . $whereClause . '
             ORDER BY lr.id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countFiltered(string $status = '', string $query = '', ?int $employeeId = null): int
    {
        $params = [];
        $where = [];

        if ($employeeId !== null) {
            $where[] = 'lr.employee_id = :employee_id';
            $params['employee_id'] = $employeeId;
        }

        if ($status !== '') {
            $where[] = 'lr.status = :status';
            $params['status'] = $status;
        }

        if ($query !== '') {
            $where[] = '(e.employee_code LIKE :query OR e.first_name LIKE :query OR e.last_name LIKE :query OR lt.type_name LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        $whereClause = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_leave_requests lr
             INNER JOIN hris_employees e ON e.id = lr.employee_id
             INNER JOIN hris_leave_types lt ON lt.id = lr.leave_type_id
             ' . $whereClause,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    public function leaveTypes(): array
    {
        return $this->fetchAll(
            'SELECT id, type_name FROM hris_leave_types WHERE is_active = 1 ORDER BY type_name ASC'
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_leave_requests WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function createRequest(array $data): int
    {
        $this->execute(
            'INSERT INTO hris_leave_requests (
                employee_id, leave_type_id, start_date, end_date, total_days, reason, status
             ) VALUES (
                :employee_id, :leave_type_id, :start_date, :end_date, :total_days, :reason, :status
             )',
            [
                'employee_id' => (int) $data['employee_id'],
                'leave_type_id' => (int) $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_days' => (float) $data['total_days'],
                'reason' => $data['reason'] ?: null,
                'status' => 'Pending',
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function hasOverlap(int $employeeId, string $startDate, string $endDate): bool
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_leave_requests
             WHERE employee_id = :employee_id
               AND status IN (\'Pending\', \'Approved\')
               AND :start_date <= end_date
               AND :end_date >= start_date',
            [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        return ((int) ($row['total'] ?? 0)) > 0;
    }

    public function updateStatus(int $id, string $status, ?int $reviewedBy, ?string $remarks): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE hris_leave_requests
             SET status = :status,
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 review_remarks = :review_remarks
             WHERE id = :id
               AND status = \'Pending\''
        );

        $stmt->execute([
            'id' => $id,
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'review_remarks' => $remarks,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function statuses(): array
    {
        return ['Pending', 'Approved', 'Rejected', 'Cancelled'];
    }
}
