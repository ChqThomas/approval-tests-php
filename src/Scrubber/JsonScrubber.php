<?php

namespace ApprovalTests\Scrubber;

class JsonScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        $data = json_decode($content);
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
