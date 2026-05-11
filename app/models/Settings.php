<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Settings extends Model
{
    public function companyProfile(): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_companies ORDER BY id ASC LIMIT 1');
    }

    public function saveCompany(array $data): int
    {
        $company = $this->companyProfile();

        if ($company) {
            $this->execute(
                'UPDATE hris_companies
                 SET company_name = :company_name,
                     address = :address,
                     phone = :phone,
                     email = :email,
                     website = :website,
                     logo_path = :logo_path
                 WHERE id = :id',
                [
                    'id' => (int) $company['id'],
                    'company_name' => $data['company_name'],
                    'address' => $data['address'] ?: null,
                    'phone' => $data['phone'] ?: null,
                    'email' => $data['email'] ?: null,
                    'website' => $data['website'] ?: null,
                    'logo_path' => $data['logo_path'] ?: null,
                ]
            );

            return (int) $company['id'];
        }

        $this->execute(
            'INSERT INTO hris_companies (company_name, address, phone, email, website, logo_path)
             VALUES (:company_name, :address, :phone, :email, :website, :logo_path)',
            [
                'company_name' => $data['company_name'],
                'address' => $data['address'] ?: null,
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'website' => $data['website'] ?: null,
                'logo_path' => $data['logo_path'] ?: null,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function rolesWithPermissionCount(): array
    {
        return $this->fetchAll(
            'SELECT r.id, r.role_name, r.description, r.is_active, COUNT(rp.permission_id) AS permission_count
             FROM hris_roles r
             LEFT JOIN hris_role_permissions rp ON rp.role_id = r.id
             GROUP BY r.id, r.role_name, r.description, r.is_active
             ORDER BY r.role_name ASC'
        );
    }

    public function findRole(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_roles WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function toggleRoleActive(int $id): bool
    {
        return $this->execute(
            'UPDATE hris_roles SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = :id',
            ['id' => $id]
        );
    }

    public function systemConfigFilePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/cache/system-settings.json';
    }

    public function loadSystemSettings(): array
    {
        $path = $this->systemConfigFilePath();
        if (!file_exists($path)) {
            return [
                'timezone' => 'Asia/Manila',
                'date_format' => 'Y-m-d',
                'default_currency' => 'PHP',
            ];
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function saveSystemSettings(array $settings): bool
    {
        $path = $this->systemConfigFilePath();
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (!is_string($json)) {
            return false;
        }

        return file_put_contents($path, $json) !== false;
    }

    // ── Departments ────────────────────────────────────────────────────────────

    public function allDepartments(): array
    {
        return $this->fetchAll(
            'SELECT d.id, d.department_name, d.is_active,
                    b.branch_name
             FROM hris_departments d
             LEFT JOIN hris_branches b ON b.id = d.branch_id
             ORDER BY d.department_name ASC'
        );
    }

    public function findDepartment(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT d.*, b.branch_name FROM hris_departments d
             LEFT JOIN hris_branches b ON b.id = d.branch_id
             WHERE d.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function createDepartment(array $data): int
    {
        $branchId = $this->defaultBranchId();
        $this->execute(
            'INSERT INTO hris_departments (branch_id, department_name, is_active)
             VALUES (:branch_id, :department_name, :is_active)',
            [
                'branch_id'       => $branchId,
                'department_name' => $data['department_name'],
                'is_active'       => isset($data['is_active']) ? 1 : 0,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateDepartment(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE hris_departments SET department_name = :department_name, is_active = :is_active WHERE id = :id',
            [
                'id'              => $id,
                'department_name' => $data['department_name'],
                'is_active'       => isset($data['is_active']) ? 1 : 0,
            ]
        );
    }

    public function deleteDepartment(int $id): bool
    {
        return $this->execute('DELETE FROM hris_departments WHERE id = :id', ['id' => $id]);
    }

    private function defaultBranchId(): int
    {
        $row = $this->fetchOne('SELECT MIN(id) AS id FROM hris_branches');
        return (int) ($row['id'] ?? 1);
    }

    // ── Designations / Positions ───────────────────────────────────────────────

    public function allDesignations(): array
    {
        return $this->fetchAll(
            'SELECT id, designation_name, description, created_at FROM hris_designations ORDER BY designation_name ASC'
        );
    }

    public function findDesignation(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_designations WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function createDesignation(array $data): int
    {
        $this->execute(
            'INSERT INTO hris_designations (designation_name, description) VALUES (:designation_name, :description)',
            [
                'designation_name' => $data['designation_name'],
                'description'      => $data['description'] ?: null,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateDesignation(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE hris_designations SET designation_name = :designation_name, description = :description WHERE id = :id',
            [
                'id'               => $id,
                'designation_name' => $data['designation_name'],
                'description'      => $data['description'] ?: null,
            ]
        );
    }

    public function deleteDesignation(int $id): bool
    {
        return $this->execute('DELETE FROM hris_designations WHERE id = :id', ['id' => $id]);
    }

    // ── Leave Types ────────────────────────────────────────────────────────────

    public function allLeaveTypes(): array
    {
        return $this->fetchAll(
            'SELECT id, type_name, description, default_days, is_paid, is_active FROM hris_leave_types ORDER BY type_name ASC'
        );
    }

    public function findLeaveType(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_leave_types WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function createLeaveType(array $data): int
    {
        $this->execute(
            'INSERT INTO hris_leave_types (type_name, description, default_days, is_paid, is_active)
             VALUES (:type_name, :description, :default_days, :is_paid, :is_active)',
            [
                'type_name'    => $data['type_name'],
                'description'  => $data['description'] ?: null,
                'default_days' => max(0, (int) ($data['default_days'] ?? 0)),
                'is_paid'      => isset($data['is_paid']) ? 1 : 0,
                'is_active'    => isset($data['is_active']) ? 1 : 0,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateLeaveType(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE hris_leave_types
             SET type_name = :type_name, description = :description,
                 default_days = :default_days, is_paid = :is_paid, is_active = :is_active
             WHERE id = :id',
            [
                'id'           => $id,
                'type_name'    => $data['type_name'],
                'description'  => $data['description'] ?: null,
                'default_days' => max(0, (int) ($data['default_days'] ?? 0)),
                'is_paid'      => isset($data['is_paid']) ? 1 : 0,
                'is_active'    => isset($data['is_active']) ? 1 : 0,
            ]
        );
    }

    public function deleteLeaveType(int $id): bool
    {
        return $this->execute('DELETE FROM hris_leave_types WHERE id = :id', ['id' => $id]);
    }

    // ── Salary Grades ──────────────────────────────────────────────────────────

    public function allSalaryGrades(): array
    {
        return $this->fetchAll(
            'SELECT id, grade_name, min_salary, max_salary, created_at FROM hris_salary_grades ORDER BY grade_name ASC'
        );
    }

    public function findSalaryGrade(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM hris_salary_grades WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function createSalaryGrade(array $data): int
    {
        $this->execute(
            'INSERT INTO hris_salary_grades (grade_name, min_salary, max_salary)
             VALUES (:grade_name, :min_salary, :max_salary)',
            [
                'grade_name' => $data['grade_name'],
                'min_salary' => (float) ($data['min_salary'] ?? 0),
                'max_salary' => (float) ($data['max_salary'] ?? 0),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateSalaryGrade(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE hris_salary_grades SET grade_name = :grade_name, min_salary = :min_salary, max_salary = :max_salary WHERE id = :id',
            [
                'id'         => $id,
                'grade_name' => $data['grade_name'],
                'min_salary' => (float) ($data['min_salary'] ?? 0),
                'max_salary' => (float) ($data['max_salary'] ?? 0),
            ]
        );
    }

    public function deleteSalaryGrade(int $id): bool
    {
        return $this->execute('DELETE FROM hris_salary_grades WHERE id = :id', ['id' => $id]);
    }
}
