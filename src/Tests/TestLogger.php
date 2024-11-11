<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark\Tests;

use Apix\Log\Logger\File;
use Apix\Log\Logger;
use Apix\Log\Logger\Stream;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MLogger;

define('LOG_STDOUT', true);

class TestLogger
{
    public static function testMonolog() : void
    {
        $log = new MLogger('test');
        $log->pushHandler(new StreamHandler('log_monolog.log', Level::Warning));

        if (LOG_STDOUT) {
            $log->pushHandler(new StreamHandler('php://stdout', Level::Warning));
        }

        $log->warning('test');
    }

    public static function testApix() : void
    {
        $file = new File('log_apix.log');
        $file
            // intercept logs that are >= `warning`
            ->setMinLevel('warning')
            // don't propagate to further buckets
            ->setCascading(true)
            // postpone/accumulate logs processing
            ->setDeferred(true)
            // automatically flush when >= 200 logs
            ->setDeferredTrigger(200);

        $log = new Logger([$file]);

        if (LOG_STDOUT) {
            $stdout = new Stream('php://stdout', 'a');
            $stdout
                // intercept logs that are >= `warning`
                ->setMinLevel('warning')
                // don't propagate to further buckets
                ->setCascading(true)
                // postpone/accumulate logs processing
                ->setDeferred(true);

            $log->add($stdout);
        }

        $log->warning('test');
    }
}
