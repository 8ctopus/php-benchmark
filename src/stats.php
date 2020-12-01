<?php

/**
 * Stats class
 * @author 8ctopus <hello@octopuslabs.io>
 */
class stats
{
    /**
     * Calculate array mean
     * @param  array $cells
     * @return float
     */
    public static function mean(array $cells)
    {
        return array_sum($cells) / count($cells);
    }


    /**
     * Calculate array median
     * @param  array $cells
     * @return float
     */
    public static function median(array $cells)
    {
        // sort array values ascending
        sort($cells, SORT_NUMERIC);

        $count = count($cells);

        $index = floor($count / 2);

        if ($count % 2)
            return $cells[$index];
        else
            return ($cells[$index -1] + $cells[$index]) / 2;
    }


    /**
     * Calculate array standard deviation
     * @param  array $cells
     * @return float
     */
    public static function standard_deviation(array $cells)
    {
        $variance = 0.0;

        $mean = self::mean($cells);

        // sum of squares
        foreach($cells as $cell) {
            // difference between cell and mean squared
            $variance += pow(($cell - $mean), 2);
        }

        $count = count($cells) -1;

        $standard_deviation = sqrt($variance) / sqrt($count);

        return $standard_deviation;
    }


    /**
     * Get array first mode
     * @param  array $cells
     * @return float first mode
     */
    public static function mode(array $cells)
    {
        return self::modes($cells)[0];
    }


    /**
     * Calculate array modes
     * @param  array $cells
     * @return array modes
     * @note https://www.calculatorsoup.com/calculators/statistics/mean-median-mode.php
     */
    public static function modes(array $cells)
    {
        // group array by count
        $values = array_count_values($cells);

        // sort (lowest first)
        asort($values);

        // get modes
        return array_keys($values, max($values));
    }


    /**
     * Calculate array quartiles
     * @param  array $cells
     * @return array quartiles
     * @note https://en.wikipedia.org/wiki/Interquartile_range#Examples
     */
    public static function quartiles(array $cells)
    {
        // sort measures ascending
        sort($cells);

        // get half total cells adjusted for odd arrays
        $count_half = floor(count($cells) / 2);

        // 1st quartile
        $cells1 = array_slice($cells, 0, $count_half);
        $quartile1 = self::median($cells1);

        // 3rd quartile
        $cells3 = array_slice($cells, -$count_half, $count_half);
        $quartile3 = self::median($cells3);

        return [$quartile1, $quartile3];
    }


    /**
     * Calculate array interquartile range
     * @param  array  $cells
     * @return float range
     */
    public static function interquartile_range(array $cells)
    {
        $quartiles = self::quartiles($cells);

        return $quartiles[1] - $quartiles[0];
    }


    /**
     * Get array outliers
     * @param  array  $cells
     * @return array outliers
     * @note Outliers are values that lie outside the upper and lower fences
     * upper fence = Q3 + 1.5 × interquartile range
     * lower fence = Q1 − 1.5 × interquartile range
     */
    public static function outliers(array $cells)
    {
        $quartiles = self::quartiles($cells);
        $iqr       = self::interquartile_range($cells);

        // calculate fences
        $fence_upper = $quartiles[1] + 1.5 * $iqr;
        $fence_lower = $quartiles[0] - 1.5 * $iqr;

        sort($cells);

        $outliers = [];

        foreach ($cells as $cell) {
            if ($cell < $fence_lower || $cell > $fence_upper)
                $outliers[] = $cell;
        }

        return $outliers;
    }


    /**
     * Approximate normality test
     * @param  array  $cells
     * @return float  probability it's normal
     * @note found here https://www.paulstephenborile.com/2018/03/code-benchmarks-can-measure-fast-software-make-faster/
     */
    public static function test_normal(array $cells)
    {
        $mean   = self::mean($cells);
        $median = self::median($cells);

        return abs($mean - $median) / max($mean, $median);
    }


    /**
     * Create histogram
     * @param  array $data_points
     * @param  int $buckets number of buckets
     * @return array histogram
     */
    public static function histogram(array $data_points, int $buckets)
    {
        // get min and max
        $max = max($data_points);
        $min = min($data_points);

        // calculate range
        $range = $max - $min;

        // calculate bucket width
        $width = $range / $buckets;

        $histogram = [];

        // initialize histogram buckets
        for ($i = 0; $i < $buckets; $i++) {
            $histogram[$i] = [
                'bucket'      => $i,
                'range_start' => $min + $i * $width,
                'range_end'   => $min + ($i + 1) * $width,
                'count'       => 0,
            ];
        }

        // group data points into buckets
        foreach ($data_points as $value) {
            // find value offset from min
            $offset = $value - $min;

            $bucket = ceil($offset / $width) - 1;

            // move min value to first bucket
            if ($bucket == -1)
                $bucket = 0;

            // increment bucket count
            $histogram[$bucket]['count']++;
        }

        return $histogram;
    }


    /**
     * Draw histogram
     * @param  array  $histogram
     * @param  int    $bar_max_length
     * @return void
     */
    public static function histogram_draw(array $histogram, int $bar_max_length)
    {
        // get buckets count
        $buckets = count($histogram);

        // find histogram max count
        $max = 0;

        foreach ($histogram as $value) {
            if ($value['count'] > $max)
                $max = $value['count'];
        }

        // draw table border
        $border = "+---------------------------". str_repeat('-', $bar_max_length + 3) ."-+\n";

        echo($border);

        // draw table header
        $bar = str_pad('bar', $bar_max_length, ' ');

        echo("| bucket | range end | count | {$bar} |\n");

        // draw table border
        echo($border);

        for ($i = 0; $i < $buckets; $i++) {
            $count = $histogram[$i]['count'];

            echo("| ". str_pad($i, strlen('bucket'), ' ', STR_PAD_LEFT) ." | ".
                str_pad(round($histogram[$i]['range_end'], 0), strlen('range end'), ' ', STR_PAD_LEFT) ." | ".
                str_pad($count, strlen('count'), ' ', STR_PAD_LEFT) ." | ".
                str_pad(str_repeat('|', round($bar_max_length * $count / $max, 0)), $bar_max_length, ' ', STR_PAD_RIGHT) ." |\n"
            );
        }

        // draw table border
        echo($border);
    }


    /**
     * Relative difference between 2 numbers
     * @param  float $n1
     * @param  float $n2
     * @param  float relative difference between n1 and n2, taking n1 as base
     */
    public static function relative_difference(float $n1, float $n2)
    {
        $absolute_difference = $n2 - $n1;

        return $absolute_difference / $n1;
    }
}
