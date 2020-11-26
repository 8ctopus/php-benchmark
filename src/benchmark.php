<?php

/**
 * PHP benchmark script
 * @author 8ctopus <hello@octopuslabs.io>
 */

// add assertions support
ini_set('zend.assertions', true);
ini_set('assert.exception', true);
//assert(false, __METHOD__ .'() unhandled situation');

// set error reporting
error_reporting(E_ERROR /*| E_WARNING */ | E_PARSE);

require_once('tests.php');
require_once('stats.php');
require_once('helper.php');

// settings
$settings = [
    'iterations'            => 100,
    'time_per_iteration'    => 50,
    'show_histogram'        => false,
    'histogram_buckets'     => 16,
    'histogram_bar_width'   => 50,
    'show_outliers'         => false,
    'show_all_measurements' => false,
    'save_to_file'          => false,
    'save_filename_base'    => 'benchmark_',
    'save_filename_extra'   => '',
    'save_filename_ext'     => date('Y-m-d-H-i-s') .'.txt',
];

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
                $settings['iterations'] = $argv[$i];
                break;

            case '--time-per-iteration':
                $i++;
                $settings['time_per_iteration'] = $argv[$i];
                break;

            case '--histogram':
                $settings['show_histogram'] = true;
                break;

            case '--histogram-buckets':
                $i++;
                $settings['histogram_buckets'] = $argv[$i];
                break;

            case '--histogram-width':
                $i++;
                $settings['histogram_bar_width'] = $argv[$i];
                break;

            case '--save':
                $settings['save_to_file'] = true;
                break;

            case '--save-extra':
                $i++;
                $settings['save_filename_extra'] = $argv[$i];
                break;

            case '--show-all':
                $settings['show_all_measurements'] = true;
                break;

            case '--show-outliers':
                $settings['show_outliers'] = true;
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
    str_pad('time per iteration', $pad1) .' : '. str_pad($settings['time_per_iteration'] .'ms', $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('iterations', $pad1) .' : '. str_pad($settings['iterations'], $pad2, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

// list tests
$tests = get_class_methods('tests');

// run tests
$save = [];

foreach ($tests as $test) {
    // filter tests
    if (preg_match('/^test_/', $test)) {
        $measurements = [];

        // run each test x times
        for ($i = 0; $i < $settings['iterations']; $i++) {
            $measurements[$i] = tests::$test($settings['time_per_iteration'] / 1000);

            if ($measurements[$i] === false) {
                $error = true;
                break;
            }
        }

        // analyze test results
        $result = helper::analyze_test($measurements);

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
                echo(str_pad($key, $pad1) .' : '. helper::format_percentage($value, $pad2) ."\n");
            else
                echo(str_pad($key, $pad1) .' : '. helper::format_number($value, $pad2) ."\n");
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
            echo(str_pad('outliers', $pad1) .' : '. helper::outliers($measurements) ."\n");
        }

        // output all measurements
        if ($settings['show_all_measurements']) {
            echo("\n");
            echo(str_pad('values', $pad1) .' : '. helper::all_measurements($measurements) ."\n");
        }

        echo($line ."\n");
    }

    // save test
    $save[$test] = $measurements;
}

// save to file
if ($settings['save_to_file']) {

    if (!empty($settings['save_filename_extra']))
        $settings['save_filename_extra'] .= '_';

    $file = $settings['save_filename_base'] . $settings['save_filename_extra'] . $settings['save_filename_ext'];

    file_put_contents($file, serialize($save));
    echo("benchmark saved to {$file}\n");
}
