<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use Exception;

require_once __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    throw new Exception('Please run the script from cli');
}

(new Benchmark($argv))
    ->run();

class Benchmark
{
    private array $argv;
    private array $settings;
    private readonly string $line;

    public function __construct(array $argv)
    {
        $this->argv = $argv;

        $this->settings = [
            'iterations' => 500,
            'time_per_iteration' => 20,

            'test_filter' => '/^test/',
            'custom_tests' => false,

            'compare' => false,

            'show_histogram' => false,
            'histogram_buckets' => 16,
            'histogram_bar_width' => 50,

            'show_outliers' => false,
            'show_all_measurements' => false,

            'save' => false,
            'save_filename' => '',
            'save_filename_base' => 'benchmark_',
            'save_filename_ext' => date('Ymd-Hi') . '.txt',
        ];

        $this->line = str_pad('', Helper::$pad1 + Helper::$pad2 + 3, '-');
    }

    public function run() : void
    {
        $this->readCommandLine($this->argv);

        $settings = $this->settings;

        $this->showTitle();

        $class = $settings['custom_tests'] ? TestsUser::class : Tests::class;

        $tests = $this->getTests($class, $settings['test_filter']);

        $reports = $this->runTests($class, $tests, $settings['iterations'], (float) $settings['time_per_iteration']);

        if ($settings['save']) {
            $this->saveReports($reports);
        }

        if ($settings['custom_tests'] && count($tests) % 2 === 0) {
            $baseline = (new Reports())
                ->addReport($reports[0]);

            $update = (new Reports())
                ->addReport($reports[1]);

            Helper::showCompare($baseline, $update);
        } elseif ($settings['compare']) {
            $baseline = unserialize(file_get_contents($settings['compare']));
            Helper::showCompare($baseline, $reports);
        } else {
            Helper::showBenchmark($reports, $settings);
        }
    }

    private function runTests(string $class, array $testsAsc, int $iterations, float $timePerIteration) : Reports
    {
        $testsDesc = $testsAsc;
        krsort($testsDesc);

        $reports = new Reports();

        for ($i = 0; $i < $iterations; ++$i) {
            $this->updateProgress($i / $iterations);

            // switch testing order
            $tests = $i % 2 ? $testsDesc : $testsAsc;

            foreach ($tests as $test) {
                $measurement = $this->runTest($class, $test, $timePerIteration);

                $reports->add($test, $measurement);
            }
        }

        return $reports;
    }

    private function runTest(string $class, string $test, float $timePerIteration) : int
    {
        // burn the first test
        $class::$test();

        $iterations = 0;
        $timeLimit = hrtime(true) + $timePerIteration * 1000000;

        while (hrtime(true) < $timeLimit) {
            $class::$test();
            ++$iterations;
        }

        return $iterations;
    }

    private function getTests(string $class, string $filter) : array
    {
        $tests = get_class_methods($class);

        // filter tests
        foreach ($tests as $index => $test) {
            if (preg_match($filter, $test) !== 1) {
                // remove not matching test
                unset($tests[$index]);
            }
        }

        // reset array keys
        return array_values($tests);
    }

    private function updateProgress(float $percentage) : void
    {
        $progress = Helper::formatPercentage($percentage, false, 3);
        $text = "Running tests {$progress}...";
        $length = strlen($text);

        echo "{$text}\033[{$length}D";
    }

    private function saveReports(Reports $reports) : void
    {
        if (empty($this->settings['save_filename'])) {
            $this->settings['save_filename'] = $this->settings['save_filename_base'] . $this->settings['save_filename_ext'];
        }

        file_put_contents($this->settings['save_filename'], serialize($reports));

        echo "benchmark saved to {$this->settings['save_filename']}\n";
        echo "{$this->line}\n";
    }

    private function readCommandLine(array $argv) : void
    {
        $settings = $this->settings;

        // get command line arguments
        for ($i = 1; $i < count($argv); ++$i) {
            $argument = $argv[$i];

            if (strpos($argument, '--') !== 0) {
                throw new Exception("unknown argument - {$argument}");
            }

            switch ($argument) {
                case '--compare':
                    $i++;
                    $settings['compare'] = $argv[$i];
                    break;

                case '--custom':
                    $settings['custom_tests'] = true;
                    break;

                case '--filter':
                    $i++;
                    $settings['test_filter'] = $argv[$i];
                    break;

                case '--histogram':
                    $settings['show_histogram'] = true;
                    break;

                case '--histogram-buckets':
                    $i++;
                    $settings['histogram_buckets'] = $argv[$i];
                    break;

                case '--histogram-width':
                    $i++;
                    $settings['histogram_bar_width'] = $argv[$i];
                    break;

                case '--iterations':
                    $i++;
                    $settings['iterations'] = $argv[$i];
                    break;

                case '--save':
                    $settings['save'] = true;
                    if (!empty($argv[$i + 1]) && strpos($argv[$i + 1], '--') === false) {
                        ++$i;
                        $settings['save_filename'] = $settings['save_filename_base'] . $argv[$i] . '_' . $settings['save_filename_ext'];
                    }

                    break;

                case '--show-all':
                    $settings['show_all_measurements'] = true;
                    break;

                case '--show-outliers':
                    $settings['show_outliers'] = true;
                    break;

                case '--time-per-iteration':
                    $i++;
                    $settings['time_per_iteration'] = $argv[$i];
                    break;

                default:
                    throw new Exception("unknown argument - {$argument}");
            }
        }
    }

    private function showTitle() : void
    {
        $settings = $this->settings;

        $totalTime = $settings['iterations'] * $settings['time_per_iteration'] / 1000;

        echo "PHP benchmark\n\n" .
            "{$this->line}\n" .
            str_pad('platform', Helper::$pad1) . ' : ' . str_pad(PHP_OS . ' ' . ((PHP_INT_SIZE === 8) ? 'x64' : 'x32'), Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('php version', Helper::$pad1) . ' : ' . str_pad(PHP_VERSION, Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('xdebug', Helper::$pad1) . ' : ' . str_pad(extension_loaded('xdebug') ? 'on' : 'off', Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('opcache', Helper::$pad1) . ' : ' . str_pad((extension_loaded('Zend OPcache') && ini_get('opcache.enable_cli')) ? 'on' : 'off', Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('memory limit', Helper::$pad1) . ' : ' . str_pad(ini_get('memory_limit'), Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('max execution', Helper::$pad1) . ' : ' . str_pad(ini_get('max_execution_time'), Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('iterations', Helper::$pad1) . ' : ' . str_pad((string) $settings['iterations'], Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('time per iteration', Helper::$pad1) . ' : ' . str_pad($settings['time_per_iteration'] . 'ms', Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('total time per test', Helper::$pad1) . ' : ' . str_pad($totalTime . 's', Helper::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            "{$this->line}\n";
    }
}
