<?php

/**
 * Add your tests here
 * @author 8ctopus <hello@octopuslabs.io>
 */

class tests
{
    /**
     * Custom test 1
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_1(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here
            $a = [
                'a' => 1,
                'b' => 2,
            ];

            $b = json_encode($a);

            // test code ends here
            $iterations++;
        }

        return $iterations;
    }


    /**
     * Custom test 2
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_2(float $limit) : int
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            // test code starts here
            $a = [
                'a' => 1,
                'b' => 2,
            ];

            $b = json_encode($a);
            // test code ends here
            $iterations++;
        }

        return $iterations;
    }
}
