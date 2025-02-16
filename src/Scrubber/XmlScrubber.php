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

        // Charger le XML
        @$dom->loadXML($content);

        // Récupérer le XML sans la déclaration
        $xml = $dom->saveXML($dom->documentElement);
        
        // Appliquer les scrubbers
        $xml = $this->scrubGuids($xml);
        $xml = $this->scrubDates($xml);
        $xml = $this->applyAdditionalScrubbers($xml);

        return trim($xml);
    }
}
