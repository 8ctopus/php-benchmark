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

// settings
$settings = [
    'iterations'            => 100,
    'time_per_iteration'    => 50,

    'filter_test'           => '/^test_/',
    'custom_tests'          => false,

    'show_histogram'        => false,
    'histogram_buckets'     => 16,
    'histogram_bar_width'   => 50,

    'show_outliers'         => false,
    'show_all_measurements' => false,

    'save'                  => false,
    'save_filename'         => '',
    'save_filename_base'    => 'benchmark_',
    'save_filename_ext'     => date('Ymd-Hi') .'.txt',
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
            case '--custom':
                $settings['custom_tests'] = true;
                break;

            case '--filter':
                $i++;
                $settings['filter_test'] = $argv[$i];
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

            case '--iterations':
                $i++;
                $settings['iterations'] = $argv[$i];
                break;

            case '--save':
                $settings['save'] = true;
                if (!empty($argv[$i]) && strpos($argument, '--') == 0) {
                    $i++;
                    $settings['save_filename'] = $settings['save_filename_base'] . $argv[$i] .'_'. $settings['save_filename_ext'];
                }

                break;

            case '--show-all':
                $settings['show_all_measurements'] = true;
                break;

            case '--show-outliers':
                $settings['show_outliers'] = true;
                break;

            case '--time-per-iteration':
                $i++;
                $settings['time_per_iteration'] = $argv[$i];
                break;

            default:
                echo("unknown argument {$argument}");
                exit();
        }
    }
}
else
    echo('<pre>');

require_once('stats.php');
require_once('helper.php');

// include either user or standard tests
if ($settings['custom_tests'])
    require_once('tests_user.php');
else
    require_once('tests.php');

// paddings
$pad1     = 18;
$pad2     = 10;
$pad_line = $pad1 + $pad2 + 3;

$line = str_pad('', $pad_line, '-');

echo('PHP benchmark' ."\n\n".
    "$line\n".
    str_pad('platform', $pad1) .' : '. str_pad(PHP_OS .' '. ((PHP_INT_SIZE == 8) ? 'x64' : 'x32'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('php version', $pad1) .' : '. str_pad(PHP_VERSION, $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('xdebug', $pad1) .' : '. str_pad(extension_loaded('xdebug') ? 'on' : 'off', $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('memory limit', $pad1) .' : '. str_pad(ini_get('memory_limit'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('max execution', $pad1) .' : '. str_pad(ini_get('max_execution_time'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('time per iteration', $pad1) .' : '. str_pad($settings['time_per_iteration'] .'ms', $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('iterations', $pad1) .' : '. str_pad($settings['iterations'], $pad2, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

// list tests
$tests = get_class_methods('tests');

// filter tests
foreach ($tests as $key => $test)
    if (preg_match($settings['filter_test'], $test) == 0)
        // remove test
        unset($tests[$key]);

// run tests
$save = [];

foreach ($tests as $test) {
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
    if ($result === null) {
        echo(str_pad($test, $pad1) .' : '. str_pad('FAILED', $pad2, ' ', STR_PAD_LEFT) ."\n");
        echo($line ."\n");
        continue;
    }

    // show test results
    echo(str_pad($test, $pad1) .' : '. str_pad('iterations', $pad2, ' ', STR_PAD_LEFT) ."\n");

    foreach ($result as $key => $value) {
        if ($key == 'normality')
            echo(str_pad($key, $pad1) .' : '. helper::format_percentage($value, false, $pad2) ."\n");
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

    // save test
    $save[$test] = $measurements;
}

// save to file
if ($settings['save']) {

    if (empty($settings['save_filename']))
        $settings['save_filename'] = $settings['save_filename_base'] . $settings['save_filename_ext'];

    file_put_contents($settings['save_filename'], serialize($save));
    echo("benchmark saved to {$settings['save_filename']}\n");
}
