<?php

namespace ChqThomas\ApprovalTests\Scrubber;

use ChqThomas\ApprovalTests\Core\Scrubber;

class JsonScrubber extends AbstractScrubber
{
    public function scrub(string $content): string
    {
        $this->resetCounters();
        // Decode JSON
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        // Handle members to ignore/scrub
        $this->handleMembers($data);

        // Re-encode to JSON
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

        // Apply other scrubbers (GUIDs, dates, etc.)
        $json = $this->scrubGuids($json);
        $json = $this->scrubDates($json);
        $json = $this->applyAdditionalScrubbers($json);

        return $json;
    }
}
