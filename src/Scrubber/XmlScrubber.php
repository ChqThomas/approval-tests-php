<?php

namespace ApprovalTests\Scrubber;

use ApprovalTests\Core\Scrubber;

class XmlScrubber extends AbstractScrubber
{
    public function scrub(string $content): string
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        // Load XML
        @$dom->loadXML($content);

        // Get XML without declaration
        $xml = $dom->saveXML($dom->documentElement);

        // Apply scrubbers
        $xml = $this->scrubGuids($xml);
        $xml = $this->scrubDates($xml);
        $xml = $this->applyAdditionalScrubbers($xml);

        return trim($xml);
    }
}
