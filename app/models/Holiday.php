<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Holiday extends Model
{
    public const TYPES = ['Regular', 'Special Non-Working', 'Special Working'];

    public function all(int $year = 0): array
    {
        if ($year <= 0) {
            return $this->fetchAll(
                'SELECT * FROM hris_holidays ORDER BY holiday_date ASC'
            );
        }

        return $this->fetchAll(
            "SELECT * FROM hris_holidays
             WHERE YEAR(holiday_date) = :year
                OR is_recurring = 1
             ORDER BY
                MONTH(holiday_date) ASC,
                DAY(holiday_date) ASC",
            ['year' => $year]
        );
    }

    public function getBetween(string $dateFrom, string $dateTo): array
    {
        $rows = $this->fetchAll(
            "SELECT holiday_date, is_recurring,
                    MONTH(holiday_date) AS m, DAY(holiday_date) AS d
             FROM hris_holidays
             WHERE (holiday_date BETWEEN :f1 AND :t1)
                OR is_recurring = 1",
            ['f1' => $dateFrom, 't1' => $dateTo]
        );

        $from = new \DateTime($dateFrom);
        $to   = new \DateTime($dateTo);

        $dates = [];
        foreach ($rows as $row) {
            if ((int) $row['is_recurring'] === 0) {
                $dates[] = $row['holiday_date'];
            } else {
                // Expand recurring holiday across the years spanned by the range
                $startYear = (int) $from->format('Y');
                $endYear   = (int) $to->format('Y');
                for ($y = $startYear; $y <= $endYear; $y++) {
                    $candidate = sprintf('%04d-%02d-%02d', $y, (int) $row['m'], (int) $row['d']);
                    if ($candidate >= $dateFrom && $candidate <= $dateTo) {
                        $dates[] = $candidate;
                    }
                }
            }
        }

        return array_values(array_unique($dates));
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM hris_holidays WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function countByYear(int $year): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS cnt FROM hris_holidays WHERE YEAR(holiday_date) = :year',
            ['year' => $year]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function countRecurring(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS cnt FROM hris_holidays WHERE is_recurring = 1');
        return (int) ($row['cnt'] ?? 0);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO hris_holidays (name, holiday_date, holiday_type, is_recurring, remarks)
             VALUES (:name, :holiday_date, :holiday_type, :is_recurring, :remarks)',
            [
                'name'         => trim((string) ($data['name'] ?? '')),
                'holiday_date' => $data['holiday_date'],
                'holiday_type' => in_array($data['holiday_type'] ?? '', self::TYPES, true) ? $data['holiday_type'] : 'Regular',
                'is_recurring' => (int) (bool) ($data['is_recurring'] ?? false),
                'remarks'      => $this->n($data['remarks'] ?? null),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function updateById(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE hris_holidays
             SET name = :name, holiday_date = :holiday_date, holiday_type = :holiday_type,
                 is_recurring = :is_recurring, remarks = :remarks
             WHERE id = :id',
            [
                'id'           => $id,
                'name'         => trim((string) ($data['name'] ?? '')),
                'holiday_date' => $data['holiday_date'],
                'holiday_type' => in_array($data['holiday_type'] ?? '', self::TYPES, true) ? $data['holiday_type'] : 'Regular',
                'is_recurring' => (int) (bool) ($data['is_recurring'] ?? false),
                'remarks'      => $this->n($data['remarks'] ?? null),
            ]
        );
    }

    public function deleteById(int $id): bool
    {
        return $this->execute('DELETE FROM hris_holidays WHERE id = :id', ['id' => $id]);
    }

    private function n(?string $v): ?string
    {
        $v = trim((string) $v);
        return $v !== '' ? $v : null;
    }
}
