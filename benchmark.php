<?php

/**
 * PHP benchmark script
 * @author 8ctopus <hello@octopuslabs.io>
 */

// add assertions support
ini_set('zend.assertions', true);
ini_set('assert.exception', true);
//assert(false, __METHOD__ .'() unhandled situation');

require_once('stats.php');
require_once('tests.php');

// settings
$iterations            = 100;
$time_per_iteration    = 50;
$show_histogram        = true;
$histogram_buckets     = 16;
$histogram_bar_width   = 50;
$show_all_measurements = false;

require_once('stats.php');
require_once('tests.php');

// set error reporting
error_reporting(E_ERROR /*| E_WARNING | E_PARSE*/);

// check if running from cli
if (php_sapi_name() == 'cli') {
    // get command line arguments
    for ($i = 1; $i < count($argv); $i++)
    {
        $argument = $argv[$i];

        if (strpos($argument, '--') != 0) {
            echo("unknown argument {$argument}");
            exit();
        }

        switch ($argument) {
            case '--iterations':
                $i++;
                $iterations = $argv[$i];
                break;

            case '--time-per-iteration':
                $i++;
                $time_per_iteration = $argv[$i];
                break;

            case '--histogram':
                $show_histogram = true;
                break;

            case '--histogram-buckets':
                $i++;
                $histogram_buckets = $argv[$i];
                break;

            case '--histogram-width':
                $i++;
                $histogram_bar_width = $argv[$i];
                break;

            case '--show-all':
                $show_all_measurements = true;
                break;

            default:
                echo("unknown argument {$argument}");
                exit();
        }
    }
}
else
    echo('<pre>');


// paddings
$pad1     = 18;
$pad2     =  9;
$pad_line = $pad1 + $pad2 + 3;

$line = str_pad('', $pad_line, '-');

echo('PHP benchmark' ."\n\n".
    "$line\n".
    str_pad('php version', $pad1) .' : '. str_pad(PHP_VERSION, $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('platform', $pad1) .' : '. str_pad(PHP_OS .' '. ((PHP_INT_SIZE == 8) ? 'x64' : 'x32'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('memory limit', $pad1) .' : '. str_pad(ini_get('memory_limit'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('max execution', $pad1) .' : '. str_pad(ini_get('max_execution_time'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('time per iteration', $pad1) .' : '. str_pad($time_per_iteration .'ms', $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('iterations', $pad1) .' : '. str_pad($iterations, $pad2, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

// list tests
$tests = get_class_methods('tests');

// run tests
foreach ($tests as $test) {
    // filter tests
    if (preg_match('/^test_/', $test)) {
        $measurements = [];

        // run each test x times
        for ($i = 0; $i < $iterations; $i++) {
            $measurements[$i] = tests::$test($time_per_iteration / 1000);

            if ($measurements[$i] === false) {
                $error = true;
                break;
            }
        }

        // analyze test results
        $result = analyze_test($measurements);

        // check for error
        if ($result === false) {
            echo(str_pad($test, $pad1) .' : '. str_pad('FAILED', $pad2, ' ', STR_PAD_LEFT) ."\n");
            echo($line ."\n");
            continue;
        }

        echo($test ."\n");

        // show test results
        foreach ($result as $key => $value) {
            if ($key == 'normality')
                echo(str_pad($key, $pad1) .' : '. format_number($value, $pad2 -1) ."%\n");
            else
                echo(str_pad($key, $pad1) .' : '. format_number($value, $pad2) ."\n");
        }

        echo("\n");

        // show histogram
        if ($show_histogram) {
            $histogram = stats::histogram($measurements, $histogram_buckets);
            stats::histogram_draw($histogram, $histogram_bar_width);
        }

        // output all measurements
        if ($show_all_measurements) {
            echo("\n");
            echo(str_pad('values', $pad1) .' : '. all_measurements($measurements) ."\n");
        }

        echo($line ."\n");
    }
}

exit();


/**
 * Analyze test results
 * @param  array $measurements
 * @return array of strings or false if any of the test iterations failed
 */
function analyze_test(array $measurements)
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
        'normality'     => stats::test_normal($measurements) * 100,
    ];
}


/**
 * Format number
 * @param  int $number
 * @param  int $padding
 * @return string
 */
function format_number(int $number, int $padding)
{
    return str_pad(number_format($number, 0, '.', ''), $padding, ' ', STR_PAD_LEFT);
}


/**
 * Format bytes
 * @param  int $size
 * @param  int $precision
 * @return string
 * @note https://stackoverflow.com/a/2510540/10126479
 */
function format_bytes(int $size, int $precision = 2)
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
function all_measurements(array $cells)
{
    $str = "\n\n";

    foreach ($cells as $key => $value) {
        $str .= format_number($value, 0) .' ';

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
function check_functions_exist(array $functions)
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
