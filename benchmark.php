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

// check if running from cli
if (php_sapi_name() != 'cli')
    echo('<pre>');

// paddings
$pad1 = 15;
$pad2 = 27;
$pad3 = 9;

$line = str_pad('', $pad2, '-');

echo('PHP benchmark' ."\n\n".
    str_pad('php version', $pad1) .' : '. str_pad(PHP_VERSION, $pad3, ' ', STR_PAD_LEFT) ."\n".
    str_pad('platform', $pad1) .' : '. str_pad(PHP_OS .' '. ((PHP_INT_SIZE == 8) ? 'x64' : 'x32'), $pad3, ' ', STR_PAD_LEFT) ."\n".
    str_pad('memory limit', $pad1) .' : '. str_pad(ini_get('memory_limit'), $pad3, ' ', STR_PAD_LEFT) ."\n".
    "$line\n"
);

$total     = 0;
$functions = get_defined_functions();

// run tests
foreach ($functions['user'] as $user) {
    if (preg_match('/^test_/', $user)) {
        $total += $result = $user();
        echo(str_pad($user, $pad1) .' : '. format_time($result));
    }
}

echo(str_pad('-', $pad2, '-') ."\n".
	str_pad('Total time ', $pad1) .' : '.
	format_time($total)
);

exit();


/**
 * Format time
 * @param  int $time
 * @return string
 */
function format_time($time)
{
	return str_pad(number_format($time, 1) .' s', $pad3, ' ', STR_PAD_LEFT) ."\n";
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
 * @return int
 */
function test_math($iterations = 220000)
{
    $time_start  = microtime(true);
    $functions   = ['abs', 'acos', 'asin', 'atan', 'decbin', 'exp', 'floor', 'exp', 'log10', 'log1p', 'sin', 'tan', 'pi', 'is_finite', 'is_nan', 'sqrt'];
    $functions_2 = ['log', 'pow'];

    foreach ($functions as $key => $function) {
        if (!function_exists($function))
            unset($functions[$key]);
    }

    for ($i = 0; $i < $iterations; $i++) {
        foreach ($functions as $function) {
            call_user_func_array($function, [$i]);
        }
    }

    foreach ($functions_2 as $key => $function) {
        if (!function_exists($function))
            unset($functions_2[$key]);
    }

    for ($i = 1; $i < $iterations; $i++) {
        foreach ($functions_2 as $function) {
            call_user_func_array($function, [$i, $i]);
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test string functions
 * @param  int $iterations
 * @return int
 */
function test_strings($iterations = 130000)
{
    $time_start = microtime(true);
    $functions  = ['addslashes', 'chunk_split', 'ltrim', 'metaphone', 'ord', 'str_shuffle',
        'strip_tags', 'strlen', 'strtoupper', 'strtolower', 'strrev', 'soundex', 'trim'];

    foreach ($functions as $key => $function) {
        if (!function_exists($function))
            unset($unctions[$key]);
    }

    $string = 'the quick brown fox jumps over the lazy dog';

    for ($i = 0; $i < $iterations; $i++) {
        foreach ($functions as $function) {
            call_user_func_array($function, [$string]);
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test loops
 * @param  int $iterations
 * @return int
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
 * @return int
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
 * @return int
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
 * @return int
 */
function test_hashes($iterations = 105000)
{
    $time_start = microtime(true);

    $hashes = ['adler32', 'crc32', 'crc32b', 'md5', 'sha1', 'sha256', 'sha384', 'sha512'];
    $string = random_bytes(1024);

    for ($i = 0; $i < $iterations; $i++) {
        foreach ($hashes as $hash) {
            hash($hash, $string, false);
        }
    }

    return microtime(true) - $time_start;
}


/**
 * Test file operations
 * @param  int $iterations
 * @return int
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

        // get temporary file name in temporary dir
        $tmp_filename = tempnam($tmp_dir, '');

        if ($tmp_filename) {
            // open temp file
            $handle = fopen($tmp_filename, 'r+');

            if ($handle) {
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
        }
    }

    //echo('total bytes : '. format_bytes($total_bytes) ."\n");
    return microtime(true) - $time_start;
}
