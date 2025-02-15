<?php

namespace ApprovalTests;

class ApprovalMaintenance
{
    /**
     * Supprime tous les fichiers .received qui n'ont pas de tests en échec associés
     */
    public static function cleanUpReceivedFiles(string $directory): void
    {
        $receivedFiles = glob($directory . '/**/*.received.*', GLOB_NOSORT);
        foreach ($receivedFiles as $receivedFile) {
            $approvedFile = str_replace('.received.', '.approved.', $receivedFile);

            // Si le fichier approved existe et est identique, on peut supprimer le received
            if (file_exists($approvedFile) &&
                file_get_contents($receivedFile) === file_get_contents($approvedFile)) {
                unlink($receivedFile);
            }
        }
    }

    /**
     * Vérifie s'il y a des fichiers .approved orphelins (sans test associé)
     */
    public static function findOrphanedApprovedFiles(string $testsDirectory): array
    {
        $orphanedFiles = [];
        $approvedFiles = glob($testsDirectory . '/**/*.approved.*', GLOB_NOSORT);

        foreach ($approvedFiles as $approvedFile) {
            // Extrait le nom du test à partir du nom du fichier
            if (preg_match('/Tests\.(\w+)Test\.(\w+)\.approved/', $approvedFile, $matches)) {
                $testClass = $matches[1] . 'Test';
                $testMethod = $matches[2];

                // Vérifie si la classe et la méthode de test existent
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
