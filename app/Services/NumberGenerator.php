<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberGenerator
{
    public static function make(string $prefix, string $table, string $column, int $padding = 4): string
    {
        $date = now()->format('Ymd');
        $base = $prefix . '-' . $date . '-';

        $last = DB::table($table)
            ->where($column, 'like', $base . '%')
            ->orderByDesc($column)
            ->value($column);

        $sequence = 1;
        if ($last) {
            $sequence = (int) substr($last, -$padding) + 1;
        }

        return $base . str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT);
    }
}
