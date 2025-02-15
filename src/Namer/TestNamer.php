<?php

namespace ApprovalTests\Namer;

use ApprovalTests\Core\ApprovalNamer;
use ApprovalTests\ApprovalException;
use PHPUnit\Framework\TestCase;

class TestNamer implements ApprovalNamer
{
    private string $testClass;
    private string $testMethod;
    private ?string $dataSetName = null;

    public function __construct()
    {
        $trace = debug_backtrace();
        
        foreach ($trace as $item) {
            if (isset($item['object']) && $item['object'] instanceof TestCase) {
                $testCase = $item['object'];
                $this->testClass = get_class($testCase);
                $this->testMethod = $item['function'];

                // Récupérer le nom du dataset via la méthode toString()
                $testName = $testCase->toString();
                
                // Le format est généralement: "testMethod with data set #0 (arg1, arg2)"
                // ou "testMethod with data set "name" (arg1, arg2)"
                if (preg_match('/with data set [#"]([^")]+)/', $testName, $matches)) {
                    $this->dataSetName = $matches[1];
                }
                break;
            }
        }

        if (!$this->testClass || !$this->testMethod) {
            throw new ApprovalException("Impossible de déterminer le contexte du test");
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
        // Remplace les caractères non autorisés dans les noms de fichiers
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    }
} 