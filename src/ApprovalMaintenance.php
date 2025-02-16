<?php

namespace ApprovalTests;

class ApprovalMaintenance
{
    /**
     * Delete all .received files that do not have associated failed tests
     */
    public static function cleanUpReceivedFiles(string $directory): void
    {
        $receivedFiles = glob($directory . '/**/*.received.*', GLOB_NOSORT);
        foreach ($receivedFiles as $receivedFile) {
            $approvedFile = str_replace('.received.', '.approved.', $receivedFile);

            // If the approved file exists and is identical, we can delete the received file
            if (file_exists($approvedFile) &&
                file_get_contents($receivedFile) === file_get_contents($approvedFile)) {
                unlink($receivedFile);
            }
        }
    }

    /**
     * Check if there are orphaned .approved files (without associated test)
     */
    public static function findOrphanedApprovedFiles(string $testsDirectory): array
    {
        $orphanedFiles = [];
        $approvedFiles = glob($testsDirectory . '/**/*.approved.*', GLOB_NOSORT);

        foreach ($approvedFiles as $approvedFile) {
            // Extract the test name from the file name
            if (preg_match('/Tests\.(\w+)Test\.(\w+)\.approved/', $approvedFile, $matches)) {
                $testClass = $matches[1] . 'Test';
                $testMethod = $matches[2];

                // Check if the test class and method exist
                $testFile = $testsDirectory . '/' . $testClass . '.php';
                if (!file_exists($testFile) ||
                    !self::methodExistsInFile($testFile, $testMethod)) {
                    $orphanedFiles[] = $approvedFile;
                }
            }
        }

        return $orphanedFiles;
    }

    private static function methodExistsInFile(string $file, string $methodName): bool
    {
        $content = file_get_contents($file);
        return (bool) preg_match('/function\s+' . preg_quote($methodName) . '\s*\(/', $content);
    }
}
