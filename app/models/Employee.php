<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Employee extends Model
{
    public function countAll(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS total FROM hris_employees');

        return (int) ($row['total'] ?? 0);
    }

    public function search(string $query = '', string $status = '', int $page = 1, int $perPage = 10): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where = $this->searchWhere($query, $status, $params);

        return $this->fetchAll(
            'SELECT e.id, e.employee_code, e.first_name, e.last_name, e.email, e.phone,
                    e.employment_status, d.department_name, ds.designation_name
             FROM hris_employees e
             INNER JOIN hris_departments d ON d.id = e.department_id
             INNER JOIN hris_designations ds ON ds.id = e.designation_id
             ' . $where . '
             ORDER BY e.id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countSearch(string $query = '', string $status = ''): int
    {
        $params = [];
        $where = $this->searchWhere($query, $status, $params);

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_employees e
             INNER JOIN hris_departments d ON d.id = e.department_id
             INNER JOIN hris_designations ds ON ds.id = e.designation_id
             ' . $where,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    public function employmentStatuses(): array
    {
        return ['Active', 'Probation', 'On Leave', 'Resigned', 'Terminated'];
    }

    public function listSimple(): array
    {
        return $this->fetchAll(
            'SELECT id, employee_code, first_name, last_name
             FROM hris_employees
             WHERE employment_status IN (\'Active\', \'Probation\', \'On Leave\')
             ORDER BY first_name, last_name'
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT e.*,
                    d.department_name,
                    ds.designation_name
             FROM hris_employees e
             LEFT JOIN hris_departments d  ON d.id = e.department_id
             LEFT JOIN hris_designations ds ON ds.id = e.designation_id
             WHERE e.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function allActive(): array
    {
        return $this->fetchAll(
            'SELECT id, employee_code,
                    CONCAT(first_name, " ", last_name) AS full_name,
                    first_name, last_name, employment_status
             FROM hris_employees
             WHERE employment_status != "Terminated"
             ORDER BY last_name ASC, first_name ASC'
        );
    }

    public function create(array $data): int
    {
        $code = $this->nextEmployeeCode();

        $this->execute(
            'INSERT INTO hris_employees (
                employee_code, first_name, middle_name, last_name, gender, date_of_birth,
                marital_status, nationality, phone, email, address,
                department_id, designation_id, employment_type, employment_status,
                date_hired, date_regularized, date_separated, supervisor_id
            ) VALUES (
                :employee_code, :first_name, :middle_name, :last_name, :gender, :date_of_birth,
                :marital_status, :nationality, :phone, :email, :address,
                :department_id, :designation_id, :employment_type, :employment_status,
                :date_hired, :date_regularized, :date_separated, :supervisor_id
            )',
            [
                'employee_code' => $code,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?: null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'marital_status' => $data['marital_status'] ?: 'Single',
                'nationality' => $data['nationality'] ?: null,
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'address' => $data['address'] ?: null,
                'department_id' => (int) $data['department_id'],
                'designation_id' => (int) $data['designation_id'],
                'employment_type' => $data['employment_type'] ?: 'Full-Time',
                'employment_status' => $data['employment_status'] ?: 'Active',
                'date_hired' => $data['date_hired'],
                'date_regularized' => $data['date_regularized'] ?: null,
                'date_separated' => $data['date_separated'] ?: null,
                'supervisor_id' => !empty($data['supervisor_id']) ? (int) $data['supervisor_id'] : null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateById(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE hris_employees SET
                first_name = :first_name,
                middle_name = :middle_name,
                last_name = :last_name,
                gender = :gender,
                date_of_birth = :date_of_birth,
                marital_status = :marital_status,
                nationality = :nationality,
                phone = :phone,
                email = :email,
                address = :address,
                department_id = :department_id,
                designation_id = :designation_id,
                employment_type = :employment_type,
                employment_status = :employment_status,
                date_hired = :date_hired,
                date_regularized = :date_regularized,
                date_separated = :date_separated,
                supervisor_id = :supervisor_id
             WHERE id = :id',
            [
                'id' => $id,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?: null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'marital_status' => $data['marital_status'] ?: 'Single',
                'nationality' => $data['nationality'] ?: null,
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'address' => $data['address'] ?: null,
                'department_id' => (int) $data['department_id'],
                'designation_id' => (int) $data['designation_id'],
                'employment_type' => $data['employment_type'] ?: 'Full-Time',
                'employment_status' => $data['employment_status'] ?: 'Active',
                'date_hired' => $data['date_hired'],
                'date_regularized' => $data['date_regularized'] ?: null,
                'date_separated' => $data['date_separated'] ?: null,
                'supervisor_id' => !empty($data['supervisor_id']) ? (int) $data['supervisor_id'] : null,
            ]
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM hris_employees WHERE id = :id', ['id' => $id]);
    }

    public function departmentOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, department_name FROM hris_departments WHERE is_active = 1 ORDER BY department_name ASC'
        );
    }

    public function designationOptions(): array
    {
        return $this->fetchAll(
            'SELECT id, designation_name FROM hris_designations ORDER BY designation_name ASC'
        );
    }

    private function nextEmployeeCode(): string
    {
        $row = $this->fetchOne('SELECT MAX(id) AS max_id FROM hris_employees');
        $next = ((int) ($row['max_id'] ?? 0)) + 1;

        return sprintf('EMP-%04d', $next);
    }

    private function searchWhere(string $query, string $status, array &$params): string
    {
        $conditions = [];

        if ($query !== '') {
            $conditions[] = '(e.employee_code LIKE :q1 OR e.first_name LIKE :q2 OR e.last_name LIKE :q3 OR e.email LIKE :q4 OR d.department_name LIKE :q5 OR ds.designation_name LIKE :q6 OR CONCAT(e.first_name, " ", e.last_name) LIKE :q7)';
            $params['q1'] = '%' . $query . '%';
            $params['q2'] = '%' . $query . '%';
            $params['q3'] = '%' . $query . '%';
            $params['q4'] = '%' . $query . '%';
            $params['q5'] = '%' . $query . '%';
            $params['q6'] = '%' . $query . '%';
            $params['q7'] = '%' . $query . '%';
        }

        if ($status !== '') {
            $conditions[] = 'e.employment_status = :status';
            $params['status'] = $status;
        }

        if ($conditions === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }
}
