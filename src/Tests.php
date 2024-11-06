<?php

/**
 * Tests
 *
 * @author Alessandro Torrisi
 *
 * The original tests are available at http://www.php-benchmark-script.com
 *
 * modified version author
 * @author 8ctopus <hello@octopuslabs.io>
 */

declare(strict_types=1);

class Tests
{
    /**
     * Test if else
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function testIfElse(float $limit) : int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        $i = 0;
        /** @disregard P1003 */
        $j = 0;

        while (microtime(true) < $timeLimit) {
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

            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test loops
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function testLoops(float $limit) : int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        while (microtime(true) < $timeLimit) {
            $j = 0;

            for ($i = 0; $i < 100; ++$i) {
                ++$j;
            }

            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test arrays
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function testArrays(float $limit) : int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        $a = [];

        while (microtime(true) < $timeLimit) {
            $a[] = [
                rand() => Helper::notRandomBytes(10),
            ];

            array_search(Helper::notRandomBytes(10), $a, true);

            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test string functions
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function testStrings(float $limit) : int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        $functions = ['addslashes', 'chunk_split', 'ltrim', 'metaphone', 'ord', 'str_shuffle',
            'strip_tags', 'strlen', 'strtoupper', 'strtolower', 'strrev', 'soundex', 'trim', ];

        // remove functions that don't exist
        $functions = Helper::checkFunctions($functions);

        $string = 'the quick brown fox jumps over the lazy dog';

        // run tests
        while (microtime(true) < $timeLimit) {
            foreach ($functions as $function) {
                call_user_func_array($function, [$string]);
            }

            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test math functions
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function testMath(float $limit) : int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        $functions = ['abs', 'acos', 'asin', 'atan', 'decbin', 'exp', 'floor', 'exp', 'is_finite',
            'is_nan', 'log', 'log10', 'log1p', 'pi', 'pow', 'sin', 'sqrt', 'tan', ];

        // remove functions that don't exist
        $functions = Helper::checkFunctions($functions);

        // run tests
        while (microtime(true) < $timeLimit) {
            foreach ($functions as $function) {
                // get function arguments count
                $reflection = new ReflectionFunction($function);
                $count = $reflection->getNumberOfParameters();

                switch ($count) {
                    case 1:
                        $arguments = [$iterations + 1];
                        break;

                    case 2:
                        $arguments = [$iterations + 1, $iterations + 1];
                        break;

                    default:
                        $arguments = [];
                        break;
                }

                call_user_func_array($function, $arguments);
            }

            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test cryptographic hashes
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time
     */
    public static function testHashes(float $limit) : int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        $hashes = ['adler32', 'crc32', 'crc32b', 'md5', 'sha1', 'sha256', 'sha384', 'sha512'];
        $string = Helper::notRandomBytes(1024);

        while (microtime(true) < $timeLimit) {
            foreach ($hashes as $hash) {
                hash($hash, $string, false);
            }

            ++$iterations;
        }

        return $iterations;
    }

    /**
     * Test file operations
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time or null on failure
     */
    public static function testFiles(float $limit) : ?int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        // max number of bytes to write
        $bytes_to_write_max = 0.5 * 1024 * 1024;
        $total_bytes = 0;

        // get temporary directory
        $tmp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

        while (microtime(true) < $timeLimit) {
            // scan temp dir
            $list = scandir($tmp_dir);

            if (!$list) {
                return null;
            }

            // get temporary file name in temporary dir
            $tmp_filename = tempnam($tmp_dir, '');

            if (!$tmp_filename) {
                return null;
            }

            // open temp file
            $handle = fopen($tmp_filename, 'r+');

            if (!$handle) {
                return null;
            }

            // get bytes count to write to file
            $bytes_to_write = rand(1, (int) $bytes_to_write_max);

            $total_bytes += $bytes_to_write;

            // write bytes to file
            /** @disregard P1003 */
            $result = fwrite($handle, Helper::notRandomBytes($bytes_to_write));

            // get file size
            $file_size = filesize($tmp_filename);

            // get file size alternate
            /** @disregard P1003 */
            $stat = fstat($handle);

            // seek to random position
            /** @disregard P1003 */
            $result = fseek($handle, rand(1, $bytes_to_write));

            // get current position
            $position = ftell($handle);

            $max_bytes_to_read = $file_size - $position;

            // calculate bytes to read
            $bytes_to_read = rand(1, $max_bytes_to_read);

            $total_bytes += $bytes_to_read;

            // read from file
            /** @disregard P1003 */
            $result = fread($handle, $bytes_to_read);

            // close file
            fclose($handle);

            // delete file
            unlink($tmp_filename);

            ++$iterations;
        }

        //echo('total bytes : '. format_bytes($total_bytes, 2) ."\n");
        return $iterations;
    }

    /**
     * Test mysql operations
     *
     * @param float $limit time limit in seconds
     *
     * @return int iterations done in allocated time or null on failure
     */
    public static function testMySql(float $limit) : ?int
    {
        $timeStarted = microtime(true);
        $timeLimit = $timeStarted + $limit;
        $iterations = 0;

        $host = 'localhost';
        $user = 'root';
        $pass = '123';
        $db = 'benchmark-test';
        $mysqli = null;
        $dbCreated = false;
        $exception = false;

        if (!function_exists('mysqli_connect')) {
            return null;
        }

        try {
            while (microtime(true) < $timeLimit) {
                // connect to database
                $mysqli = mysqli_connect($host, $user, $pass);

                if (!$mysqli) {
                    throw new Exception('Connect to database - FAILED');
                }

                if (!$iterations) {
                    // check if database already exists
                    $query = <<<TAG
                    SELECT
                        SCHEMA_NAME
                    FROM
                        INFORMATION_SCHEMA.SCHEMATA
                    WHERE
                        SCHEMA_NAME = '{$db}'
                    TAG;

                    $result = mysqli_query($mysqli, $query);

                    if (!$result) {
                        throw new Exception('Check if database exists - FAILED');
                    }

                    $array = mysqli_fetch_array($result);

                    if (isset($array)) {
                        throw new Exception('Database already exists');
                    }

                    // create database
                    $query = <<<TAG
                    CREATE DATABASE `{$db}`;
                    TAG;

                    if (!mysqli_query($mysqli, $query)) {
                        throw new Exception('Create database - FAILED');
                    }

                    $dbCreated = true;
                }

                // select database
                if (!mysqli_select_db($mysqli, $db)) {
                    throw new Exception('Select database - FAILED');
                }

                if (!$iterations) {
                    // create table
                    $table = 'test';
                    $query = <<<TAG
                    CREATE TABLE `{$table}` (
                        `date` timestamp NOT NULL,
                        `string` varchar(512) NOT NULL
                    );
                    TAG;

                    if (!mysqli_query($mysqli, $query)) {
                        throw new Exception('Create table - FAILED');
                    }
                }

                // insert into table
                $str = bin2hex(Helper::notRandomBytes(rand(1, 256)));

                $query = <<<TAG
                INSERT INTO
                    `{$table}` (`date`, `string`)
                VALUES
                    (CURRENT_TIMESTAMP, '{$str}');
                TAG;

                if (!mysqli_query($mysqli, $query)) {
                    throw new Exception('Insert into table - FAILED');
                }

                // select from table
                $query = <<<TAG
                SELECT
                    *
                FROM
                    `{$table}`
                WHERE
                    1;
                TAG;

                $result = mysqli_query($mysqli, $query);

                if (!$result) {
                    throw new Exception('Select from table - FAILED');
                }

                $array = mysqli_fetch_array($result);

                if (!$array) {
                    throw new Exception('Select from table - FAILED');
                }

                // disconnect from database
                mysqli_close($mysqli);

                ++$iterations;
            }
        } catch (Exception $exception) {
            $exception = true;
            //echo($exception->getMessage() ."\n");
        } finally {
            // check for connection failure
            if (!$mysqli) {
                return null;
            }

            if (!$exception) {
                // connect to database
                $mysqli = mysqli_connect($host, $user, $pass);
            }

            if ($dbCreated) {
                // drop database
                $query = <<<TAG
                DROP DATABASE `{$db}`;
                TAG;

                mysqli_query($mysqli, $query);
            }

            // disconnect from database
            mysqli_close($mysqli);
        }

        return $exception ? null : $iterations;
    }
}
