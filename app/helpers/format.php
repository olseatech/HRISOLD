<?php

declare(strict_types=1);

if (!function_exists('format_date')) {
    function format_date(?string $value, string $format = 'M d, Y'): string
    {
        if (!$value) {
            return '-';
        }

        $timestamp = strtotime($value);

        return $timestamp ? date($format, $timestamp) : '-';
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int|string $value, string $symbol = 'PHP '): string
    {
        return $symbol . number_format((float) $value, 2);
    }
}
