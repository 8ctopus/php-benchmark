<?php

declare(strict_types=1);

namespace Oct8pus\Tests;

use Latte\Engine;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TestTemplates
{
    private static function viewsDir() : string
    {
        return __DIR__ . '/../views';
    }

    private static function params() : array
    {
        return [
            'title' => 'test',
            'name' => 'world',
            'favicon' => 'favicon.ico',
            'list' => [
                'first',
                'second',
                'third',
            ],
        ];
    }

    public static function testTwig() : void
    {
        $namespaces = [
            '__main__' => '',
        ];

        $loader = new FilesystemLoader($namespaces, self::viewsDir());

        $environment = new Environment($loader, [
            //'auto_reload' => true,
            //'cache' => Helper::storageDir() . '/twig',
            'debug' => false,
            //'strict_variables' => true,
        ]);

        $output = $environment->render('Index.twig', self::params());
    }

    public static function testLatte() : void
    {
        $latte = new Engine();

        //$latte->setTempDirectory('/path/to/tempdir');

        $output = $latte->renderToString(self::viewsDir() . '/Index.latte', self::params());
    }
}
