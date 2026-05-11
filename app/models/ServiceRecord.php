<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ServiceRecord extends Model
{
    private const APPOINTMENT_STATUSES = [
        'Permanent','Temporary','Coterminous','COS','Job Order','Casual','Contractual',
    ];

    private const APPOINTMENT_NATURES = [
        'Original','Promotional','Transfer','Reinstatement','Renewal','Reclassification','Demotion',
    ];

    private const SEPARATION_TYPES = [
        'Resigned','Retired','LWOP','Dismissed','End of Contract','Transfer','Death','Others',
    ];

    public function appointmentStatuses(): array { return self::APPOINTMENT_STATUSES; }
    public function appointmentNatures(): array  { return self::APPOINTMENT_NATURES; }
    public function separationTypes(): array     { return self::SEPARATION_TYPES; }

    // -------------------------------------------------------------------------
    // Queries
    // -------------------------------------------------------------------------

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT sr.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last,
                    e.employment_status AS emp_status
             FROM hris_service_records sr
             JOIN hris_employees e ON e.id = sr.employee_id
             WHERE sr.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findByEmployee(int $employeeId): array
    {
        return $this->fetchAll(
            'SELECT sr.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_service_records sr
             JOIN hris_employees e ON e.id = sr.employee_id
             WHERE sr.employee_id = :eid
             ORDER BY sr.is_current DESC, sr.date_from DESC',
            ['eid' => $employeeId]
        );
    }

    public function currentForEmployee(int $employeeId): ?array
    {
        return $this->fetchOne(
            'SELECT sr.* FROM hris_service_records sr
             WHERE sr.employee_id = :eid AND sr.is_current = 1 LIMIT 1',
            ['eid' => $employeeId]
        );
    }

    public function search(string $query, string $empId, int $page, int $perPage): array
    {
        $params = [];
        $where  = $this->buildWhere($query, $empId, $params);
        $offset = ($page - 1) * $perPage;
        return $this->fetchAll(
            "SELECT sr.*,
                    e.employee_code, e.first_name AS emp_first, e.last_name AS emp_last
             FROM hris_service_records sr
             JOIN hris_employees e ON e.id = sr.employee_id
             {$where}
             ORDER BY sr.is_current DESC, e.last_name ASC, sr.date_from DESC
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
             FROM hris_service_records sr
             JOIN hris_employees e ON e.id = sr.employee_id
             {$where}",
            $params
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function countCurrentAppointments(): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM hris_service_records WHERE is_current = 1'
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function countEmployeesWithRecords(): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(DISTINCT employee_id) AS cnt FROM hris_service_records'
        );
        return (int) ($row['cnt'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Write
    // -------------------------------------------------------------------------

    public function create(array $d): int
    {
        $this->execute(
            'INSERT INTO hris_service_records (
                employee_id, position_title, item_number,
                salary_grade, salary_step, monthly_salary,
                appointment_status, appointment_nature,
                office_unit, division,
                date_from, date_to, is_current,
                separation_type, separation_date, remarks
             ) VALUES (
                :employee_id, :position_title, :item_number,
                :salary_grade, :salary_step, :monthly_salary,
                :appointment_status, :appointment_nature,
                :office_unit, :division,
                :date_from, :date_to, :is_current,
                :separation_type, :separation_date, :remarks
             )',
            $this->params($d)
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateById(int $id, array $d): bool
    {
        return $this->execute(
            'UPDATE hris_service_records SET
                position_title = :position_title,
                item_number = :item_number,
                salary_grade = :salary_grade,
                salary_step = :salary_step,
                monthly_salary = :monthly_salary,
                appointment_status = :appointment_status,
                appointment_nature = :appointment_nature,
                office_unit = :office_unit,
                division = :division,
                date_from = :date_from,
                date_to = :date_to,
                is_current = :is_current,
                separation_type = :separation_type,
                separation_date = :separation_date,
                remarks = :remarks
             WHERE id = :id',
            array_merge($this->params($d), ['id' => $id])
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute(
            'DELETE FROM hris_service_records WHERE id = :id',
            ['id' => $id]
        );
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
            $conditions[] = '(e.employee_code LIKE :q1 OR e.first_name LIKE :q2 OR e.last_name LIKE :q3 OR sr.position_title LIKE :q4)';
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
            $params['q4'] = '%' . $query . '%';
        }

        if ($empId !== '') {
            $conditions[] = 'sr.employee_id = :emp_id';
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
        $status = trim((string) ($d['appointment_status'] ?? ''));
        $nature = trim((string) ($d['appointment_nature'] ?? ''));
        $sep    = trim((string) ($d['separation_type'] ?? ''));

        return [
            'employee_id'        => (int) ($d['employee_id'] ?? 0),
            'position_title'     => trim((string) ($d['position_title'] ?? '')),
            'item_number'        => $this->n($d['item_number'] ?? null),
            'salary_grade'       => $this->n($d['salary_grade'] ?? null),
            'salary_step'        => $this->n($d['salary_step'] ?? null),
            'monthly_salary'     => is_numeric($d['monthly_salary'] ?? '') ? (float) $d['monthly_salary'] : null,
            'appointment_status' => in_array($status, self::APPOINTMENT_STATUSES, true) ? $status : null,
            'appointment_nature' => in_array($nature, self::APPOINTMENT_NATURES, true) ? $nature : null,
            'office_unit'        => $this->n($d['office_unit'] ?? null),
            'division'           => $this->n($d['division'] ?? null),
            'date_from'          => $this->n($d['date_from'] ?? null),
            'date_to'            => $this->n($d['date_to'] ?? null),
            'is_current'         => isset($d['is_current']) && $d['is_current'] ? 1 : 0,
            'separation_type'    => in_array($sep, self::SEPARATION_TYPES, true) ? $sep : null,
            'separation_date'    => $this->n($d['separation_date'] ?? null),
            'remarks'            => $this->n($d['remarks'] ?? null),
        ];
    }
}
