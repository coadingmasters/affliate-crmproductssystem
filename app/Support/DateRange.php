<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Turns a named period into a concrete UTC range.
 *
 * Boundaries are worked out in the display timezone so "today" means today
 * for whoever is reading the screen, then converted for querying.
 */
class DateRange
{
    /**
     * Presets offered across the admin filters.
     */
    public const PERIODS = [
        'all' => 'All time',
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'this_week' => 'This week',
        'last_week' => 'Last week',
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year' => 'This year',
        'custom' => 'Custom range',
    ];

    /**
     * Resolve a period into [from, to], either of which may be null.
     *
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    public static function resolve(string $period, ?string $from = null, ?string $to = null): array
    {
        $tz = config('app.display_timezone');
        $now = CarbonImmutable::now($tz);

        $utc = fn (?CarbonImmutable $date) => $date?->utc();

        return match ($period) {
            'today' => [$utc($now->startOfDay()), $utc($now->endOfDay())],
            'yesterday' => [$utc($now->subDay()->startOfDay()), $utc($now->subDay()->endOfDay())],
            'this_week' => [$utc($now->startOfWeek()), $utc($now->endOfWeek())],
            'last_week' => [$utc($now->subWeek()->startOfWeek()), $utc($now->subWeek()->endOfWeek())],
            'this_month' => [$utc($now->startOfMonth()), $utc($now->endOfMonth())],
            'last_month' => [$utc($now->subMonth()->startOfMonth()), $utc($now->subMonth()->endOfMonth())],
            'this_year' => [$utc($now->startOfYear()), $utc($now->endOfYear())],
            'custom' => [
                $from ? $utc(CarbonImmutable::parse($from, $tz)->startOfDay()) : null,
                $to ? $utc(CarbonImmutable::parse($to, $tz)->endOfDay()) : null,
            ],
            default => [null, null],
        };
    }

    /**
     * Validate a yyyy-mm-dd string coming from a date input.
     */
    public static function parseDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * A human label for the active range, for showing on screen.
     */
    public static function label(string $period, ?string $from = null, ?string $to = null): string
    {
        if ($period === 'custom') {
            $tz = config('app.display_timezone');
            $fmt = fn (?string $d) => $d ? CarbonImmutable::parse($d, $tz)->format('M j, Y') : '…';

            return $fmt($from).' – '.$fmt($to);
        }

        return self::PERIODS[$period] ?? 'All time';
    }
}
