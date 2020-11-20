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
$pad2 = 25;

$line = str_pad('', $pad2, '-');

echo('PHP benchmark' ."\n\n".
	str_pad('php version', $pad1) .' :   '. PHP_VERSION ."\n".
    str_pad('platform', $pad1) .' :   '. PHP_OS ."\n".
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
	return str_pad(number_format($time, 1), 5, ' ', STR_PAD_LEFT) ." s\n";
}


/**
 * Test math functions
 * @param  int $iterations
 * @return int
 */
function test_math($iterations = 140000)
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
function test_loops($iterations = 19000000)
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
function test_if_else($iterations = 9000000)
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
function test_arrays($iterations = 50000)
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
function test_hashes($iterations = 100000)
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
function test_files($iterations = 1000)
{
    $time_start = microtime(true);

    $rand_max = 1 * 1024 * 1024;

    $tmp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

    for ($i = 0; $i < $iterations; $i++) {
        $tmp_filename = tempnam($tmp_dir, '');

        if ($tmp_filename) {
            $handle = fopen($tmp_filename, 'r+');

            if ($handle) {
                $random = rand(1, $rand_max);

                // false
                $result = fwrite($handle, random_bytes($random));

                // -1
                $result = fseek($handle, rand(1, $random));

                $position = ftell($handle);

                // string
                $result = fread($handle, rand(1, $random));

                // bool
                fclose($handle);

                unlink($tmp_filename);
            }
        }
    }

    return microtime(true) - $time_start;
}
