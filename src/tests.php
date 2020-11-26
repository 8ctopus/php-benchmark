<?php

/**
 * Tests
 * original author
 * @author Alessandro Torrisi
 * The original tests are available at http://www.php-benchmark-script.com
 *
 * modified version author
 * @author 8ctopus <hello@octopuslabs.io>
 */

class tests
{
    /**
     * Test math functions
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_math(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $functions  = ['abs', 'acos', 'asin', 'atan', 'decbin', 'exp', 'floor', 'exp', 'is_finite',
            'is_nan', 'log', 'log10', 'log1p', 'pi', 'pow', 'sin', 'sqrt', 'tan'];

        // remove functions that don't exist
        $functions = check_functions_exist($functions);

        // run tests
        while (microtime(true) < $time_limit) {
            foreach ($functions as $function) {
                // get function arguments count
                $reflection = new ReflectionFunction($function);
                $arguments  = $reflection->getNumberOfParameters();

                call_user_func_array($function, $arguments == 1 ? [$iterations + 1] : [$iterations + 1, $iterations + 1]);
            }

            $iterations++;
        }

        return $iterations;
    }


    /**
     * Test string functions
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_strings(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $functions  = ['addslashes', 'chunk_split', 'ltrim', 'metaphone', 'ord', 'str_shuffle',
            'strip_tags', 'strlen', 'strtoupper', 'strtolower', 'strrev', 'soundex', 'trim'];

        // remove functions that don't exist
        $functions = check_functions_exist($functions);

        $string = 'the quick brown fox jumps over the lazy dog';

        // run tests
        while (microtime(true) < $time_limit) {
            foreach ($functions as $function) {
                call_user_func_array($function, [$string]);
            }

            $iterations++;
        }

        return $iterations;
    }


    /**
     * Test loops
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_loops(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        while (microtime(true) < $time_limit) {
            $j = 0;

            for ($i = 0; $i < 100; $i++) {
                $j++;
            }

            $iterations++;
        }

        return $iterations;
    }


    /**
     * Test if else
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_if_else(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $i = 0;
        $j = 0;

        while (microtime(true) < $time_limit) {
            if ($i % 2 == 0) {
                $j = 1;
            }
            elseif ($i % 3 == 0) {
                $j = 2;
            }
            else
            if ($i % 5 == 0) {
                $j = 3;
            }
            else {
                $j = 4;
            }

            $iterations++;
        }

        return $iterations;
    }


    /**
     * Test arrays
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    public static function test_arrays(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $a = [];

        while (microtime(true) < $time_limit) {
            array_push($a, [
                rand() => random_bytes(10)
            ]);

            array_search(random_bytes(10), $a, true);

            $iterations++;
        }

        return $iterations;
    }


    /**
     * Test cryptographic hashes
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time
     */
    function test_hashes(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $hashes = ['adler32', 'crc32', 'crc32b', 'md5', 'sha1', 'sha256', 'sha384', 'sha512'];
        $string = random_bytes(1024);

        while (microtime(true) < $time_limit) {
            foreach ($hashes as $hash) {
                hash($hash, $string, false);
            }

            $iterations++;
        }

        return $iterations;
    }


    /**
     * Test file operations
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time or false on failure
     */
    public static function test_files(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        // max number of bytes to write
        $bytes_to_write_max = 0.5 * 1024 * 1024;
        $total_bytes        = 0;

        // get temporary directory
        $tmp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

        while (microtime(true) < $time_limit) {
            // scan temp dir
            $list = scandir($tmp_dir);

            if (!$list)
                return false;

            // get temporary file name in temporary dir
            $tmp_filename = tempnam($tmp_dir, '');

            if (!$tmp_filename)
                return false;

            // open temp file
            $handle = fopen($tmp_filename, 'r+');

            if (!$handle)
                return false;

            // get bytes count to write to file
            $bytes_to_write = rand(1, $bytes_to_write_max);

            $total_bytes += $bytes_to_write;

            // write bytes_to_write bytes to file
            $result = fwrite($handle, random_bytes($bytes_to_write));

            // seek to random position
            $result = fseek($handle, rand(1, $bytes_to_write));

            // get current position
            $position = ftell($handle);

            // get file size
            $file_size = filesize($tmp_filename);

            // calculate bytes to read
            $bytes_to_read = rand(1, $file_size - $position);

            $total_bytes += $bytes_to_read;

            // read from file
            $result = fread($handle, $bytes_to_read);

            // close file
            fclose($handle);

            // delete file
            unlink($tmp_filename);

            $iterations++;
        }

        //echo('total bytes : '. format_bytes($total_bytes) ."\n");
        return $iterations;
    }


    /**
     * Test mysql operations
     * @param  float $limit time limit in seconds
     * @return int iterations done in allocated time or false on failure
     */
    public static function test_mysql(float $limit)
    {
        $time_start = microtime(true);
        $time_limit = $time_start + $limit;
        $iterations = 0;

        $host       = 'localhost';
        $user       = 'root';
        $pass       = '123';
        $db         = 'benchmark-test';
        $mysqli     = null;
        $db_created = false;
        $exception  = false;

        try {
            while (microtime(true) < $time_limit) {
                // connect to database
                $mysqli = mysqli_connect($host, $user, $pass);

                if (!$mysqli)
                    throw new Exception('Connect to database - FAILED');

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

                    if (!$result)
                        throw new Exception('Check if database exists - FAILED');

                    $array = mysqli_fetch_array($result);

                    if (isset($array))
                        throw new Exception('Database already exists');

                    // create database
                    $query = <<<TAG
                        CREATE DATABASE `{$db}`;
                    TAG;

                    if (!mysqli_query($mysqli, $query))
                        throw new Exception('Create database - FAILED');

                    $db_created = true;
                }

                // select database
                if (!mysqli_select_db($mysqli, $db))
                    throw new Exception('Select database - FAILED');

                if (!$iterations) {
                    // create table
                    $table = 'test';
                    $query = <<<TAG
                        CREATE TABLE `{$table}` (
                            `date` timestamp NOT NULL,
                            `string` varchar(512) NOT NULL
                        );
                    TAG;

                    if (!mysqli_query($mysqli, $query))
                        throw new Exception('Create table - FAILED');
                }

                // insert into table
                $str = bin2hex(random_bytes(rand(1, 256)));

                $query = <<<TAG
                    INSERT INTO
                        `{$table}` (`date`, `string`)
                    VALUES
                        (CURRENT_TIMESTAMP, '{$str}');
                TAG;

                if (!mysqli_query($mysqli, $query))
                    throw new Exception('Insert into table - FAILED');

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

                if (!$result)
                    throw new Exception('Select from table - FAILED');

                $array = mysqli_fetch_array($result);

                if (!$array)
                    throw new Exception('Select from table - FAILED');

                // disconnect from database
                mysqli_close($mysqli);

                $iterations++;
            }
        }
        catch (Exception $e) {
            $exception = true;
            //echo($e->getMessage() ."\n");
        }
        finally {
            // check for connection failure
            if (!$mysqli)
                return false;

            if (!$exception)
                // connect to database
                $mysqli = mysqli_connect($host, $user, $pass);

            if ($db_created) {
                // drop database
                $query = <<<TAG
                    DROP DATABASE `{$db}`;
                TAG;

                mysqli_query($mysqli, $query);
            }

            // disconnect from database
            mysqli_close($mysqli);
        }

        return !$exception ? $iterations : false;
    }
}
