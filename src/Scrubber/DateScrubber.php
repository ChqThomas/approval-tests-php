<?php

namespace ApprovalTests\Scrubber;

class DateScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        // Replace ISO dates
        $content = preg_replace('/\d{4}-\d{2}-\d{2}/', 'YYYY-MM-DD', $content);

        // Replace timestamps
        $content = preg_replace('/\d{10,}/', 'TIMESTAMP', $content);

        return $content;
    }
}
