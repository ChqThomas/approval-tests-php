<?php

namespace ApprovalTests\Scrubber;

use ApprovalTests\Core\Scrubber;

class RegexScrubber implements Scrubber
{
    private array $regexReplacements = [];

    public static function create(array $regexReplacements = []): self
    {
        $scrubber = new self();
        foreach ($regexReplacements as $pattern => $replacement) {
            $scrubber->addRegexReplacement($pattern, $replacement);
        }
        return $scrubber;
    }

    public function addRegexReplacement(string $pattern, string $replacement): self
    {
        $this->regexReplacements[] = [
            'pattern' => $pattern,
            'replacement' => $replacement
        ];
        return $this;
    }

    public function scrub(string $content): string
    {
        $result = $content;

        foreach ($this->regexReplacements as $replacement) {
            $result = preg_replace($replacement['pattern'], $replacement['replacement'], $result);
        }

        return $result;
    }
}
