<?php

namespace ChqThomas\ApprovalTests\Reporter;

use ChqThomas\ApprovalTests\Core\ApprovalReporter;

class CliReporter implements ApprovalReporter
{
    public function report(string $receivedFile, string $approvedFile): void
    {
        // Do nothing as PHPUnit already handles error display
    }
}
