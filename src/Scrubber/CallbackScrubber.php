<?php

namespace ChqThomas\ApprovalTests\Scrubber;

class CallbackScrubber implements ScrubberInterface
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function scrub(string $content): string
    {
        return ($this->callback)($content);
    }
}
