<?php

namespace ChqThomas\ApprovalTests\Scrubber;

use ChqThomas\ApprovalTests\Core\Scrubber;

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
