<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use Exception;

require_once __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    throw new Exception('run from cli');
}

for ($i = 1; $i < count($argv); ++$i) {
    $argument = $argv[$i];

    if (strpos($argument, '--') !== 0) {
        throw new Exception("unknown argument - {$argument}");
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
            throw new Exception("unknown argument - {$argument}");
    }
}

if (!isset($file1) || !isset($file2) || !file_exists($file1) || !file_exists($file2)) {
    throw new Exception('both files must exist');
}

$data1 = unserialize(file_get_contents($file1));
$data2 = unserialize(file_get_contents($file2));

Helper::showCompare($data1, $data2);
