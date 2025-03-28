<?php

namespace ChqThomas\ApprovalTests\FileApprover;

use ChqThomas\ApprovalTests\Configuration;
use ChqThomas\ApprovalTests\CustomApprovalException;
use ChqThomas\ApprovalTests\Namer\NamerInterface;
use ChqThomas\ApprovalTests\Reporter\ReporterInterface;
use ChqThomas\ApprovalTests\Scrubber\ScrubberInterface;
use ChqThomas\ApprovalTests\Writer\ApprovalWriter;
use ChqThomas\ApprovalTests\Writer\TextWriter;
use PHPUnit\Framework\Assert;
use SebastianBergmann\Comparator\ComparisonFailure;

abstract class FileApproverBase implements FileApproverInterface
{
    protected function getReporter(): ReporterInterface
    {
        return Configuration::getInstance()->getReporter();
    }

    abstract protected function getNamer(): NamerInterface;

    protected function normalizeLineEndings(string $text): string
    {
        // Convert all \r\n and \r to \n
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Remove trailing spaces
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        $text = implode("\n", $lines);

        // Remove empty lines at the end
        return rtrim($text);
    }

    public function verify($received, ?ScrubberInterface $scrubber = null, ?ApprovalWriter $writer = null): void
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

    protected function prepareReceivedText($received, ?ScrubberInterface $scrubber): string
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
        if (Configuration::getInstance()->isAutoApprove()) {
            copy($files['received'], $files['approved']);
            return;
        }

        file_put_contents($files['approved'], '');
        $this->getReporter()->report($files['received'], $files['approved']);
        throw new CustomApprovalException(
            "New test: please verify the received file and copy it to approved if correct.\n",
            $files['approved'],
            $files['received']
        );
    }

    protected function formatFileLink(string $path): string
    {
        $realPath = realpath($path);
        // @todo not needed when run from phpstorm terminal
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
        // Convert all CRLF and CR to LF
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Remove trailing spaces
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        $text = implode("\n", $lines);

        // Ensure text ends with a newline
        return rtrim($text) . "\n";
    }

    protected function normalizeText(string $text): string
    {
        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Remove trailing spaces
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);

        // Rebuild text
        $text = implode("\n", $lines);

        // Remove multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove spaces between tags
        $text = preg_replace('/>\s+</', '><', $text);

        return trim($text);
    }

    protected function isBinaryContent(string $content): bool
    {
        // Check if content contains non-printable characters
        return preg_match('/[^\x20-\x7E\t\r\n]/', $content) === 1;
    }
}
