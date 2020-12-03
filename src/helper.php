<?php

/**
 * Helper class
 * @author 8ctopus <hello@octopuslabs.io>
 */
class helper
{
    // paddings
    public static $pad1 = 18;
    public static $pad2 = 10;

    /**
     * Analyze test results
     * @param  array $measurements
     * @return array of strings or null if any of the test iterations failed
     */
    public static function analyze_test(array $measurements) : ?array
    {
        // check if the test failed at least once
        if (in_array(false, $measurements))
            return null;

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
     * Show benchmark results
     * @param  array $data
     * @return void
     */
    public static function show_benchmark(array $data) : void
    {
        $line = str_pad('', self::$pad1 + self::$pad2 + 3, '-');

        // analyze test results
        foreach ($data as $test => $measurements) {
            $result = helper::analyze_test($measurements);

            // check for error
            if ($result === null) {
                echo(str_pad($test, self::$pad1) .' : '. str_pad('FAILED', self::$pad2, ' ', STR_PAD_LEFT) ."\n");
                echo($line ."\n");
                continue;
            }

            // show test results
            echo(str_pad($test, self::$pad1) .' : '. str_pad('iterations', self::$pad2, ' ', STR_PAD_LEFT) ."\n");

            foreach ($result as $key => $value) {
                if ($key == 'normality')
                    echo(str_pad($key, self::$pad1) .' : '. helper::format_percentage($value, false, self::$pad2) ."\n");
                else
                    echo(str_pad($key, self::$pad1) .' : '. helper::format_number($value, self::$pad2) ."\n");
            }

            // show histogram
            if ($settings['show_histogram']) {
                echo("\n");
                $histogram = stats::histogram($measurements, $settings['histogram_buckets']);
                stats::histogram_draw($histogram, $settings['histogram_bar_width']);
            }

            // output outliers
            if ($settings['show_outliers']) {
                echo("\n");
                echo(str_pad('outliers', self::$pad1) .' : '. helper::outliers($measurements) ."\n");
            }

            // output all measurements
            if ($settings['show_all_measurements']) {
                echo("\n");
                echo(str_pad('values', self::$pad1) .' : '. helper::all_measurements($measurements) ."\n");
            }

            echo($line ."\n");
        }
    }


    /**
     * Show comparison
     * @param array $baseline
     * @param array $latest
     * @return void
     */
    public static function show_compare(array $baseline, array $latest) : void
    {
        // paddings
        self::$pad1 = 18;
        self::$pad2 = 10;
        $line = str_pad('', self::$pad1 + 3 * self::$pad2 + 3, '-');

        echo($line ."\n");

        // compare tests
        foreach ($baseline as $test1 => $measurements1) {
            // get latest measurements
            $measurements2 = $latest[$test1];

            // analyze test results
            $result1 = helper::analyze_test($measurements1);
            $result2 = helper::analyze_test($measurements2);

            // check for error
            if ($result1 === null || $result2 === null) {
                echo(str_pad($test1, self::$pad1) .' : '. str_pad('FAILED', self::$pad2, ' ', STR_PAD_LEFT) ."\n");
                echo($line ."\n");
                continue;
            }

            // show test results
            echo(str_pad($test1, self::$pad1) .' : '. str_pad('baseline', self::$pad2, ' ', STR_PAD_LEFT) . str_pad('latest', self::$pad2, ' ', STR_PAD_LEFT) ."\n");

            // show test results
            foreach ($result1 as $key => $value1) {
                // get latest result for key
                $value2 = $result2[$key];

                if ($key == 'normality')
                    echo(str_pad($key, self::$pad1) .' : '. helper::format_percentage($value1, false, self::$pad2) . helper::format_percentage($value1, false, self::$pad2) ."\n");
                else {
                    try {
                        $delta = stats::relative_difference($value1, $value2);

                        echo(str_pad($key, self::$pad1) .' : '. helper::format_number($value1, self::$pad2) . helper::format_number($value2, self::$pad2) . helper::format_percentage($delta, true, self::$pad2) ."\n");
                    }
                    catch (DivisionByZeroError $e) {
                        echo(str_pad($key, self::$pad1) .' : '. helper::format_number($value1, self::$pad2) . helper::format_number($value2, self::$pad2) . str_pad('nan', self::$pad2, ' ', STR_PAD_LEFT) ."\n");
                    }
                }
            }

            echo($line ."\n");
        }

    }


    /**
     * Format number
     * @param  int $number
     * @param  int $padding
     * @return string
     */
    public static function format_number(int $number, int $padding) : string
    {
        return str_pad(number_format($number, 0, '.', ''), $padding, ' ', STR_PAD_LEFT);
    }


    /**
     * Format percentage
     * @param  float $number
     * @param  bool $sign
     * @param  int $padding
     * @return string
     */
    public static function format_percentage(float $number, bool $sign, int $padding) : string
    {
        $str = '';

        if ($sign)
            $str = ($number > 0) ? '+' : '';

        $str .= number_format(100 * $number, 1, '.', '') .'%';

        $str = str_pad($str, $padding, ' ', STR_PAD_LEFT);

        if ($sign) {
            // add color
            if ($number > 0)
                $str = "\033[01;32m{$str}\033[0m";
            else
            if ($number < 0)
                $str = "\033[01;31m{$str}\033[0m";
        }

        return $str;
    }


    /**
     * Format bytes
     * @param  int $size
     * @param  int $precision
     * @return string
     * @note https://stackoverflow.com/a/2510540/10126479
     */
    public static function format_bytes(int $size, int $precision = 2) : string
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
    public static function all_measurements(array $cells) : string
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
    public static function outliers(array $cells) : string
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
    public static function check_functions_exist(array $functions) : array
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
    public static function not_random_bytes(int $length) : string
    {
        $str = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= chr(rand(0, 255));
        }

        return $str;
    }
}
