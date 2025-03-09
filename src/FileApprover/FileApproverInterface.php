<?php

namespace ChqThomas\ApprovalTests\FileApprover;

use ChqThomas\ApprovalTests\Scrubber\ScrubberInterface;
use ChqThomas\ApprovalTests\Writer\ApprovalWriter;

interface FileApproverInterface
{
    public function verify($received, ?ScrubberInterface $scrubber = null, ?ApprovalWriter $writer = null): void;
}
