<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public static function required(array $data, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            $value = $data[$field] ?? null;

            if ($value === null || trim((string) $value) === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        return $errors;
    }

    public static function email(array $data, string $field): array
    {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '') {
            return [];
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return [$field => 'Email format is invalid.'];
        }

        return [];
    }

    public static function inSet(array $data, string $field, array $allowed): array
    {
        $value = trim((string) ($data[$field] ?? ''));

        if ($value === '') {
            return [];
        }

        if (!in_array($value, $allowed, true)) {
            return [$field => 'Invalid value selected.'];
        }

        return [];
    }

    public static function validDate(array $data, string $field): array
    {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '') {
            return [];
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$dt || $dt->format('Y-m-d') !== $value) {
            return [$field => 'Invalid date format.'];
        }

        return [];
    }

    public static function dateOrder(array $data, string $startField, string $endField): array
    {
        $startValue = trim((string) ($data[$startField] ?? ''));
        $endValue = trim((string) ($data[$endField] ?? ''));

        if ($startValue === '' || $endValue === '') {
            return [];
        }

        $start = \DateTime::createFromFormat('Y-m-d', $startValue);
        $end = \DateTime::createFromFormat('Y-m-d', $endValue);

        if (!$start || !$end) {
            return [];
        }

        if ($start > $end) {
            return [$endField => 'End date must be on or after start date.'];
        }

        return [];
    }

    public static function datetimeOrder(?string $start, ?string $end): bool
    {
        if (!$start || !$end) {
            return true;
        }

        try {
            return new \DateTimeImmutable($start) <= new \DateTimeImmutable($end);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function numericMin(array $data, string $field, float $min): array
    {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '') {
            return [];
        }

        if (!is_numeric($value) || (float) $value < $min) {
            return [$field => 'Value must be at least ' . $min . '.'];
        }

        return [];
    }
}
