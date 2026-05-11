<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Appointment extends Model
{
    private const APPOINTMENT_TYPES = [
        'Original', 'Promotional', 'Transfer', 'Reinstatement', 'Renewal', 'Others',
    ];

    private const EMPLOYMENT_STATUSES = [
        'Permanent', 'Temporary', 'Coterminous', 'COS', 'Job Order', 'Casual',
    ];

    public function appointmentTypes(): array    { return self::APPOINTMENT_TYPES; }
    public function employmentStatuses(): array  { return self::EMPLOYMENT_STATUSES; }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT a.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last,
                    e.employment_status AS emp_status
             FROM hris_appointments a
             JOIN hris_employees e ON e.id = a.employee_id
             WHERE a.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByEmployee(int $employeeId): array
    {
        return $this->fetchAll(
            'SELECT a.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_appointments a
             JOIN hris_employees e ON e.id = a.employee_id
             WHERE a.employee_id = :eid
             ORDER BY a.is_current DESC, a.effectivity_date DESC',
            ['eid' => $employeeId]
        );
    }

    public function search(string $query, string $empId, int $page, int $perPage): array
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $params);
        $offset = ($page - 1) * $perPage;
        return $this->fetchAll(
            "SELECT a.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_appointments a
             JOIN hris_employees e ON e.id = a.employee_id
             {$where}
             ORDER BY a.is_current DESC, e.last_name ASC, a.effectivity_date DESC
             LIMIT :lim OFFSET :off",
            array_merge($params, ['lim' => $perPage, 'off' => $offset])
        );
    }

    public function countSearch(string $query, string $empId): int
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $params);
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt
             FROM hris_appointments a
             JOIN hris_employees e ON e.id = a.employee_id
             {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function countCurrent(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS cnt FROM hris_appointments WHERE is_current = 1');
        return (int) ($row['cnt'] ?? 0);
    }

    public function countEmployeesWithAppointments(): int
    {
        $row = $this->fetchOne('SELECT COUNT(DISTINCT employee_id) AS cnt FROM hris_appointments');
        return (int) ($row['cnt'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO hris_appointments (
                employee_id, appointment_type, position_title, item_number,
                salary_grade, salary_step, monthly_salary, employment_status,
                office_unit, division,
                effectivity_date, oath_date, report_date,
                is_current, remarks
             ) VALUES (
                :employee_id, :appointment_type, :position_title, :item_number,
                :salary_grade, :salary_step, :monthly_salary, :employment_status,
                :office_unit, :division,
                :effectivity_date, :oath_date, :report_date,
                :is_current, :remarks
             )',
            $this->params($d)
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateById(int $id, array $d): bool
    {
        return $this->execute(
            'UPDATE hris_appointments SET
                appointment_type  = :appointment_type,
                position_title    = :position_title,
                item_number       = :item_number,
                salary_grade      = :salary_grade,
                salary_step       = :salary_step,
                monthly_salary    = :monthly_salary,
                employment_status = :employment_status,
                office_unit       = :office_unit,
                division          = :division,
                effectivity_date  = :effectivity_date,
                oath_date         = :oath_date,
                report_date       = :report_date,
                is_current        = :is_current,
                remarks           = :remarks
             WHERE id = :id',
            array_merge($this->params($d), ['id' => $id])
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM hris_appointments WHERE id = :id', ['id' => $id]);
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

    private function buildWhere(string $query, string $empId, array &$params): string
    {
        $conditions = [];

        if ($query !== '') {
            $conditions[] = '(e.employee_code LIKE :q1 OR e.first_name LIKE :q2 OR e.last_name LIKE :q3 OR a.position_title LIKE :q4)';
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
            $params['q4'] = '%' . $query . '%';
        }

        if ($empId !== '') {
            $conditions[] = 'a.employee_id = :emp_id';
            $params['emp_id'] = (int) $empId;
        }

        return $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
    }

    private function n(?string $v): ?string
    {
        $v = trim((string) $v);
        return $v !== '' ? $v : null;
    }

    private function params(array $d): array
    {
        $type   = trim((string) ($d['appointment_type'] ?? ''));
        $status = trim((string) ($d['employment_status'] ?? ''));

        return [
            'employee_id'      => (int) ($d['employee_id'] ?? 0),
            'appointment_type' => in_array($type, self::APPOINTMENT_TYPES, true) ? $type : 'Others',
            'position_title'   => trim((string) ($d['position_title'] ?? '')),
            'item_number'      => $this->n($d['item_number'] ?? null),
            'salary_grade'     => $this->n($d['salary_grade'] ?? null),
            'salary_step'      => $this->n($d['salary_step'] ?? null),
            'monthly_salary'   => is_numeric($d['monthly_salary'] ?? '') ? (float) $d['monthly_salary'] : null,
            'employment_status'=> in_array($status, self::EMPLOYMENT_STATUSES, true) ? $status : null,
            'office_unit'      => $this->n($d['office_unit'] ?? null),
            'division'         => $this->n($d['division'] ?? null),
            'effectivity_date' => $this->n($d['effectivity_date'] ?? null),
            'oath_date'        => $this->n($d['oath_date'] ?? null),
            'report_date'      => $this->n($d['report_date'] ?? null),
            'is_current'       => isset($d['is_current']) && $d['is_current'] ? 1 : 0,
            'remarks'          => $this->n($d['remarks'] ?? null),
        ];
    }
}
