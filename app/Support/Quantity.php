<?php

namespace App\Support;

/**
 * Formats exact decimal strings (e.g. "1500.250") for display without
 * converting them to floats.
 */
final class Quantity
{
    public static function format(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        $negative = str_starts_with($whole, '-');
        $whole = ltrim($whole, '-');
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);
        $fraction = rtrim($fraction, '0');

        return ($negative ? '-' : '').$whole.($fraction !== '' ? '.'.$fraction : '');
    }
}
