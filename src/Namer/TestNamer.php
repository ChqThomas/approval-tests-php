<?php

namespace ApprovalTests\Namer;

use ApprovalTests\Core\ApprovalNamer;
use ApprovalTests\ApprovalException;
use PHPUnit\Framework\TestCase;

class TestNamer implements ApprovalNamer
{
    private ?string $testClass = null;
    private ?string $testMethod = null;
    private ?string $dataSetName = null;

    public function __construct()
    {
        $trace = debug_backtrace();

        foreach ($trace as $item) {
            if (isset($item['object']) && $item['object'] instanceof TestCase) {
                $testCase = $item['object'];
                $this->testClass = get_class($testCase);
                $this->testMethod = $item['function'];

                // Get the dataset name via the toString() method
                $testName = $testCase->toString();

                // The format is generally: "testMethod with data set #0 (arg1, arg2)"
                // or "testMethod with data set "name" (arg1, arg2)"
                if (preg_match('/with data set [#"]([^")]+)/', $testName, $matches)) {
                    $this->dataSetName = $matches[1];
                }
                break;
            }
        }

        if (!$this->testClass || !$this->testMethod) {
            throw new ApprovalException("Unable to determine the test context");
        }
    }

    public function getApprovedFile(): string
    {
        return $this->getFileBase() . '.approved';
    }

    public function getReceivedFile(): string
    {
        return $this->getFileBase() . '.received';
    }

    private function getFileBase(): string
    {
        $reflector = new \ReflectionClass($this->testClass);
        $testDirectory = dirname($reflector->getFileName());
        $approvalsDirectory = $testDirectory . '/approvals';

        if (!is_dir($approvalsDirectory)) {
            mkdir($approvalsDirectory, 0777, true);
        }

        $parts = [
            str_replace('\\', '.', $this->testClass),
            $this->testMethod
        ];

        if ($this->dataSetName !== null) {
            $parts[] = $this->sanitizeDataSetName($this->dataSetName);
        }

        return $approvalsDirectory . '/' . implode('.', $parts);
    }

    private function sanitizeDataSetName(string $name): string
    {
        // Replace invalid characters in file names
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    }
}
