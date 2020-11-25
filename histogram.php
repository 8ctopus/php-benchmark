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
     * @return array histogram
     */
    public static function create(array $data_points, int $buckets)
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
    public static function draw(array $histogram)
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
}
