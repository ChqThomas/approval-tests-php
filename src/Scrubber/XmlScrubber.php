<?php

namespace ApprovalTests\Scrubber;

class XmlScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        // Charger le XML
        @$dom->loadXML($content);

        // Sauvegarder avec la déclaration XML
        return trim($dom->saveXML());
    }
}
