<?php

declare(strict_types=1);

namespace Oct8pus\Benchmark;

class Report
{
    private readonly string $name;
    private array $data;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->data = [];
    }

    public function add(int $value) : void
    {
        $this->data[] = $value;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function data() : array
    {
        return $this->data;
    }
}
