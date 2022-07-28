<?php

require __DIR__ .'/../vendor/autoload.php';

define('LOG_STDOUT', true);

/**
 * Add your tests here
 *
 * @author 8ctopus <hello@octopuslabs.io>
 */
class tests
{
    /**
     * baseline 1
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function baseline_1(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here
            pow(2, 10);

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * baseline 2 (same test as baseline 1, just to test engine)
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function baseline_2(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here
            pow(2, 10);

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Equality variant 1
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function equal_1(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here

            $a = 1;
            $b = true;

            if ($a == $b) {
                $c = 1;
            } else {
                $c = 0;
            }

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Equality variant 1
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function equal_2(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here

            $a = 1;
            $b = true;

            if ($a === $b) {
                $c = 1;
            } else {
                $c = 0;
            }

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Regex variant 1
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function regex_1(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here

            // there's only one chance in 350 to see a zip string
            if (mt_rand(1, 350) == 1) {
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "';
            } else {
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "';
            }

            $result = preg_match('~GET /bin/(.*?)v\\d\\.\\d{3}\\.zip~', $string, $matches);

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Regex variant 2
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function regex_2(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here

            // there's only one chance in 350 to see a zip string
            if (mt_rand(1, 350) == 1) {
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "';
            } else {
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "';
            }

            if (strpos($string, '.zip') !== false) {
                $result = preg_match('~GET /bin/(.*?)v\\d\\.\\d{3}\\.zip~', $string, $matches);
            }

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    public static function str_br1(string $str) : string
    {
        return $str . PHP_EOL;
    }

    public static function str_br2(string &$str) : string
    {
        return $str . PHP_EOL;
    }

    /**
     * Pass function argument variant 1
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function fn_argument_1(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $str = 'hello world how are you doing today?';

        while (microtime(true) < $time_limit) {
            // test code starts here

            $str = self::str_br2($str);

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Pass function argument variant 2
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function fn_argument_2(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $str = 'hello world how are you doing today?';

        while (microtime(true) < $time_limit) {
            // test code starts here

            $str = self::str_br1($str);

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test monolog logger
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function logger_monolog(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $log = new Monolog\Logger('test');
        $log->pushHandler(new Monolog\Handler\StreamHandler('log_monolog.log', Monolog\Level::Warning));

        if (LOG_STDOUT) {
            // log to stdout
            $log->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Level::Warning));
        }

        while (microtime(true) < $time_limit) {
            // test code starts here

            $log->warning('test');

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test apix logger
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function logger_apix(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $file = new Apix\Log\Logger\File('log_apix.log');
        $file
            // intercept logs that are >= `warning`
            ->setMinLevel('warning')
            // don't propagate to further buckets
            ->setCascading(true)
            // postpone/accumulate logs processing
            ->setDeferred(true);

        $log = new Apix\Log\Logger([$file]);

        if (LOG_STDOUT) {
            $stdout = new Apix\Log\Logger\Stream('php://stdout', 'a');
            $stdout
                // intercept logs that are >= `warning`
                ->setMinLevel('warning')
                // don't propagate to further buckets
                ->setCascading(true)
                // postpone/accumulate logs processing
                ->setDeferred(true);

            $log->add($stdout);
        }

        while (microtime(true) < $time_limit) {
            // test code starts here

            $log->warning('test');

            // test code ends here
            ++$iterations;
        }

        return $iterations;
    }
}
