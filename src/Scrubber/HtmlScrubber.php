<?php

namespace ApprovalTests\Scrubber;

class HtmlScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        // Pour le HTML malformé, retourner tel quel
        if (strpos($content, '<') !== false && strpos($content, '>') !== false) {
            $dom = new \DOMDocument('1.0');

            // Essayer de parser le HTML
            $internalErrors = libxml_use_internal_errors(true);
            if (@$dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR)) {
                // Si le parsing réussit, retourner le HTML formaté
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

        // Si le parsing échoue ou si ce n'est pas du HTML, retourner tel quel
        return trim($content);
    }
}
