<?php

/**
 * Histogram class
 * @author 8ctopus <hello@octopuslabs.io>
 */
class histogram
{
    /**
     * Create histogram
     * @param  array $data_points
     * @param  int $buckets number of buckets
     * @return array
     */
    public static function create($data_points, $buckets)
    {
        $histogram = [];

        // initialize histogram buckets
        for ($i = 0; $i < $buckets; $i++) {
            $histogram[$i] = 0;
        }

        // get min and max
        $max = max($data_points);
        $min = min($data_points);

        // calculate range
        $range = $max - $min;

        // calculate bucket width
        $width = $range / $buckets;

        // group data points into buckets
        foreach ($data_points as $value) {
            // find value offset from min
            $offset = $value - $min;

            $bucket = ceil($offset / $width) - 1;

            // move min value to first bucket
            if ($bucket == -1)
                $bucket = 0;

            // increment bucket count
            $histogram[$bucket]++;
        }

        return $histogram;
    }


    /**
     * Draw histogram
     * @param  array  $histogram
     * @return void
     */
    public static function draw(array $histogram)
    {
        // get buckets count
        $buckets = count($histogram);

        // find histogram max
        $max_count = 0;

        foreach ($histogram as $value) {
            if ($value > $max_count)
                $max_count = $value;
        }

        //var_dump($histogram);

        // draw histogram
        $bar_max_length = $max_count;

        // draw table border
        $border = "+---------------". str_repeat('-', $bar_max_length + 3) ."-+\n";

        echo($border);

        // draw table header
        $bar = str_pad('bar', $bar_max_length, ' ');

        echo("| bucket | count | {$bar} |\n");

        // draw table border
        echo($border);

        for ($i = 0; $i < $buckets; $i++) {
            $count = $histogram[$i];
            echo("| ". str_pad($i, strlen('bucket'), ' ', STR_PAD_LEFT). " | ". str_pad($count, strlen('count'), ' ', STR_PAD_LEFT) ." | ". str_pad(str_repeat('*', round($bar_max_length * $count / $max_count, 0)), $bar_max_length, ' ', STR_PAD_RIGHT) ." |\n");
        }

        // draw table border
        echo($border);
    }
}
