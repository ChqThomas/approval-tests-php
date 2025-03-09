<?php

namespace ChqThomas\ApprovalTests\Scrubber;

abstract class ScrubberBase implements ScrubberInterface
{
    protected array $replacements = [];

    public function addReplacement(string $search, string $replace): self
    {
        $this->replacements[$search] = $replace;
        return $this;
    }

    public function scrub(string $content): string
    {
        $result = $this->preProcess($content);
        $result = $this->applyReplacements($result);
        return $this->postProcess($result);
    }

    protected function preProcess(string $content): string
    {
        return $content;
    }

    protected function applyReplacements(string $content): string
    {
        return str_replace(
            array_keys($this->replacements),
            array_values($this->replacements),
            $content
        );
    }

    protected function postProcess(string $content): string
    {
        return $content;
    }
}
