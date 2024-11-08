<?php

declare(strict_types=1);

use Oct8pus\Benchmark\Benchmark;

require_once __DIR__ . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    throw new Exception('Please run the script from cli');
}

(new Benchmark($argv))
    ->run();
