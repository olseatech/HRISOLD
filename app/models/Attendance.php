<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Attendance extends Model
{
    public function countPresentByDate(string $date): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_attendance
             WHERE date = :date
               AND status IN (\'Present\', \'Late\', \'Half-Day\')',
            ['date' => $date]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function presentTrendByRange(string $startDate, string $endDate): array
    {
        $rows = $this->fetchAll(
            'SELECT date, COUNT(*) AS total
             FROM hris_attendance
             WHERE date BETWEEN :start_date AND :end_date
               AND status IN (\'Present\', \'Late\', \'Half-Day\')
             GROUP BY date
             ORDER BY date ASC',
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        $trend = [];
        foreach ($rows as $row) {
            $date = (string) ($row['date'] ?? '');
            if ($date === '') {
                continue;
            }

            $trend[$date] = (int) ($row['total'] ?? 0);
        }

        return $trend;
    }

    public function listFiltered(string $date, string $query = '', string $status = '', int $page = 1, int $perPage = 10, ?int $employeeId = null): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = ['date' => $date];
        $where = 'WHERE a.date = :date';

        if ($employeeId !== null) {
            $where .= ' AND a.employee_id = :employee_id';
            $params['employee_id'] = $employeeId;
        }

        if ($query !== '') {
            $where .= ' AND (e.employee_code LIKE :query OR e.first_name LIKE :query OR e.last_name LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $where .= ' AND a.status = :status';
            $params['status'] = $status;
        }

        return $this->fetchAll(
            'SELECT a.id, a.employee_id, a.date, a.clock_in, a.clock_out, a.hours_worked, a.overtime_hrs, a.status, a.remarks,
                    e.employee_code, e.first_name, e.last_name
             FROM hris_attendance a
             INNER JOIN hris_employees e ON e.id = a.employee_id
             ' . $where . '
             ORDER BY e.first_name ASC, e.last_name ASC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countFiltered(string $date, string $query = '', string $status = '', ?int $employeeId = null): int
    {
        $params = ['date' => $date];
        $where = 'WHERE a.date = :date';

        if ($employeeId !== null) {
            $where .= ' AND a.employee_id = :employee_id';
            $params['employee_id'] = $employeeId;
        }

        if ($query !== '') {
            $where .= ' AND (e.employee_code LIKE :query OR e.first_name LIKE :query OR e.last_name LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $where .= ' AND a.status = :status';
            $params['status'] = $status;
        }

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_attendance a
             INNER JOIN hris_employees e ON e.id = a.employee_id
             ' . $where,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    public function findByEmployeeDate(int $employeeId, string $date): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM hris_attendance WHERE employee_id = :employee_id AND date = :date LIMIT 1',
            [
                'employee_id' => $employeeId,
                'date' => $date,
            ]
        );
    }

    public function statuses(): array
    {
        return ['Present', 'Absent', 'Late', 'Half-Day', 'Holiday', 'Rest Day'];
    }

    public function record(array $data): bool
    {
        return $this->execute(
            'INSERT INTO hris_attendance (
                employee_id, date, clock_in, clock_out, hours_worked, overtime_hrs, status, remarks
            ) VALUES (
                :employee_id, :date, :clock_in, :clock_out, :hours_worked, :overtime_hrs, :status, :remarks
            )
            ON DUPLICATE KEY UPDATE
                clock_in = VALUES(clock_in),
                clock_out = VALUES(clock_out),
                hours_worked = VALUES(hours_worked),
                overtime_hrs = VALUES(overtime_hrs),
                status = VALUES(status),
                remarks = VALUES(remarks)',
            [
                'employee_id' => (int) $data['employee_id'],
                'date' => $data['date'],
                'clock_in' => $data['clock_in'] ?: null,
                'clock_out' => $data['clock_out'] ?: null,
                'hours_worked' => $data['hours_worked'] !== '' ? (float) $data['hours_worked'] : null,
                'overtime_hrs' => $data['overtime_hrs'] !== '' ? (float) $data['overtime_hrs'] : 0,
                'status' => $data['status'],
                'remarks' => $data['remarks'] ?: null,
            ]
        );
    }
}
