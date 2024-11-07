<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

use ArrayAccess;
use Exception;
use Iterator;

class Reports implements ArrayAccess, Iterator
{
    private array $reports;
    private int $position;

    public function __construct()
    {
        $this->reports = [];
        $this->position = 0;
    }

    public function add(string $name, int $value) : void
    {
        if (!array_key_exists($name, $this->reports)) {
            $this->reports[$name] = new Report($name);
        }

        $this->reports[$name]->add($value);
    }

    public function addReport(Report $report) : self
    {
        $this->reports[$report->name()] = $report;
        return $this;
    }

    public function names() : array
    {
        return array_keys($this->reports);
    }

    public function reports() : array
    {
        return $this->reports;
    }

    public function offsetExists(mixed $offset) : bool
    {
        if (is_integer($offset)) {
            return $offset < count($this->reports);
        }

        return array_key_exists($offset, $this->reports);
    }

    public function offsetGet(mixed $offset) : Report
    {
        if (is_integer($offset)) {
            return array_values($this->reports)[$offset];
        }

        return $this->reports[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value) : void
    {
        throw new Exception('not implemented');
    }

    public function offsetUnset(mixed $offset) : void
    {
        throw new Exception('not implemented');
    }

    public function rewind() : void
    {
        $this->position = 0;
    }

    public function current() : Report
    {
        return $this->offsetGet($this->position);
    }

    public function key() : int
    {
        return $this->position;
    }

    public function next() : void
    {
        ++$this->position;
    }

    public function valid() : bool
    {
        return $this->offsetExists($this->position);
    }
}
