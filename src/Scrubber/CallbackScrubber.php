<?php

namespace ApprovalTests\Scrubber;

use ApprovalTests\Core\Scrubber;

class CallbackScrubber implements Scrubber
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
