<?php

namespace ChqThomas\ApprovalTests\PHPUnit;

use ChqThomas\ApprovalTests\CustomApprovalException;
use ChqThomas\ApprovalTests\Reporter\DiffReporter;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener as PHPUnitTestListener;
use PHPUnit\Framework\Warning;

class TestListener extends AbstractApprovalTestHandler implements PHPUnitTestListener
{
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->handleApprovalException($t);
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->handleApprovalException($e);
    }

    protected function handleApprovalException(\Throwable $throwable): void
    {
        $exception = $throwable;

        if ($throwable instanceof ExceptionWrapper) {
            $exception = $throwable->getOriginalException() ?? $throwable;
        }

        if ($exception instanceof CustomApprovalException) {
            $this->openDiffReporter($exception);
        }
    }

    // Required by interface
    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
    }
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
    }
    public function startTest(Test $test): void
    {
    }
    public function endTest(Test $test, float $time): void
    {
    }
}
