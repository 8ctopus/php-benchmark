<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use DivisionByZeroError;

class Helper
{
    // paddings
    public static int $pad1 = 19;
    public static int $pad2 = 14;

    /**
     * Analyze test results
     *
     * @param array $measurements
     *
     * @return array of strings or null if any of the test iterations failed
     */
    public static function analyzeTest(array $measurements) : ?array
    {
        // check if the test failed at least once
        if (in_array(false, $measurements, true)) {
            return null;
        }

        return [
            'mean' => Stats::mean($measurements),
            'median' => Stats::median($measurements),
            'mode' => Stats::mode($measurements),
            'minimum' => min($measurements),
            'maximum' => max($measurements),
            'quartile 1' => Stats::quartiles($measurements)[0],
            'quartile 3' => Stats::quartiles($measurements)[1],
            'IQ range' => Stats::interquartileRange($measurements),
            'std deviation' => Stats::standardDeviation($measurements),
            'normality' => Stats::testNormal($measurements),
        ];
    }

    /**
     * Show benchmark results
     *
     * @param array $data
     * @param array $settings
     *
     * @return void
     */
    public static function showBenchmark(array $data, array $settings) : void
    {
        $line = str_pad('', self::$pad1 + self::$pad2 + 3, '-');

        // analyze test results
        foreach ($data as $test => $measurements) {
            $result = self::analyzeTest($measurements);

            // check for error
            if ($result === null) {
                echo str_pad($test, self::$pad1) . ' : ' . str_pad('FAILED', self::$pad2, ' ', STR_PAD_LEFT) . "\n";
                echo $line . "\n";
                continue;
            }

            // show test results
            echo str_pad($test, self::$pad1) . ' : ' . str_pad('iterations', self::$pad2, ' ', STR_PAD_LEFT) . "\n";

            foreach ($result as $key => $value) {
                if ($key === 'normality') {
                    echo str_pad($key, self::$pad1) . ' : ' . self::formatPercentage($value, false, self::$pad2) . "\n";
                } else {
                    echo str_pad($key, self::$pad1) . ' : ' . self::formatNumber($value, self::$pad2) . "\n";
                }
            }

            // show histogram
            if ($settings['show_histogram']) {
                echo "\n";
                $histogram = Stats::histogram($measurements, $settings['histogram_buckets']);
                Stats::histogramDraw($histogram, $settings['histogram_bar_width']);
            }

            // output outliers
            if ($settings['show_outliers']) {
                echo "\n";
                echo str_pad('outliers', self::$pad1) . ' : ' . self::outliers($measurements) . "\n";
            }

            // output all measurements
            if ($settings['show_all_measurements']) {
                echo "\n";
                echo str_pad('values', self::$pad1) . ' : ' . self::allMeasurements($measurements) . "\n";
            }

            echo $line . "\n";
        }
    }

    /**
     * Show comparison
     *
     * @param array  $baseline
     * @param string $btitle
     * @param array  $latest
     * @param string $title
     *
     * @return void
     */
    public static function showCompare(array $baseline, string $btitle, array $latest, string $ltitle) : void
    {
        // paddings
        $line = str_pad('', self::$pad1 + 3 * self::$pad2 + 3, '-');

        echo $line . "\n";

        // compare tests
        foreach ($baseline as $test1 => $measurements1) {
            // get latest measurements
            $measurements2 = $latest[$test1];

            // analyze test results
            $result1 = self::analyzeTest($measurements1);
            $result2 = self::analyzeTest($measurements2);

            // check for error
            if ($result1 === null || $result2 === null) {
                echo str_pad($test1, self::$pad1) . ' : ' . str_pad('FAILED', self::$pad2, ' ', STR_PAD_LEFT) . "\n";
                echo $line . "\n";
                continue;
            }

            // show test results
            echo str_pad((string) $test1, self::$pad1) . ' : ' . str_pad($btitle, self::$pad2, ' ', STR_PAD_LEFT) . str_pad($ltitle, self::$pad2, ' ', STR_PAD_LEFT) . "\n";

            // show test results
            foreach ($result1 as $key => $value1) {
                // get latest result for key
                $value2 = $result2[$key];

                if ($key === 'normality') {
                    echo str_pad($key, self::$pad1) . ' : ' . self::formatPercentage($value1, false, self::$pad2) . self::formatPercentage($value1, false, self::$pad2) . "\n";
                } else {
                    try {
                        $delta = Stats::relativeDifference($value1, $value2);

                        echo str_pad($key, self::$pad1) . ' : ' . self::formatNumber($value1, self::$pad2) . self::formatNumber($value2, self::$pad2) . self::formatPercentage($delta, true, self::$pad2) . "\n";
                    } catch (DivisionByZeroError $exception) {
                        echo str_pad($key, self::$pad1) . ' : ' . self::formatNumber($value1, self::$pad2) . self::formatNumber($value2, self::$pad2) . str_pad('nan', self::$pad2, ' ', STR_PAD_LEFT) . "\n";
                    }
                }
            }

            echo $line . "\n";
        }
    }

