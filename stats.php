<?php

/**
 * Stats class
 * @author 8ctopus <hello@octopuslabs.io>
 */
class stats
{
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
     * @return void
     */
    public static function histogram_draw(array $histogram)
    {
        $bar_max_length = 100;

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
     * Calculate array average
     * @param  array $cells
     * @return float
     */
    public static function average(array $cells)
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

        $average = self::average($cells);

        // sum of squares
        foreach($cells as $cell) {
            // difference between cell and average squared
            $variance += pow(($cell - $average), 2);
        }

        $count = count($cells) -1;

        $standard_deviation = sqrt($variance) / sqrt($count);

        return $standard_deviation;
    }


    /**
     * Approximate normality test
     * @param  array  $cells
     * @return probability it's normal
     * @note found here https://www.paulstephenborile.com/2018/03/code-benchmarks-can-measure-fast-software-make-faster/
     */
    public static function test_normal(array $cells)
    {
        $average = self::average($cells);
        $median  = self::median($cells);

        return ($average - $median) / max($mean, $median);
    }
}

