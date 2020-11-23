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

// iterations
$iterations = 25;

// paddings
$pad1 = 15;
$pad2 = 27;
$pad3 = 9;

$line = str_pad('', $pad2, '-');

// check if running from cli
if (php_sapi_name() != 'cli')
    echo('<pre>');

echo('PHP benchmark' ."\n\n".
    str_pad('php version', $pad1) .' : '. str_pad(PHP_VERSION, $pad3, ' ', STR_PAD_LEFT) ."\n".
    str_pad('platform', $pad1) .' : '. str_pad(PHP_OS .' '. ((PHP_INT_SIZE == 8) ? 'x64' : 'x32'), $pad3, ' ', STR_PAD_LEFT) ."\n".
    str_pad('memory limit', $pad1) .' : '. str_pad(ini_get('memory_limit'), $pad3, ' ', STR_PAD_LEFT) ."\n".
    str_pad('max execution', $pad1) .' : '. str_pad(ini_get('max_execution_time'), $pad3, ' ', STR_PAD_LEFT) ."\n".
    str_pad('iterations', $pad1) .' : '. str_pad($iterations, $pad3, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

$total     = 0;

// list functions
$functions = get_defined_functions();

// run tests
foreach ($functions['user'] as $user) {
    // check if function starts with test
    if (preg_match('/^test_/', $user)) {
        $timings = [];

        // run each test x times
        for ($i = 0; $i < $iterations; $i++) {
            $timings[$i] = $user();
        }

        if ($timings[0] == -1) {
            echo(str_pad($user, $pad1) .' : '. str_pad('FAILED', $pad3, ' ', STR_PAD_LEFT) ."\n");
            continue;
        }

        $total += median($timings);
        echo(str_pad($user, $pad1) .' : '. format_time(median($timings), $pad3, true) .' '. variability($timings) .' '. interval($timings) .' - '. show_all($timings) ."\n");
    }
}

echo(str_pad('-', $pad2, '-') ."\n".
    str_pad('Total time ', $pad1) .' : '.
    format_time($total, $pad3, true)
);

exit();


/**
 * Format time
 * @param  int $time
 * @param  int $padding
 * @param  bool $add_unit
 * @return string
 */
function format_time($time, $padding, $add_unit = false)
{
    $str = number_format($time, 1) . ($add_unit ? ' s' : '');

    return str_pad($str, $padding, ' ', STR_PAD_LEFT);
}


/**
 * Format bytes
 * @param  int $size
 * @param  int $precision
 * @return string
 * @note https://stackoverflow.com/a/2510540/10126479
 */
function format_bytes($size, $precision = 2)
{
    $base = log($size, 1024);
    $suffixes = ['', 'K', 'M', 'G', 'T'];

    return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
}


/**
 * Test math functions
 * @param  int $iterations
 * @return int elapsed time
 */
function test_math($iterations = 220000)
{
    $time_start  = microtime(true);
    $functions   = ['abs', 'acos', 'asin', 'atan', 'decbin', 'exp', 'floor', 'exp', 'is_finite',
        'is_nan', 'log', 'log10', 'log1p', 'sin', 'tan', 'pi', 'pow', 'sqrt'];

    // remove functions that don't exist
    foreach ($functions as $key => $function) {
        if (!function_exists($function))
            unset($functions[$key]);
    }

    // run tests
    foreach ($functions as $function) {
        // get function arguments count
        $reflection = new ReflectionFunction($function);
        $arguments  = $reflection->getNumberOfParameters();

        for ($i = 0; $i < $iterations; $i++) {
            call_user_func_array($function, $arguments == 1 ? [$i + 1] : [$i + 1, $i + 1]);
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test string functions
 * @param  int $iterations
 * @return int elapsed time
 */
function test_strings($iterations = 130000)
{
    $time_start = microtime(true);
    $functions  = ['addslashes', 'chunk_split', 'ltrim', 'metaphone', 'ord', 'str_shuffle',
        'strip_tags', 'strlen', 'strtoupper', 'strtolower', 'strrev', 'soundex', 'trim'];

    // remove functions that don't exist
    foreach ($functions as $key => $function) {
        if (!function_exists($function))
            unset($unctions[$key]);
    }

    $string = 'the quick brown fox jumps over the lazy dog';

    // run tests
    foreach ($functions as $function) {
        for ($i = 0; $i < $iterations; $i++) {
            call_user_func_array($function, [$string]);
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test loops
 * @param  int $iterations
 * @return int elapsed time
 */
function test_loops($iterations = 16500000)
{
    $time_start = microtime(true);

    $j = 0;

    for ($i = 0; $i < $iterations; ++$i) {
        ++$j;
    }

    $i = 0;

    while ($i < $iterations)
        ++$i;

    return microtime(true) - $time_start;
}


/**
 * Test if else
 * @param  int $iterations
 * @return int elapsed time
 */
function test_if_else($iterations = 11500000)
{
    $time_start = microtime(true);

    $j = 0;

    for ($i = 0; $i < $iterations; $i++) {
        if ($i == -1) {
            $j = 1;
        }
        elseif ($i == -2) {
            $j = 2;
        }
        else if ($i == -3) {
            $j = 3;
        }
        else {
            $j = 4;
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test arrays
 * @param  int $iterations
 * @return int elapsed time
 */
function test_arrays($iterations = 48000)
{
    $time_start = microtime(true);

    $a = [];

    for ($i = 0; $i < $iterations; $i++) {
        array_push($a, [
            rand() => random_bytes(10)
        ]);
    }

    for ($i = 0; $i < $iterations; $i++) {
        array_search(random_bytes(10), $a, true);
    }

    return microtime(true) - $time_start;
}


/**
 * Test cryptographic hashes
 * @param  int $iterations
 * @return int elapsed time
 */
function test_hashes($iterations = 105000)
{
    $time_start = microtime(true);

    $hashes = ['adler32', 'crc32', 'crc32b', 'md5', 'sha1', 'sha256', 'sha384', 'sha512'];
    $string = random_bytes(1024);

    foreach ($hashes as $hash) {
        for ($i = 0; $i < $iterations; $i++) {
            hash($hash, $string, false);
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test file operations
 * @param  int $iterations
 * @return int elapsed time or -1 on failure
 */
function test_files($iterations = 500)
{
    $time_start = microtime(true);

    // max number of bytes to write
    $bytes_to_write_max = 0.5 * 1024 * 1024;
    $total_bytes        = 0;

    // get temporary directory
    $tmp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

    for ($i = 0; $i < $iterations; $i++) {
        // scan temp dir
        $list = scandir($tmp_dir);

        if (!$list)
            return -1;

        // get temporary file name in temporary dir
        $tmp_filename = tempnam($tmp_dir, '');

        if (!$tmp_filename)
            return -1;

        // open temp file
        $handle = fopen($tmp_filename, 'r+');

        if (!$handle)
            return -1;

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
    }

    //echo('total bytes : '. format_bytes($total_bytes) ."\n");
    return microtime(true) - $time_start;
}


/**
 * Test mysql operations
 * @param  int    $iterations
 * @return int elapsed time or -1 on error
 */
function test_mysql($iterations = 700)
{
    $time_start = microtime(true);

    $host       = 'localhost';
    $user       = 'root';
    $pass       = '123';
    $db         = 'benchmark-test';
    $mysqli     = null;
    $db_created = false;
    $exception  = false;

    try {
        // connect to database
        $mysqli = mysqli_connect($host, $user, $pass);

        if (!$mysqli)
            throw new Exception('Connect to database - FAILED');

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

        // select database
        if (!mysqli_select_db($mysqli, $db))
            throw new Exception('Select database - FAILED');

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

        for ($i = 0; $i < $iterations; $i++) {
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
        }
    }
    catch (Exception $e) {
        $exception = true;
        //echo($e->getMessage() ."\n");
    }
    finally {
        // check for connection failure
        if (!$mysqli)
            return -1;

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

    return $exception ? -1 : microtime(true) - $time_start;
}


/**
 * Calculate array total
 * @param  array $cells
 * @return float
 */
function total(array $cells)
{
    $total = 0;

    foreach ($cells as $cell) {
        $total += $cell;
    }

    return $total;
}


/**
 * Calculate array average
 * @param  array $cells
 * @return float
 */
function average(array $cells)
{
    return $total($cells) / sizeof($cells);
}


/**
 * Calculate array median
 * @param  array $cells
 * @return float
 */
function median(array $cells)
{
    // sort array values ascending
    sort($cells, SORT_NUMERIC);

    $count = sizeof($cells);

    $index = floor($count / 2);

    if ($count % 2)
        return $cells[$index];
    else
        return ($cells[$index -1] + $cells[$index]) / 2;
}


/**
 * Get array min - max interval
 * @param  array $cells
 * @return string
 */
function interval($cells)
{
    // interval
    return '['. format_time(min($cells), 0) .' - '. format_time(max($cells), 0) .']';
}


/**
 * Get array fluctuation from mediam
 * @param  array $cells
 * @return string
 */
function variability($cells)
{
    $variability = (max($cells) - min($cells)) / median($cells) * 100 / 2;

    return '±'. number_format($variability, 1) .'%';
}


/**
 * Get all array values /* sorted from min to max* /
 * @param  array $cells
 * @return string
 */
function show_all($cells)
{
    // sort array values ascending
    //sort($cells, SORT_NUMERIC);

    $str = '';

    foreach ($cells as $cell) {
        $str .= format_time($cell, 0) .' ';
    }

    return $str;
}
