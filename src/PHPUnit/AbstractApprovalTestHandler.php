<?php

namespace ChqThomas\ApprovalTests\PHPUnit;

use ChqThomas\ApprovalTests\CustomApprovalException;
use ChqThomas\ApprovalTests\Reporter\DiffReporter;
use PHPUnit\Framework\ExceptionWrapper;

abstract class AbstractApprovalTestHandler
{
    public function openDiffReporter(CustomApprovalException $exception): void
    {
        $reporter = new DiffReporter();
        $reporter->report($exception->getApprovedFile(), $exception->getReceivedFile());
    }
}
