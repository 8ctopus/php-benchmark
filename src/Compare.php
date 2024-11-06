<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use Exception;

// add assertions support
ini_set('zend.assertions', true);
ini_set('assert.exception', true);
//assert(false, __METHOD__ .'() unhandled situation');

error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    throw new Exception('run from cli');
}

// get command line arguments
for ($i = 1; $i < count($argv); ++$i) {
    $argument = $argv[$i];

    if (strpos($argument, '--') !== 0) {
        throw new Exception("unknown argument {$argument}");
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
            throw new Exception("unknown argument {$argument}");
    }
}

// check for required variables
if (!isset($file1) || !isset($file2)) {
    throw new Exception('file1 and file2 required');
}

// check files exist
if (!file_exists($file1) || !file_exists($file2)) {
    throw new Exception('valid file1 and file2 required');
}

// get data sets
$data1 = unserialize(file_get_contents($file1));
$data2 = unserialize(file_get_contents($file2));

Helper::showCompare($data1, 'file1', $data2, 'file2');
