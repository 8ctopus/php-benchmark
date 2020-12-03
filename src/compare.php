<?php

/**
 * Compare results
 * @author 8ctopus <hello@octopuslabs.io>
 */

// add assertions support
ini_set('zend.assertions', true);
ini_set('assert.exception', true);
//assert(false, __METHOD__ .'() unhandled situation');

// set error reporting
error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once('stats.php');
require_once('helper.php');

// check if running from cli
if (php_sapi_name() != 'cli') {
    echo('cli required');
    exit();
}

// get command line arguments
for ($i = 1; $i < count($argv); $i++)
{
    $argument = $argv[$i];

    if (strpos($argument, '--') != 0) {
        echo("unknown argument {$argument}");
        exit();
    }

    switch ($argument) {
        case '--file1':
            $i++;
            $file1 = $argv[$i];
            break;

        case '--file2':
            $i++;
            $file2 = $argv[$i];
            break;

        default:
            echo("unknown argument {$argument}");
            exit();
    }
}

// check for required variables
if (!isset($file1) || !isset($file2)) {
    echo("file1 and file2 required\n");
    exit();
}

// check files exist
if (!file_exists($file1) || !file_exists($file2)) {
    echo("valid file1 and file2 required\n");
    exit();
}

// paddings
$pad1     = 18;
$pad2     =  9;
$pad_line = $pad1 + 3 * $pad2 + 3;

$line = str_pad('', $pad_line, '-');

// get data sets
$data1 = unserialize(file_get_contents($file1));
$data2 = unserialize(file_get_contents($file2));

echo($line ."\n");

// compare data
foreach ($data1 as $test1 => $measurements1) {
    // get data2 measurements
    $measurements2 = $data2[$test1];

    // analyze test results
    $result1 = helper::analyze_test($measurements1);
    $result2 = helper::analyze_test($measurements2);

    // check for error
    if ($result1 === null || $result2 === null) {
        echo(str_pad($test1, $pad1) .' : '. str_pad('FAILED', $pad2, ' ', STR_PAD_LEFT) ."\n");
        echo($line ."\n");
        continue;
    }

    echo($test1 ."\n");

    // show test results
    foreach ($result1 as $key => $value1) {
        // get data2 result for key
        $value2 = $result2[$key];

        if ($key == 'normality')
            echo(str_pad($key, $pad1) .' : '. helper::format_percentage($value1, false, $pad2) . helper::format_percentage($value1, false, $pad2) ."\n");
        else {
            $delta = stats::relative_difference($value1, $value2);

            echo(str_pad($key, $pad1) .' : '. helper::format_number($value1, $pad2) . helper::format_number($value2, $pad2) . helper::format_percentage($delta, true, $pad2) ."\n");
        }
    }

    echo($line ."\n");
}
