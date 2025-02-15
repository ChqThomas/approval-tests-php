<?php

namespace ApprovalTests\Reporter;

class BeyondCompareReporter implements ReporterInterface
{
    public function report(string $approvedFile, string $receivedFile): void
    {
        $command = sprintf('bcompare "%s" "%s"', $receivedFile, $approvedFile);
        exec($command);
    }
}
