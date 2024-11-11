<?php

declare(strict_types=1);

namespace Oct8pus\Tests;

use Exception;

class TestMysql
{
    public static function testMySql() : void
    {
        $host = 'localhost';
        $user = 'root';
        $pass = '123';
        $db = 'benchmark-test';

        if (!function_exists('mysqli_connect')) {
            throw new Exception('mysqli');
        }

        // connect to database
        $mysqli = mysqli_connect($host, $user, $pass);

        if (!$mysqli) {
            throw new Exception('Connect to database');
        }

        // create database
        $query = <<<TAG
        CREATE DATABASE IF NOT EXISTS `{$db}`;
        TAG;

        if (!mysqli_query($mysqli, $query)) {
            throw new Exception('Create database');
        }

        // select database
        if (!mysqli_select_db($mysqli, $db)) {
            throw new Exception('Select database');
        }

        $table = 'test';

        // create table
        $query = <<<TAG
        CREATE TABLE IF NOT EXISTS `{$table}` (
            `date` timestamp NOT NULL,
            `string` varchar(512) NOT NULL
        );
        TAG;

        if (!mysqli_query($mysqli, $query)) {
            throw new Exception('Create table');
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
            throw new Exception('Insert into table');
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
            throw new Exception('Select from table');
        }

        $array = mysqli_fetch_array($result);

        if (!$array) {
            throw new Exception('Select from table');
        }

        // drop database
        $query = <<<TAG
        DROP DATABASE `{$db}`;
        TAG;

        mysqli_query($mysqli, $query);

        // disconnect from database
        mysqli_close($mysqli);
    }
}
