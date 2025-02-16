<?php

namespace ApprovalTests\Core;

use ApprovalTests\ApprovalException;
use ApprovalTests\Configuration;
use ApprovalTests\CustomApprovalException;
use ApprovalTests\Writer\ApprovalWriter;
use ApprovalTests\Writer\TextWriter;
use ApprovalTests\Writer\BinaryWriter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

abstract class FileApproverBase
{
    /** @var string */
    private $approvedFile;

    /** @var string */
    private $receivedFile;

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

    public function verify($received, ?Scrubber $scrubber = null, ?ApprovalWriter $writer = null): void
    {
        $receivedText = $this->prepareReceivedText($received, $scrubber);
        $writer = $this->prepareWriter($receivedText, $writer);

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
        throw new CustomApprovalException(
            "Nouveau test : veuillez vérifier le fichier received et le copier dans approved s'il est correct.\n",
            $files['approved'],
            $files['received']
        );
    }

    protected function formatFileLink(string $path): string
    {
        $realPath = realpath($path);
        $url = sprintf('phpstorm://open?file=%s&line=%s', rawurlencode($realPath), 0);
        return sprintf("\x1b]8;;%s\x1b\\%s\x1b]8;;\x1b\\", $url, $realPath);
    }

    protected function compareFiles(array $files, string $receivedText): void
    {
        $approvedText = file_get_contents($files['approved']);
        $receivedPath = realpath($files['received']);
        $approvedPath = realpath($files['approved']);

        try {
            $isBinary = $this->isBinaryContent($receivedText) || $this->isBinaryContent($approvedText);
            $normalizedReceived = $isBinary ? $receivedText : $this->normalizeText($receivedText);
            $normalizedApproved = $isBinary ? $approvedText : $this->normalizeText($approvedText);

            if ($normalizedApproved !== $normalizedReceived) {
                $this->reportFailure(
                    $receivedPath,
                    $approvedPath,
                    $isBinary,
                    $approvedText,
                    $receivedText
                );
            }

            Assert::assertTrue(true);
            unlink($files['received']);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function reportFailure(
        string $receivedPath,
        string $approvedPath,
        bool $isBinary,
        string $approvedText,
        string $receivedText
    ): void {
        $messageType = $isBinary ? 'binaire' : '';
        $message = sprintf(
            "Received: %s\n" .
            "Approved: %s",
            $this->formatFileLink($receivedPath),
            $this->formatFileLink($approvedPath)
        );

        $this->getReporter()->report($receivedPath, $approvedPath);

        $failure = new ComparisonFailure(
            $approvedText,
            $receivedText,
            $this->formatTextForDiff($approvedText),
            $this->formatTextForDiff($receivedText)
        );

        throw new CustomApprovalException(
            $message . $failure->getDiff(),
            $approvedPath,
            $receivedPath
        );
    }

    protected function formatTextForDiff(string $text): string
    {
        // Convertir tous les CRLF et CR en LF
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Supprimer les espaces en fin de ligne
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        $text = implode("\n", $lines);

        // S'assurer que le texte se termine par une nouvelle ligne
        return rtrim($text) . "\n";
    }

    protected function normalizeText(string $text): string
    {
        // Normaliser les fins de ligne
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Supprimer les espaces en fin de ligne
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        
        // Reconstruire le texte
        $text = implode("\n", $lines);
        
        // Supprimer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Supprimer les espaces entre les balises
        $text = preg_replace('/>\s+</', '><', $text);
        
        return trim($text);
    }

    protected function isBinaryContent(string $content): bool
    {
        // Vérifie si le contenu contient des caractères non imprimables
        return preg_match('/[^\x20-\x7E\t\r\n]/', $content) === 1;
    }
}
