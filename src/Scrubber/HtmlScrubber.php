<?php

namespace ChqThomas\ApprovalTests\Scrubber;

use ChqThomas\ApprovalTests\Formatter\HtmlFormatter;

class HtmlScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        $dom = new \DOMDocument('1.0');
        $indenter = new HtmlFormatter();

        // Try to parse the HTML
        $internalErrors = libxml_use_internal_errors(true);

        // Check if it's a complete document or a fragment
        $isCompleteDocument =
            (stripos($content, '<!DOCTYPE') !== false ||
                stripos($content, '<html') !== false);

        if ($isCompleteDocument) {
            $loadContent = $content;
        } else {
            // Wrapper for HTML fragments
            $loadContent = "<!DOCTYPE html><html><body>" . $content . "</body></html>";
        }

        if (@$dom->loadHTML($loadContent, LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOXMLDECL)) {
            if ($isCompleteDocument) {
                // For complete documents, just get the body
                $body = $dom->saveHTML();
            } else {
                // For a fragment, get the body and indent it
                $bodyNode = $dom->getElementsByTagName('body')->item(0);
                $body = '';
                foreach ($bodyNode->childNodes as $child) {
                    $body .= $dom->saveHTML($child);
                }
            }

            $html = $indenter->indent($body);
            libxml_use_internal_errors($internalErrors);
            return trim($html);
        }

        libxml_use_internal_errors($internalErrors);
        // If parsing fails or if it's not HTML, return as is
        return trim($content);
    }
}
