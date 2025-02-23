<?php

namespace ChqThomas\ApprovalTests\Maintenance;

class ApprovalMaintenance
{
    public static function verifyNoAbandonedFiles(array $ignoredFiles = []): void
    {
        $approvedFiles = self::findApprovedFiles();
        $abandonedFiles = self::findAbandonedFiles($approvedFiles, $ignoredFiles);

        if (!empty($abandonedFiles)) {
            throw new \RuntimeException(
                "The following approved files have no associated tests:\n" .
                implode("\n", $abandonedFiles)
            );
        }
    }

    private static function findApprovedFiles(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator('tests')
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.approved.txt')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private static function findAbandonedFiles(array $approvedFiles, array $ignoredFiles): array
    {
        $abandonedFiles = [];
        foreach ($approvedFiles as $approvedFile) {
            if (in_array($approvedFile, $ignoredFiles)) {
                continue;
            }

            $testFile = self::getAssociatedTestFile($approvedFile);
            if (!file_exists($testFile)) {
                $abandonedFiles[] = $approvedFile;
            }
        }
        return $abandonedFiles;
    }

    private static function getAssociatedTestFile(string $approvedFile): string
    {
        // Example: tests/approvals/Tests.ExampleTest.testMethod.approved.txt
        // -> tests/ExampleTest.php
        $parts = explode('.', basename($approvedFile));
        $className = $parts[1];
        return dirname(dirname($approvedFile)) . '/' . $className . '.php';
    }
}
