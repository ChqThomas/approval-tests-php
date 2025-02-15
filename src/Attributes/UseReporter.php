<?php

namespace ApprovalTests\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class UseReporter
{
    private array $reporters;

    public function __construct(string ...$reporters)
    {
        $this->reporters = $reporters;
    }

    public function getReporters(): array
    {
        return $this->reporters;
    }
} 