<?php

declare(strict_types=1);

namespace Oct8pus\Tests;

class TestUser
{
    /**
     * Baseline 1 (same as Baseline 2, just to test equality)
     */
    public static function baseline1() : void
    {
        pow(2, 10);
    }

    public static function baseline2() : void
    {
        pow(2, 10);
    }

    public static function equal1() : void
    {
        $a = 1;
        $b = "1";
        /** @disregard P1003 */
        $c = 0;

        if ($a == $b) {
            /** @disregard P1003 */
            $c = 1;
        } else {
            /** @disregard P1003 */
            $c = 2;
        }
    }

    public static function equal2() : void
    {
        $a = 1;
        $b = "1";
        /** @disregard P1003 */
        $c = 0;

        if ($a === $b) {
            /** @disregard P1003 */
            $c = 1;
        } else {
            /** @disregard P1003 */
            $c = 2;
        }
    }

    public static function regex1() : void
    {
        // there's only one chance in 350 to see a zip string
        if (mt_rand(1, 350) === 1) {
            $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "';
        } else {
            $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "';
        }

        /** @disregard P1003 */
        $result = preg_match('~GET /bin/(.*?)v\d\.\d{3}\.zip~', $string, $matches);
    }

    public static function regex2() : void
    {
        // there's only one chance in 350 to see a zip string
        if (mt_rand(1, 350) === 1) {
            $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /bin/filev1.048.zip HTTP/2.0" 200 11853462 "';
        } else {
            $string = '8.8.8.8 - - [01/Dec/2020:06:56:08 +0100] "GET /css/someotherfile.css HTTP/2.0" 200 11853462 "';
        }

        if (strpos($string, '.zip') !== false) {
            /** @disregard P1003 */
            $result = preg_match('~GET /bin/(.*?)v\d\.\d{3}\.zip~', $string, $matches);
        }
    }

    private static function strBr1(string $str) : string
    {
        return $str . PHP_EOL;
    }

    private static function strBr2(string &$str) : string
    {
        return $str . PHP_EOL;
    }

    public static function fnArgument1() : void
    {
        $str = 'hello world how are you doing today?';

        $str = self::strBr2($str);
    }

    public static function fnArgument2() : void
    {
        $str = 'hello world how are you doing today?';

        $str = self::strBr1($str);
    }
}
