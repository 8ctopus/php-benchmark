<?php

/**
 * PHP benchmark script
 *
 * original author
 * @author Alessandro Torrisi
 * The original version of the script is available at http://www.php-benchmark-script.com
 *
 * modified version author
 * @author 8ctopus <hello@octopuslabs.io>
 */

// settings
$iterations            = 100;
$time_per_iteration    = 50;
$show_histogram        = true;
$show_all_measurements = false;

require_once('stats.php');

// set error reporting
error_reporting(E_ERROR /*| E_WARNING | E_PARSE*/);

// check if running from cli
if (php_sapi_name() != 'cli')
    echo('<pre>');

// paddings
$pad1     = 18;
$pad2     =  9;
$pad_line = $pad1 + $pad2 + 3;

$line = str_pad('', $pad_line, '-');

echo('PHP benchmark' ."\n\n".
    "$line\n".
    str_pad('php version', $pad1) .' : '. str_pad(PHP_VERSION, $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('platform', $pad1) .' : '. str_pad(PHP_OS .' '. ((PHP_INT_SIZE == 8) ? 'x64' : 'x32'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('memory limit', $pad1) .' : '. str_pad(ini_get('memory_limit'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('max execution', $pad1) .' : '. str_pad(ini_get('max_execution_time'), $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('time per iteration', $pad1) .' : '. str_pad($time_per_iteration .'ms', $pad2, ' ', STR_PAD_LEFT) ."\n".
    str_pad('iterations', $pad1) .' : '. str_pad($iterations, $pad2, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

// list functions
$functions = get_defined_functions();

// run tests
foreach ($functions['user'] as $user) {
    // check if function starts with test
    if (preg_match('/^test_/', $user)) {
        $timings = [];

        // run each test x times
        for ($i = 0; $i < $iterations; $i++) {
            $timings[$i] = $user($time_per_iteration / 1000);

            if ($timings[$i] === false) {
                $error = true;
                break;
            }
        }

        // analyze test results
        $result = analyze_test($timings);

        // check for error
        if ($result === false) {
            echo(str_pad($user, $pad1) .' : '. str_pad('FAILED', $pad2, ' ', STR_PAD_LEFT) ."\n");
            echo($line ."\n");
            continue;
        }

        echo($user ."\n");

        // show test results
        foreach ($result as $key => $value) {
            echo(str_pad($key, $pad1) .' : '. format_number($value, $pad2) ."\n");
        }

        echo("\n");

        // show histogram
        if ($show_histogram) {
            $buckets = 16;
            $histogram = stats::histogram($timings, $buckets);
            stats::histogram_draw($histogram);
        }

        // output all measurements
        if ($show_all_measurements) {
            echo("\n");
            echo(str_pad('values', $pad1) .' : '. all_measurements($timings) ."\n");
        }

        echo($line ."\n");
    }
}

exit();


/**
 * Test math functions
 * @param  float $limit time limit in seconds
 * @return int iterations done in allocated time
 */
function test_math(float $limit)
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
function test_strings(float $limit)
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
function test_loops(float $limit)
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
function test_if_else(float $limit)
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
function test_arrays(float $limit)
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
function test_files(float $limit)
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
function test_mysql(float $limit)
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


/**
 * Analyze test results
 * @param  array $timings
 * @return array of strings or false if any of the test iterations failed
 */
function analyze_test(array $timings)
{
    // check if the test failed at least once
    if (in_array(false, $timings))
        return false;

    return [
        'average'       => stats::average($timings),
        'median'        => stats::median($timings),
        'minmum'        => min($timings),
        'maximum'       => max($timings),
        'std deviation' => stats::standard_deviation($timings),
    ];
}


/**
 * Format number
 * @param  int $number
 * @param  int $padding
 * @return string
 */
function format_number(int $number, int $padding)
{
    return str_pad(number_format($number, 0, '.', ''), $padding, ' ', STR_PAD_LEFT);
}


/**
 * Format bytes
 * @param  int $size
 * @param  int $precision
 * @return string
 * @note https://stackoverflow.com/a/2510540/10126479
 */
function format_bytes(int $size, int $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = ['', 'K', 'M', 'G', 'T'];

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}


/**
 * Get all array values as string
 * @param  array $cells
 * @return string
 */
function all_measurements(array $cells)
{
    $str = "\n\n";

    foreach ($cells as $key => $value) {
        $str .= format_number($value, 0) .' ';

        if (!(($key + 1) % 32))
            $str .= "\n";
    }

    return $str ."\n";
}


/**
 * Check functions exist
 * @param  array  $functions
 * @return array  only existing functions are returned
 */
function check_functions_exist(array $functions)
{
    // remove functions that don't exist
    foreach ($functions as $key => $function) {
        if (!function_exists($function)) {
            echo("Removed $function as it does not exist");
            unset($functions[$key]);
        }
    }

    return $functions;
}
