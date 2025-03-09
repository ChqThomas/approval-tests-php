<?php

namespace ChqThomas\ApprovalTests\Scrubber;

interface ScrubberInterface
{
    public function scrub(string $content): string;
}
