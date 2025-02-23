<?php

namespace ChqThomas\ApprovalTests\Scrubber;

class CsvScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Split into lines
        $lines = explode("\n", $content);

        // Clean each line
        $lines = array_map(function ($line) {
            return trim($line);
        }, $lines);

        return implode("\n", $lines);
    }
}
