<?php

namespace ApprovalTests\Scrubber;

class HtmlScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        // For malformed HTML, return as is
        if (strpos($content, '<') !== false && strpos($content, '>') !== false) {
            $dom = new \DOMDocument('1.0');

            // Try to parse the HTML
            $internalErrors = libxml_use_internal_errors(true);
            if (@$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR)) {
                // If parsing succeeds, return the formatted HTML
                $html = '';
                $body = $dom->getElementsByTagName('body')->item(0);
                if ($body) {
                    foreach ($body->childNodes as $child) {
                        $html .= $dom->saveHTML($child);
                    }
                    return trim($html);
                }
            }
            libxml_use_internal_errors($internalErrors);
        }

        // If parsing fails or if it's not HTML, return as is
        return trim($content);
    }
}
