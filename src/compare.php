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

// get data sets
$data1 = unserialize(file_get_contents($file1));
$data2 = unserialize(file_get_contents($file2));

// show compare results
helper::show_compare($data1, 'file1', $data2, 'file2');
