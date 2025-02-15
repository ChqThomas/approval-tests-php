<?php

namespace ApprovalTests\Scrubber;

class CsvScrubber extends ScrubberBase
{
    protected function preProcess(string $content): string
    {
        // Normaliser les fins de ligne
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Séparer en lignes
        $lines = explode("\n", $content);
        
        // Nettoyer chaque ligne
        $lines = array_map(function($line) {
            return trim($line);
        }, $lines);
        
        return implode("\n", $lines);
    }
} 