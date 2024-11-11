<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use Exception;
use Oct8pus\Tests\TestDefault;

class Benchmark
{
    private array $argv;
    private string $line;

    private int $iterations = 500;
    private float $timePerIteration = 20;

    private string $testFilter = '/^test/';

    /** @var string|false */
    private $customTests = false;

    /** @var string|false */
    private $compare = false;

    private bool $save = false;
    private string $saveFile = '';
    private string $saveFilePrefix = 'benchmark_';
    private string $saveFileExt = '.txt';

    private bool $showHistogram = false;
    private int $histogramBuckets = 16;
    private int $histogramBarWidth = 50;

    private bool $showOutliers = false;
    private bool $showAllMeasurements = false;

    private int $pad1 = 19;
    private int $pad2 = 14;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->line = str_pad('', $this->pad1 + $this->pad2 + 3, '-');
    }

    public function run() : void
    {
        $this->readArguments($this->argv);

        $this->showTitle();

        $class = $this->customTests === false ? TestDefault::class : "Oct8pus\\Tests\\{$this->customTests}";

        $tests = $this->getTests($class, $this->testFilter);

        $reports = $this->runTests($class, $tests);

        if ($this->save) {
            $this->saveReports($reports);
        }

        if ($this->customTests && count($tests) && count($tests) % 2 === 0) {
            $baseline = (new Reports())
                ->addReport($reports[0]);

            $update = (new Reports())
                ->addReport($reports[1]);

            Helper::showCompare($baseline, $update);
        } elseif ($this->compare !== false) {
            $baseline = unserialize(file_get_contents($this->compare));
            Helper::showCompare($baseline, $reports);
        } else {
            $this->showBenchmark($reports);
        }
    }

    private function runTests(string $class, array $testsAsc) : Reports
    {
        $testsDesc = $testsAsc;
        krsort($testsDesc);

        $reports = new Reports();

        for ($i = 0; $i < $this->iterations; ++$i) {
            $this->updateProgress($i / $this->iterations);

            // switch testing order
            $tests = $i % 2 ? $testsDesc : $testsAsc;

            foreach ($tests as $test) {
                $measurement = $this->runTest($class, $test);

                $reports->add($test, $measurement);
            }
        }

        return $reports;
    }

    private function runTest(string $class, string $test) : int
    {
        // burn the first test (for op cache)
        $class::$test();

        $iterations = 0;
        $timeLimit = hrtime(true) + $this->timePerIteration * 1000000;

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
        if (empty($this->saveFile)) {
            $this->saveFile = $this->saveFilePrefix . $this->saveFileExt;
        }

        file_put_contents($this->saveFile, serialize($reports));

        echo "benchmark saved to {$this->saveFile}\n";
        echo "{$this->line}\n";
    }

    private function readArguments(array $arguments) : void
    {
        for ($i = 1; $i < count($arguments); ++$i) {
            $argument = $arguments[$i];

            switch ($argument) {
                case '--compare':
                    $i++;
                    $this->compare = $arguments[$i];
                    break;

                case '--custom':
                    $i++;
                    $this->customTests = $arguments[$i];
                    break;

                case '--filter':
                    $i++;
                    $this->testFilter = $arguments[$i];
                    break;

                case '--histogram':
                    $this->showHistogram = true;
                    break;

                case '--histogram-buckets':
                    $i++;
                    $this->histogramBuckets = (int) $arguments[$i];
                    break;

                case '--histogram-width':
                    $i++;
                    $this->histogramBarWidth = (int) $arguments[$i];
                    break;

                case '--iterations':
                    $i++;
                    $this->iterations = (int) $arguments[$i];
                    break;

                case '--save':
                    $this->save = true;
                    if (!empty($arguments[$i + 1]) && strpos($arguments[$i + 1], '--') === false) {
                        ++$i;
                        $this->saveFile = $this->saveFilePrefix . $arguments[$i] . $this->saveFileExt;
                    }

                    break;

                case '--show-all':
                    $this->showAllMeasurements = true;
                    break;

                case '--show-outliers':
                    $this->showOutliers = true;
                    break;

                case '--time-per-iteration':
                    $i++;
                    $this->timePerIteration = (float) $arguments[$i];
                    break;

                default:
                    throw new Exception("unknown argument - {$argument}");
            }
        }
    }

    private function showTitle() : void
    {
        $totalTime = $this->iterations * $this->timePerIteration / 1000;

        $xdebug = extension_loaded('xdebug') && ini_get('xdebug.mode') !== '';
        $opcache = extension_loaded('Zend OPcache') && ini_get('opcache.enable_cli');

        echo "PHP benchmark\n\n" .
            "{$this->line}\n" .
            str_pad('platform', $this->pad1) . ' : ' . str_pad(PHP_OS . ' ' . ((PHP_INT_SIZE === 8) ? 'x64' : 'x32'), $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('php version', $this->pad1) . ' : ' . str_pad(PHP_VERSION, $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('xdebug', $this->pad1) . ' : ' . str_pad($xdebug ? 'on' : 'off', $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('opcache', $this->pad1) . ' : ' . str_pad($opcache ? 'on' : 'off', $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('memory limit', $this->pad1) . ' : ' . str_pad(ini_get('memory_limit'), $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('max execution', $this->pad1) . ' : ' . str_pad(ini_get('max_execution_time'), $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('iterations', $this->pad1) . ' : ' . str_pad((string) $this->iterations, $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('time per iteration', $this->pad1) . ' : ' . str_pad($this->timePerIteration . 'ms', $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('total time per test', $this->pad1) . ' : ' . str_pad($totalTime . 's', $this->pad2, ' ', STR_PAD_LEFT) . "\n" .
            "{$this->line}\n";
    }

    private function showBenchmark(Reports $reports) : void
    {
        $line = str_pad('', $this->pad1 + $this->pad2 + 3, '-');

        foreach ($reports as $report) {
            $result = Helper::analyzeTest($report);

            if ($result === null) {
                echo str_pad($report->name(), $this->pad1) . ' : ' . str_pad('FAILED', $this->pad2, ' ', STR_PAD_LEFT) . "\n";
                echo "{$line}\n";
                continue;
            }

            echo str_pad($report->name(), $this->pad1) . ' : ' . str_pad('iterations', $this->pad2, ' ', STR_PAD_LEFT) . "\n";

            foreach ($result as $key => $value) {
                if ($key === 'normality') {
                    echo str_pad($key, $this->pad1) . ' : ' . Helper::formatPercentage($value, false, $this->pad2) . "\n";
                } else {
                    echo str_pad($key, $this->pad1) . ' : ' . Helper::formatNumber($value, $this->pad2) . "\n";
                }
            }

            if ($this->showHistogram) {
                echo "\n";
                $histogram = Stats::histogram($report->data(), $this->histogramBuckets);
                Stats::drawHistogram($histogram, $this->histogramBarWidth);
            }

            if ($this->showOutliers) {
                echo "\n";
                echo str_pad('outliers', $this->pad1) . ' : ' . Helper::outliers($report->data()) . "\n";
            }

            if ($this->showAllMeasurements) {
                echo "\n";
                echo str_pad('values', $this->pad1) . ' : ' . Helper::allMeasurements($report->data()) . "\n";
            }

            echo "{$line}\n";
        }
    }
}
