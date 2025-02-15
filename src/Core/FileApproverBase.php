<?php

namespace ApprovalTests\Core;

use ApprovalTests\ApprovalException;
use ApprovalTests\Configuration;
use ApprovalTests\Writer\ApprovalWriter;
use ApprovalTests\Writer\TextWriter;
use ApprovalTests\Writer\BinaryWriter;
use PHPUnit\Framework\Assert;

abstract class FileApproverBase
{
    protected function getReporter()
    {
        return Configuration::getInstance()->getReporter();
    }

    abstract protected function getNamer(): ApprovalNamer;
    
    protected function normalizeLineEndings(string $text): string
    {
        // Convertit tous les \r\n et \r en \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Supprime les espaces en fin de ligne
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        $text = implode("\n", $lines);
        
        // Supprime les lignes vides à la fin
        return rtrim($text);
    }

    protected function normalizeContent(string $text): string
    {
        // Normalise les fins de ligne
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Supprime les espaces en fin de ligne
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        $text = implode("\n", $lines);
        
        // Supprime les lignes vides à la fin
        $text = rtrim($text);
        
        // Normalise les caractères spéciaux
        $text = str_replace(
            ["\n", "\r", "\t"],
            ['\\n', '\\r', '\\t'],
            $text
        );
        
        return $text;
    }

    public function verify($received, ?Scrubber $scrubber = null, ?ApprovalWriter $writer = null): void
    {
        $receivedText = $this->prepareReceivedText($received, $scrubber);
        $writer = $this->prepareWriter( $receivedText, $writer);
        
        $files = $this->prepareFiles($writer);
        $this->writeReceivedFile($files['received'], $writer);

        if (!file_exists($files['approved'])) {
            $this->handleNewTest($files);
        }

        $this->compareFiles($files, $receivedText);
    }

    protected function prepareReceivedText($received, ?Scrubber $scrubber): string
    {
        $text = $scrubber ? $scrubber->scrub($received) : $received;
        return $this->normalizeLineEndings($text);
    }

    protected function prepareWriter(string $text, ?ApprovalWriter $writer): ApprovalWriter
    {
        return $writer ?? new TextWriter($text);
    }

    protected function prepareFiles(ApprovalWriter $writer): array
    {
        $namer = $this->getNamer();
        return [
            'received' => $namer->getReceivedFile() . '.' . $writer->getFileExtension(),
            'approved' => $namer->getApprovedFile() . '.' . $writer->getFileExtension()
        ];
    }

    protected function writeReceivedFile(string $receivedFile, ApprovalWriter $writer): void
    {
        $writer->write($receivedFile);
    }

    protected function handleNewTest(array $files): void
    {
        file_put_contents($files['approved'], '');
        $this->getReporter()->report($files['received'], $files['approved']);
        throw new ApprovalException(
            "Nouveau test : veuillez vérifier le fichier received et le copier dans approved s'il est correct.\n" .
            "Received: {$files['received']}\n" .
            "Approved: {$files['approved']}"
        );
    }

    protected function compareFiles(array $files, string $receivedText): void
    {
        $approvedText = file_get_contents($files['approved']);

        try {
            if ($this->isBinaryContent($receivedText) || $this->isBinaryContent($approvedText)) {
                // Comparaison binaire directe
                Assert::assertEquals(
                    $approvedText,
                    $receivedText,
                    "Le contenu binaire reçu ne correspond pas au contenu approuvé"
                );
            } else {
                // Normaliser les textes avant comparaison
                $approvedText = $this->normalizeText($approvedText);
                $receivedText = $this->normalizeText($receivedText);

                Assert::assertEquals(
                    $approvedText,
                    $receivedText,
                    "Le contenu reçu ne correspond pas au contenu approuvé"
                );
            }
            unlink($files['received']);
        } catch (\Exception $e) {
            $this->getReporter()->report($files['received'], $files['approved']);
            throw $e;
        }
    }

    protected function normalizeText(string $text): string
    {
        // Supprimer les retours à la ligne et les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Supprimer les espaces entre les balises
        $text = preg_replace('/>\s+</', '><', $text);
        
        // Supprimer les espaces au début et à la fin
        $text = trim($text);
        
        return $text;
    }

    protected function isBinaryContent(string $content): bool
    {
        // Vérifie si le contenu contient des caractères non imprimables
        return preg_match('/[^\x20-\x7E\t\r\n]/', $content) === 1;
    }
} 