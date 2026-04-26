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
}
