<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

class TestsMySql
{
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
