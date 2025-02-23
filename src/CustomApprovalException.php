<?php

namespace ChqThomas\ApprovalTests;

use PHPUnit\Framework\AssertionFailedError;

class CustomApprovalException extends AssertionFailedError
{
    private string $approvedFile;
    private string $receivedFile;

    public function __construct(
        string $message,
        string $approvedFile,
        string $receivedFile
    ) {
        parent::__construct($message);
        $this->approvedFile = $approvedFile;
        $this->receivedFile = $receivedFile;
    }

    public function getApprovedFile(): string
    {
        return $this->approvedFile;
    }

    public function getReceivedFile(): string
    {
        return $this->receivedFile;
    }
}
