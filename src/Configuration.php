<?php

namespace ApprovalTests;

use ApprovalTests\Core\ApprovalReporter;
use ApprovalTests\Reporter\ReporterFactory;

class Configuration
{
    private static ?Configuration $instance = null;
    private ?ApprovalReporter $frontLoadedReporter = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getFrontLoadedReporter(): ?ApprovalReporter
    {
        return $this->frontLoadedReporter;
    }

    public function setFrontLoadedReporter(ApprovalReporter $reporter): void
    {
        $this->frontLoadedReporter = $reporter;
    }

    public function getReporter(): ApprovalReporter
    {
        return $this->frontLoadedReporter ?? ReporterFactory::getDefaultReporter();
    }
}
