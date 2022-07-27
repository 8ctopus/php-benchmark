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
    'iterations'            => 500,
    'time_per_iteration'    => 10,

    'filter_test'           => '/^test_/',
    'custom_tests'          => false,

    'compare'               => false,

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
if (php_sapi_name() != 'cli') {
    echo('Please run the script from cli');
    exit();
}

// get command line arguments
for ($i = 1; $i < count($argv); $i++)
{
    $argument = $argv[$i];

    if (strpos($argument, '--') !== 0) {
        echo("unknown argument {$argument}");
        exit();
    }

    switch ($argument) {
        case '--compare':
            $i++;
            $settings['compare'] = $argv[$i];
            break;

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
            if (!empty($argv[$i + 1]) && strpos($argv[$i + 1], '--') === false) {
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

require_once('stats.php');
require_once('helper.php');

// include either user or standard tests
if ($settings['custom_tests'])
    require_once('tests_user.php');
else
    require_once('tests.php');

$line = str_pad('', helper::$pad1 + helper::$pad2 + 3, '-');

echo('PHP benchmark' ."\n\n".
    "$line\n".
    str_pad('platform', helper::$pad1) .' : '. str_pad(PHP_OS .' '. ((PHP_INT_SIZE == 8) ? 'x64' : 'x32'), helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('php version', helper::$pad1) .' : '. str_pad(PHP_VERSION, helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('xdebug', helper::$pad1) .' : '. str_pad(extension_loaded('xdebug') ? 'on' : 'off', helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('memory limit', helper::$pad1) .' : '. str_pad(ini_get('memory_limit'), helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('max execution', helper::$pad1) .' : '. str_pad(ini_get('max_execution_time'), helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('time per iteration', helper::$pad1) .' : '. str_pad($settings['time_per_iteration'] .'ms', helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('iterations', helper::$pad1) .' : '. str_pad($settings['iterations'], helper::$pad2, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

// list tests
$tests = get_class_methods('tests');

// filter tests
foreach ($tests as $key => $test) {
    if (preg_match($settings['filter_test'], $test) === 0) {
        // remove test
        unset($tests[$key]);
    }
}

// cleanup array
$tests = array_values($tests);

// run tests
$save = [];

// run tests x times
for ($i = 0; $i < $settings['iterations']; $i++) {
    // update test progress
    $progress = helper::format_percentage($i / $settings['iterations'], false, 3);
    $text     = "Running tests {$progress}...";
    $len      = strlen($text);

    echo($text);
    echo("\033[{$len}D");

    if (!($i % 2)) {
        // start from first test
        for ($j = 0; $j < count($tests); ++$j) {
            $test = $tests[$j];
            $measurement = tests::$test($settings['time_per_iteration'] / 1000);

            if (!$i)
                $save[$test] = [$measurement];
            else
                array_push($save[$test], $measurement);

            // remove test if it failed
            //if ($measurement === null)
            //    unset($tests[$j]);
        }
    } else {
        // start from last test
        for ($j = count($tests) - 1; $j >= 0 ; --$j) {
            $test = $tests[$j];
            $measurement = tests::$test($settings['time_per_iteration'] / 1000);

            if (!$i)
                $save[$test] = [$measurement];
            else
                array_push($save[$test], $measurement);

            // remove test if it failed
            //if ($measurement === null)
            //    unset($tests[$j]);
        }
    }
}

// save results to file
if ($settings['save']) {
    if (empty($settings['save_filename']))
        $settings['save_filename'] = $settings['save_filename_base'] . $settings['save_filename_ext'];

    file_put_contents($settings['save_filename'], serialize($save));
    echo("benchmark saved to {$settings['save_filename']}\n");
    echo("$line\n");
}

if ($settings['custom_tests'] && count($tests) % 2 == 0) {

    $keys = array_keys($save);

    $test1 = array_values(array_slice($save, 0, 1, false));
    $test2 = array_values(array_slice($save, 1, 1, false));

    // compare custom tests
    helper::show_compare($test1, $keys[0], $test2, $keys[1]);
}
else
if ($settings['compare']) {
    // get compare data set
    $baseline = unserialize(file_get_contents($settings['compare']));

    // show comparison
    helper::show_compare($baseline, 'file', $save, 'test');
}
else
    helper::show_benchmark($save, $settings);

