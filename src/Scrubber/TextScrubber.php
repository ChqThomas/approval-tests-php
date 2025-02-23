<?php

namespace ChqThomas\ApprovalTests\Scrubber;

use ChqThomas\ApprovalTests\Core\Scrubber;

class TextScrubber extends AbstractScrubber
{
    public function scrub(string $content): string
    {
        $this->resetCounters();
        $content = $this->scrubGuids($content);
        $content = $this->scrubDates($content);
        return $this->applyAdditionalScrubbers($content);
    }
}
