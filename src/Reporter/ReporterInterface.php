<?php

namespace ChqThomas\ApprovalTests\Reporter;

interface ReporterInterface
{
    /**
     * Launch a reporter to compare approved and received files
     */
    public function report(string $approvedFile, string $receivedFile): void;
}
