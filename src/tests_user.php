<?php

/**
 * Add your tests here
 * @author 8ctopus <hello@octopuslabs.io>
 */

class tests
{
    /**
     * baseline 1
     * @param  float $limit time limit in seconds
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
     * @param  float $limit time limit in seconds
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
     * @param  float $limit time limit in seconds
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

            if ($a == $b)
                $c = 1;
            else
                $c = 0;

            // test code ends here
            $iterations++;
        }

        return $iterations;
    }


    /**
     * Equality variant 1
     * @param  float $limit time limit in seconds
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

            if ($a === $b)
                $c = 1;
            else
                $c = 0;

            // test code ends here
            $iterations++;
        }

        return $iterations;
    }


    /**
     * Regex variant 1
     * @param  float $limit time limit in seconds
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
            if (mt_rand(1, 350) == 1)
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "';
            else
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "';

            $result = preg_match("~GET /bin/(.*?)v\d\.\d{3}\.zip~", $string, $matches);

            // test code ends here
            $iterations++;
        }

        return $iterations;
    }


    /**
     * Regex variant 2
     * @param  float $limit time limit in seconds
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
            if (mt_rand(1, 350) == 1)
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "';
            else
                $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "';

            if (strpos($string, '.zip') !== false) {
                $result = preg_match("~GET /bin/(.*?)v\d\.\d{3}\.zip~", $string, $matches);
            }

            // test code ends here
            $iterations++;
        }

        return $iterations;
    }
}
