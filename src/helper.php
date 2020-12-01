<?php

/**
 * Helper class
 * @author 8ctopus <hello@octopuslabs.io>
 */
class helper
{
    /**
     * Analyze test results
     * @param  array $measurements
     * @return array of strings or false if any of the test iterations failed
     */
    public static function analyze_test(array $measurements)
    {
        // check if the test failed at least once
        if (in_array(false, $measurements))
            return false;

        return [
            'mean'          => stats::mean($measurements),
            'median'        => stats::median($measurements),
            'mode'          => stats::mode($measurements),
            'minmum'        => min($measurements),
            'maximum'       => max($measurements),
            'quartile 1'    => stats::quartiles($measurements)[0],
            'quartile 3'    => stats::quartiles($measurements)[1],
            'IQ range'      => stats::interquartile_range($measurements),
            'std deviation' => stats::standard_deviation($measurements),
            'normality'     => stats::test_normal($measurements),
        ];
    }


    /**
     * Format number
     * @param  int $number
     * @param  int $padding
     * @return string
     */
    public static function format_number($number, $padding)
    {
        return str_pad(number_format($number, 0, '.', ''), $padding, ' ', STR_PAD_LEFT);
    }


    /**
     * Format percentage
     * @param  float $number
     * @param  int $padding
     * @return string
     */
    public static function format_percentage($number, $padding)
    {
        return str_pad(number_format(100 * $number, 1, '.', '') .'%', $padding, ' ', STR_PAD_LEFT);
    }


    /**
     * Format bytes
     * @param  int $size
     * @param  int $precision
     * @return string
     * @note https://stackoverflow.com/a/2510540/10126479
     */
    public static function format_bytes($size, $precision)
    {
        $base = log($size, 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
    }


    /**
     * Get all array values as string
     * @param  array $cells
     * @return string
     */
    public static function all_measurements(array $cells)
    {
        $str = "\n\n";

        foreach ($cells as $key => $value) {
            $str .= self::format_number($value, 0) .' ';

            if (!(($key + 1) % 32))
                $str .= "\n";
        }

        return $str ."\n";
    }


    /**
     * Get outliers as string
     * @param  array $cells
     * @return string
     */
    public static function outliers(array $cells)
    {
        $outliers = stats::outliers($cells);

        $str = "\n\n";

        foreach ($outliers as $key => $outlier) {
            $str .= self::format_number($outlier, 0) .' ';

            if (!(($key + 1) % 32))
                $str .= "\n";
        }

        return $str ."\n";
    }


    /**
     * Check functions exist
     * @param  array  $functions
     * @return array  only existing functions are returned
     */
    public static function check_functions_exist(array $functions)
    {
        // remove functions that don't exist
        foreach ($functions as $key => $function) {
            if (!function_exists($function)) {
                echo("Removed $function as it does not exist");
                unset($functions[$key]);
            }
        }

        return $functions;
    }


    /**
     * Create not random bytes string
     * @param  int $length
     * @return string
     */
    public static function not_random_bytes($length)
    {
        $str = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= chr(rand(0, 255));
        }

        return $str;
    }
}
