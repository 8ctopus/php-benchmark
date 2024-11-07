<?php

/**
 * Tests
 *
 * @author Alessandro Torrisi
 *
 * The original tests are available at http://www.php-benchmark-script.com
 */

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use ReflectionFunction;

class Tests
{
    public static function testIfElse() : void
    {
        $i = rand(0, 20);

        /** @disregard P1003 */
        $j = 0;

        if ($i % 2 === 0) {
            /** @disregard P1003 */
            $j = 1;
        } elseif ($i % 3 === 0) {
            /** @disregard P1003 */
            $j = 2;
        } elseif ($i % 5 === 0) {
            /** @disregard P1003 */
            $j = 3;
        } else {
            /** @disregard P1003 */
            $j = 4;
        }
    }

    public static function testLoops() : void
    {
        $j = 0;

        for ($i = 0; $i < 100; ++$i) {
            ++$j;
        }
    }

    public static function testArrays() : void
    {
        $a = [
            rand() => Helper::notRandomBytes(10),
        ];

        array_search(Helper::notRandomBytes(10), $a, true);
    }

    public static function testStrings() : void
    {
        $functions = ['addslashes', 'chunk_split', 'ltrim', 'metaphone', 'ord', 'str_shuffle',
            'strip_tags', 'strlen', 'strtoupper', 'strtolower', 'strrev', 'soundex', 'trim', ];

        // remove functions that don't exist
        $functions = Helper::cleanFunctions($functions);

        $string = 'the quick brown fox jumps over the lazy dog';

        foreach ($functions as $function) {
            call_user_func_array($function, [$string]);
        }
    }

    public static function testMath() : void
    {
        $functions = ['abs', 'acos', 'asin', 'atan', 'decbin', 'exp', 'floor', 'exp', 'is_finite',
            'is_nan', 'log', 'log10', 'log1p', 'pi', 'pow', 'sin', 'sqrt', 'tan', ];

        // remove functions that don't exist
        $functions = Helper::cleanFunctions($functions);

        foreach ($functions as $function) {
            // get function arguments count
            $reflection = new ReflectionFunction($function);

            $count = $reflection->getNumberOfParameters();

            switch ($count) {
                case 1:
                    $arguments = [rand(1, 100)];
                    break;

                case 2:
                    $arguments = [rand(1, 100), rand(1, 100)];
                    break;

                default:
                    $arguments = [];
                    break;
            }

            call_user_func_array($function, $arguments);
        }
    }

    public static function testHashes() : void
    {
        $hashes = ['adler32', 'crc32', 'crc32b', 'md5', 'sha1', 'sha256', 'sha384', 'sha512'];
        $string = Helper::notRandomBytes(1024);

        foreach ($hashes as $hash) {
            hash($hash, $string, false);
        }
    }

    public static function testFiles() : void
    {
        // max number of bytes to write
        $bytesToWriteMax = 0.5 * 1024 * 1024;
        $totalBytes = 0;

        // get temporary directory
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

        // list temp dir
        $list = scandir($tmpDir);

        if (!$list) {
            return;
        }

        // get temporary file name in temporary dir
        $tmpFilename = tempnam($tmpDir, '');

        if (!$tmpFilename) {
            return;
        }

        // open temp file
        $handle = fopen($tmpFilename, 'r+');

        if (!$handle) {
            return;
        }

        // get bytes count to write to file
        $bytesToWrite = rand(1, (int) $bytesToWriteMax);

        $totalBytes += $bytesToWrite;

        // write bytes to file
        /** @disregard P1003 */
        $result = fwrite($handle, Helper::notRandomBytes($bytesToWrite));

        // get file size
        $fileSize = filesize($tmpFilename);

        // get file size alternate
        /** @disregard P1003 */
        $stat = fstat($handle);

        // seek to random position
        /** @disregard P1003 */
        $result = fseek($handle, rand(1, $bytesToWrite));

        // get current position
        $position = ftell($handle);

        $maxBytesToRead = $fileSize - $position;

        // calculate bytes to read
        $bytesToRead = rand(1, $maxBytesToRead);

        $totalBytes += $bytesToRead;

        // read from file
        /** @disregard P1003 */
        $result = fread($handle, $bytesToRead);

        // close file
        fclose($handle);

        // delete file
        unlink($tmpFilename);

        //echo('total bytes : '. format_bytes($total_bytes, 2) ."\n");
    }
}
