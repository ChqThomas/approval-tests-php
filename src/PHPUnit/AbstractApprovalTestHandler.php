<?php

namespace ApprovalTests\PHPUnit;

use ApprovalTests\CustomApprovalException;
use ApprovalTests\Reporter\DiffReporter;
use PHPUnit\Framework\ExceptionWrapper;

abstract class AbstractApprovalTestHandler
{
    public function openDiffReporter(CustomApprovalException $exception): void
    {
        $reporter = new DiffReporter();
        $reporter->report($exception->getApprovedFile(), $exception->getReceivedFile());
    }
}
