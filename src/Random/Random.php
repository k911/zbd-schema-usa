<?php
declare(strict_types=1);

namespace App\Random;

use Generator;

final class Random
{
    public static function ints(int $count = 1, int $min = 0, int $max = PHP_INT_MAX): Generator
    {
        while ($count > 0) {
            --$count;
            yield self::int($min, $max);
        }
    }

    public static function int(int $min = 0, int $max = PHP_INT_MAX): int
    {
        try {
            return \random_int($min, $max);
        } catch (\Exception $e) {
            return rand($min, $max);
        }
    }

    public static function intPair(int $min = 0, int $max = PHP_INT_MAX): array
    {
        return [self::int($min, $max), self::int($min, $max)];
    }

    public static function intPairs(int $count = 1, int $min = 0, int $max = PHP_INT_MAX): Generator
    {
        while ($count > 0) {
            --$count;
            yield self::intPair($min, $max);
        }
    }

    public static function uniqueIntPairsDifferentSizes(int $count = 1, int $minOne = 0, int $maxOne = PHP_INT_MAX, int $minTwo = 0, int $maxTwo = PHP_INT_MAX): Generator
    {
        $registry = [];

        $min = min($minOne, $minTwo);
        $max = max($maxOne, $maxTwo);

        if ($count > ($max - $min)) {
            throw new \OutOfRangeException(\sprintf('Cannot generate "%d" unique pairs due to bigger count than available range "%d".', $count, $max - $min));
        }

        while ($count > 0) {
            $pair = [self::int($minOne, $maxOne), self::int($minTwo, $maxTwo)];
            $key = \sprintf('%d_%d', $pair[0], $pair[1]);
            if (!isset($registry[$key])) {
                $registry[$key] = true;
                --$count;
                yield $pair;
            }
        }
    }

    public static function uniqueIntPairs(int $count = 1, int $min = 0, int $max = PHP_INT_MAX): Generator
    {
        $registry = [];

        if ($count > ($max - $min)) {
            throw new \OutOfRangeException(\sprintf('Cannot generate "%d" unique pairs due to bigger count than available range "%d".', $count, $max - $min));
        }

        while ($count > 0) {
            $pair = self::intPair($min, $max);
            $key = \sprintf('%d_%d', $pair[0], $pair[1]);
            if (!isset($registry[$key])) {
                $registry[$key] = true;
                --$count;
                yield $pair;
            }
        }
    }

}