<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Payroll extends Model
{
    public function salaryGrades(): array
    {
        return $this->fetchAll('SELECT * FROM hris_salary_grades ORDER BY grade_name ASC');
    }

    public function upsertGrade(string $gradeName, float $minSalary, float $maxSalary): bool
    {
        $row = $this->fetchOne('SELECT id FROM hris_salary_grades WHERE grade_name = :grade_name LIMIT 1', ['grade_name' => $gradeName]);

        if ($row) {
            return $this->execute(
                'UPDATE hris_salary_grades SET min_salary = :min_salary, max_salary = :max_salary WHERE id = :id',
                [
                    'id' => (int) $row['id'],
                    'min_salary' => $minSalary,
                    'max_salary' => $maxSalary,
                ]
            );
        }

        return $this->execute(
            'INSERT INTO hris_salary_grades (grade_name, min_salary, max_salary) VALUES (:grade_name, :min_salary, :max_salary)',
            [
                'grade_name' => $gradeName,
                'min_salary' => $minSalary,
                'max_salary' => $maxSalary,
            ]
        );
    }

    public function listEmployeeSalaries(string $query = '', int $page = 1, int $perPage = 10): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where = '';

        if ($query !== '') {
            $where = 'WHERE e.employee_code LIKE :query OR e.first_name LIKE :query OR e.last_name LIKE :query OR sg.grade_name LIKE :query';
            $params['query'] = '%' . $query . '%';
        }

        return $this->fetchAll(
            'SELECT es.id, es.employee_id, es.salary_grade_id, es.basic_salary, es.effective_date, es.is_current,
                    e.employee_code, e.first_name, e.last_name,
                    sg.grade_name
             FROM hris_employee_salaries es
             INNER JOIN hris_employees e ON e.id = es.employee_id
             LEFT JOIN hris_salary_grades sg ON sg.id = es.salary_grade_id
             ' . $where . '
             ORDER BY es.effective_date DESC, es.id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countEmployeeSalaries(string $query = ''): int
    {
        $params = [];
        $where = '';

        if ($query !== '') {
            $where = 'WHERE e.employee_code LIKE :query OR e.first_name LIKE :query OR e.last_name LIKE :query OR sg.grade_name LIKE :query';
            $params['query'] = '%' . $query . '%';
        }

        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total
             FROM hris_employee_salaries es
             INNER JOIN hris_employees e ON e.id = es.employee_id
             LEFT JOIN hris_salary_grades sg ON sg.id = es.salary_grade_id
             ' . $where,
            $params
        );

        return (int) ($row['total'] ?? 0);
    }

    public function createEmployeeSalary(int $employeeId, ?int $salaryGradeId, float $basicSalary, string $effectiveDate, bool $isCurrent): int
    {
        $this->db->beginTransaction();

        try {
            if ($isCurrent) {
                $this->execute('UPDATE hris_employee_salaries SET is_current = 0 WHERE employee_id = :employee_id', ['employee_id' => $employeeId]);
            }

            $this->execute(
                'INSERT INTO hris_employee_salaries (employee_id, salary_grade_id, basic_salary, effective_date, is_current)
                 VALUES (:employee_id, :salary_grade_id, :basic_salary, :effective_date, :is_current)',
                [
                    'employee_id' => $employeeId,
                    'salary_grade_id' => $salaryGradeId,
                    'basic_salary' => $basicSalary,
                    'effective_date' => $effectiveDate,
                    'is_current' => $isCurrent ? 1 : 0,
                ]
            );

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findSalaryById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_employee_salaries WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function listEmployeesSimple(): array
    {
        return $this->fetchAll(
            'SELECT id, employee_code, first_name, last_name
             FROM hris_employees
             ORDER BY first_name ASC, last_name ASC'
        );
    }
}
