<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait SeedsWithoutFaker
{
    protected function pick(array $items): mixed
    {
        return Arr::random($items);
    }

    protected function randomBetween(Carbon $start, Carbon $end): Carbon
    {
        return Carbon::createFromTimestamp(
            random_int($start->getTimestamp(), $end->getTimestamp())
        );
    }

    protected function randomPhone(): string
    {
        return '09'.str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
    }
}
