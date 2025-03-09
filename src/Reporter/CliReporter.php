<?php

namespace ChqThomas\ApprovalTests\Reporter;

class CliReporter implements ReporterInterface
{
    public function report(string $approvedFile, string $receivedFile): void
    {
        // Do nothing as PHPUnit already handles error display
    }
}
