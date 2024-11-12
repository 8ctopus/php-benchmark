<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use DivisionByZeroError;

class Stats
{
    /**
     * Calculate array mean
     *
     * @param array $cells
     *
     * @return float
     */
    public static function mean(array $cells) : float
    {
        return array_sum($cells) / count($cells);
    }

    /**
     * Calculate array median
     *
     * @param array $cells
     *
     * @return ?float
     */
    public static function median(array $cells) : ?float
    {
        // sort array values ascending
        sort($cells, SORT_NUMERIC);

        $count = count($cells);

        $index = floor($count / 2);

        if ($count % 2) {
            return $cells[$index];
        }

        return ($cells[$index - 1] + $cells[$index]) / 2;
    }

    /**
     * Calculate array standard deviation
     *
     * @param array $cells
     *
     * @return float
     */
    public static function standardDeviation(array $cells) : float
    {
        $variance = 0.0;

        $mean = self::mean($cells);

        // sum of squares
        foreach ($cells as $cell) {
            // difference between cell and mean squared
            $variance += ($cell - $mean) ** 2;
        }

        $count = (float) (count($cells) - 1);

        return sqrt($variance) / sqrt($count);
    }

    /**
     * Get array first mode
     *
     * @param array $cells
     *
     * @return float first mode
     */
    public static function mode(array $cells) : float
    {
        return self::modes($cells)[0];
    }

    /**
     * Calculate array modes
     *
     * @param array $cells
     *
     * @return array modes
     *
     * @note https://www.calculatorsoup.com/calculators/statistics/mean-median-mode.php
     */
    public static function modes(array $cells) : array
    {
        // group array by count
        $values = array_count_values($cells);

        // sort (lowest first)
        asort($values);

        // get modes
        return array_keys($values, max($values), true);
    }

    /**
     * Calculate array quartiles
     *
     * @param array $cells
     *
     * @return array quartiles
     *
     * @note https://en.wikipedia.org/wiki/Interquartile_range#Examples
     */
    public static function quartiles(array $cells) : array
    {
        // sort measures ascending
        sort($cells);

        // get half total cells adjusted for odd arrays
        $countHalf = (int) floor(count($cells) / 2);

        // 1st quartile
        $cells1 = array_slice($cells, 0, $countHalf);
        $quartile1 = self::median($cells1);

        // 3rd quartile
        $cells3 = array_slice($cells, -$countHalf, $countHalf);
        $quartile3 = self::median($cells3);

        return [$quartile1, $quartile3];
    }

    /**
     * Calculate array interquartile range
     *
     * @param array $cells
     *
     * @return float range
     */
    public static function interquartileRange(array $cells) : float
    {
        $quartiles = self::quartiles($cells);

        return $quartiles[1] - $quartiles[0];
    }

    /**
     * Get array outliers
     *
     * @param array $cells
     *
     * @return array outliers
     *
     * @note Outliers are values that lie outside the upper and lower fences
     * upper fence = Q3 + 1.5 × interquartile range
     * lower fence = Q1 − 1.5 × interquartile range
     */
    public static function outliers(array $cells) : array
    {
        $quartiles = self::quartiles($cells);
        $iqr = self::interquartileRange($cells);

        // calculate fences
        $fenceUpper = $quartiles[1] + 1.5 * $iqr;
        $fenceLower = $quartiles[0] - 1.5 * $iqr;

        sort($cells);

        $outliers = [];

        foreach ($cells as $cell) {
            if ($cell < $fenceLower || $cell > $fenceUpper) {
                $outliers[] = $cell;
            }
        }

        return $outliers;
    }

    /**
     * Approximate normality test
     *
     * @param array $data
     *
     * @return float probability it's normal
     *
     * @note found here https://www.paulstephenborile.com/2018/03/code-benchmarks-can-measure-fast-software-make-faster/
     */
    public static function testNormal(array $data) : float
    {
        $mean = self::mean($data);
        $median = self::median($data);

        return abs($mean - $median) / max($mean, $median);
    }

    /**
     * Create histogram
     *
     * @param array $data
     * @param int   $buckets    number of buckets
     *
     * @return array histogram
     */
    public static function histogram(array $data, int $buckets) : array
    {
        sort($data);

        $min = min($data);
        $max = max($data);

        $range = $max - $min;

        // calculate single bucket width
        $width = $range / $buckets;

        $histogram = [];

        // create buckets
        for ($i = 0; $i < $buckets; ++$i) {
            $histogram[$i] = [
                'bucket' => $i,
                // [start , end[
                'range_start' => $min + $i * $width,
                'range_end' => $min + ($i + 1) * $width,
                'count' => 0,
            ];
        }

        $histogram[$buckets - 1]['range_end'] += 0.00001;

        // group data points into buckets
        $i = 0;
        $end = $histogram[$i]['range_end'];

        foreach ($data as $value) {
            while ($value >= $end) {
                $end = $histogram[++$i]['range_end'];
            }

            ++$histogram[$i]['count'];
        }

        return $histogram;
    }

    /**
     * Draw histogram
     *
     * @param array $histogram
     * @param int   $barMaxLength
     *
     * @return void
     */
    public static function drawHistogram(array $histogram, int $barMaxLength) : void
    {
        // get buckets count
        $buckets = count($histogram);

        // find histogram max count
        $max = 0;

        foreach ($histogram as $value) {
            if ($value['count'] > $max) {
                $max = $value['count'];
            }
        }

        // draw table border
        $border = '+---------------------------' . str_repeat('-', $barMaxLength + 3) . "-+\n";

        echo $border;

        // draw table header
        $bar = str_pad('bar', $barMaxLength, ' ');

        echo "| bucket | range end | count | {$bar} |\n";

        // draw table border
        echo $border;

        for ($i = 0; $i < $buckets; ++$i) {
            $count = $histogram[$i]['count'];

            echo '| ' . str_pad((string) $i, strlen('bucket'), ' ', STR_PAD_LEFT) . ' | ' .
                str_pad((string) round($histogram[$i]['range_end'], 0), strlen('range end'), ' ', STR_PAD_LEFT) . ' | ' .
                str_pad((string) $count, strlen('count'), ' ', STR_PAD_LEFT) . ' | ' .
                str_pad(str_repeat('|', (int) round($barMaxLength * $count / $max, 0)), $barMaxLength, ' ', STR_PAD_RIGHT) . " |\n"
            ;
        }

        // draw table border
        echo $border;
    }

    /**
     * Relative difference between 2 numbers
     *
     * @param float $n1
     * @param float $n2
     *
     * @throws Division by zero
     */
    public static function relativeDifference(float $n1, float $n2) : float
    {
        if (!$n1) {
            throw new DivisionByZeroError();
        }

        return ($n2 - $n1) / $n1;
    }
}