    /**
     * Format number
     *
     * @param float $number
     * @param int   $padding
     *
     * @return string
     */
    public static function formatNumber(float $number, int $padding) : string
    {
        return str_pad(number_format($number, 0, '.', ''), $padding, ' ', STR_PAD_LEFT);
    }

    /**
     * Format percentage
     *
     * @param float $number
     * @param bool  $sign
     * @param int   $padding
     *
     * @return string
     */
    public static function formatPercentage(float $number, bool $sign, int $padding) : string
    {
        $str = '';

        if ($sign) {
            $str = ($number > 0) ? '+' : '';
        }

        $str .= number_format(100 * $number, 1, '.', '') . '%';

        $str = str_pad($str, $padding, ' ', STR_PAD_LEFT);

        if ($sign) {
            // add color
            if ($number > 0) {
                $str = "\033[01;32m{$str}\033[0m";
            } elseif ($number < 0) {
                $str = "\033[01;31m{$str}\033[0m";
            }
        }

        return $str;
    }

    /**
     * Format bytes
     *
     * @param float $size
     * @param int   $precision
     *
     * @return string
     *
     * @note https://stackoverflow.com/a/2510540/10126479
     */
    public static function formatBytes(float $size, int $precision = 2) : string
    {
        $base = log($size, 1024.0);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(1024 ** ($base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    /**
     * Get all array values as string
     *
     * @param array $cells
     *
     * @return string
     */
    public static function allMeasurements(array $cells) : string
    {
        $str = "\n\n";

        foreach ($cells as $key => $value) {
            $str .= self::formatNumber($value, 0) . ' ';

            if (!(($key + 1) % 32)) {
                $str .= "\n";
            }
        }

        return $str . "\n";
    }

    /**
     * Get outliers as string
     *
     * @param array $cells
     *
     * @return string
     */
    public static function outliers(array $cells) : string
    {
        $outliers = Stats::outliers($cells);

        $str = "\n\n";

        foreach ($outliers as $key => $outlier) {
            $str .= self::formatNumber($outlier, 0) . ' ';

            if (!(($key + 1) % 32)) {
                $str .= "\n";
            }
        }

        return $str . "\n";
    }

    /**
     * Clean not existing functions
     *
     * @param array $functions
     *
     * @return array
     */
    public static function cleanFunctions(array $functions) : array
    {
        // remove functions that don't exist
        foreach ($functions as $key => $function) {
            if (!function_exists($function)) {
                echo "Removed {$function} as it does not exist";
                unset($functions[$key]);
            }
        }

        return $functions;
    }

    /**
     * Create not random bytes string
     *
     * @param int $length
     *
     * @return string
     */
    public static function notRandomBytes(int $length) : string
    {
        $str = '';

        for ($i = 0; $i < $length; ++$i) {
            $str .= chr(rand(0, 255));
        }

        return $str;
    }
}
