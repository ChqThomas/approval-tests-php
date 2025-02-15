<?php

namespace ApprovalTests\PHPUnit;

use ApprovalTests\CustomApprovalException;
use ApprovalTests\Reporter\DiffReporter;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener as PHPUnitTestListener;
use PHPUnit\Framework\Warning;

class TestListener implements PHPUnitTestListener
{
    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->handleApprovalException($t);
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->handleApprovalException($e);
    }

    private function handleApprovalException(\Throwable $throwable): void 
    {
        $exception = $throwable;
        
        // Extraire l'exception d'origine si elle est enveloppée
        if ($throwable instanceof ExceptionWrapper) {
            $exception = $throwable->getOriginalException() ?? $throwable;
        }
        
        // Vérifier si c'est une erreur d'approbation en vérifiant le message
        if ($exception instanceof CustomApprovalException) {
            $reporter = new DiffReporter();
            $reporter->report($exception->getApprovedFile(), $exception->getReceivedFile());
        }
    }

    // Méthodes requises par l'interface mais non utilisées
    public function addWarning(Test $test, Warning $e, float $time): void {}
    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void {}
    public function addRiskyTest(Test $test, \Throwable $t, float $time): void {}
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void {}
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void {}
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void {}
    public function startTest(Test $test): void {}
    public function endTest(Test $test, float $time): void {}
} 