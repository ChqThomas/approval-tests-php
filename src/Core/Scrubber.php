<?php

namespace ChqThomas\ApprovalTests\Core;

interface Scrubber
{
    public function scrub(string $content): string;
}
