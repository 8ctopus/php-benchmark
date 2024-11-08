<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use Exception;

class Benchmark
{
    private array $argv;
    private readonly string $line;

    private int $iterations = 500;
    private float $timePerIteration = 20;

    private string $testFilter = '/^test/';
    private bool $customTests = false;

    private bool|string $compare = false;

    private bool $save = false;
    private string $saveFile = '';
    private string $saveFilePrefix = 'benchmark_';
    private string $saveFileExt = '.txt';

    private bool $showHistogram = false;
    private int $histogramBuckets = 16;
    private int $histogramBarWidth = 50;

    private bool $showOutliers = false;
    private bool $showAllMeasurements = false;

    private static int $pad1 = 19;
    private static int $pad2 = 14;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->line = str_pad('', self::$pad1 + self::$pad2 + 3, '-');
    }

    public function run() : void
    {
        $this->readCommandLine($this->argv);

        $this->showTitle();

        $class = $this->customTests ? TestsUser::class : Tests::class;

        $tests = $this->getTests($class, $this->testFilter);

        $reports = $this->runTests($class, $tests);

        if ($this->save) {
            $this->saveReports($reports);
        }

        if ($this->customTests && count($tests) % 2 === 0) {
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

    private function readCommandLine(array $argv) : void
    {
        for ($i = 1; $i < count($argv); ++$i) {
            $argument = $argv[$i];

            if (strpos($argument, '--') !== 0) {
                throw new Exception("unknown argument - {$argument}");
            }

            switch ($argument) {
                case '--compare':
                    $i++;
                    $this->compare = $argv[$i];
                    break;

                case '--custom':
                    $this->customTests = true;
                    break;

                case '--filter':
                    $i++;
                    $this->testFilter = $argv[$i];
                    break;

                case '--histogram':
                    $this->showHistogram = true;
                    break;

                case '--histogram-buckets':
                    $i++;
                    $this->histogramBuckets = $argv[$i];
                    break;

                case '--histogram-width':
                    $i++;
                    $this->histogramBarWidth = $argv[$i];
                    break;

                case '--iterations':
                    $i++;
                    $this->iterations = $argv[$i];
                    break;

                case '--save':
                    $this->save = true;
                    if (!empty($argv[$i + 1]) && strpos($argv[$i + 1], '--') === false) {
                        ++$i;
                        $this->saveFile = $this->saveFilePrefix . $argv[$i] . '_' . $this->saveFileExt;
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
                    $this->timePerIteration = $argv[$i];
                    break;

                default:
                    throw new Exception("unknown argument - {$argument}");
            }
        }
    }

    private function showTitle() : void
    {
        $totalTime = $this->iterations * $this->timePerIteration / 1000;

        echo "PHP benchmark\n\n" .
            "{$this->line}\n" .
            str_pad('platform', self::$pad1) . ' : ' . str_pad(PHP_OS . ' ' . ((PHP_INT_SIZE === 8) ? 'x64' : 'x32'), self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('php version', self::$pad1) . ' : ' . str_pad(PHP_VERSION, self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('xdebug', self::$pad1) . ' : ' . str_pad(extension_loaded('xdebug') ? 'on' : 'off', self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('opcache', self::$pad1) . ' : ' . str_pad((extension_loaded('Zend OPcache') && ini_get('opcache.enable_cli')) ? 'on' : 'off', self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('memory limit', self::$pad1) . ' : ' . str_pad(ini_get('memory_limit'), self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('max execution', self::$pad1) . ' : ' . str_pad(ini_get('max_execution_time'), self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('iterations', self::$pad1) . ' : ' . str_pad((string) $this->iterations, self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('time per iteration', self::$pad1) . ' : ' . str_pad($this->timePerIteration . 'ms', self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            str_pad('total time per test', self::$pad1) . ' : ' . str_pad($totalTime . 's', self::$pad2, ' ', STR_PAD_LEFT) . "\n" .
            "{$this->line}\n";
    }

    public function showBenchmark(Reports $data) : void
    {
        $line = str_pad('', self::$pad1 + self::$pad2 + 3, '-');

        // analyze test results
        foreach ($data as $report) {
            $result = Helper::analyzeTest($report);

            if ($result === null) {
                echo str_pad($report->name(), self::$pad1) . ' : ' . str_pad('FAILED', self::$pad2, ' ', STR_PAD_LEFT) . "\n";
                echo "{$line}\n";
                continue;
            }

            echo str_pad($report->name(), self::$pad1) . ' : ' . str_pad('iterations', self::$pad2, ' ', STR_PAD_LEFT) . "\n";

            foreach ($result as $key => $value) {
                if ($key === 'normality') {
                    echo str_pad($key, self::$pad1) . ' : ' . Helper::formatPercentage($value, false, self::$pad2) . "\n";
                } else {
                    echo str_pad($key, self::$pad1) . ' : ' . Helper::formatNumber($value, self::$pad2) . "\n";
                }
            }

            if ($this->showHistogram) {
                echo "\n";
                $histogram = Stats::histogram($report->data(), $this->histogramBuckets);
                Stats::drawHistogram($histogram, $this->histogramBarWidth);
            }

            if ($this->showOutliers) {
                echo "\n";
                echo str_pad('outliers', self::$pad1) . ' : ' . Helper::outliers($report->data()) . "\n";
            }

            if ($this->showAllMeasurements) {
                echo "\n";
                echo str_pad('values', self::$pad1) . ' : ' . Helper::allMeasurements($report->data()) . "\n";
            }

            echo "{$line}\n";
        }
    }
}
