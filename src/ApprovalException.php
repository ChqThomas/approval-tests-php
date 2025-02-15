<?php

namespace ApprovalTests;

class ApprovalException extends \Exception
{
    private string $approvedFile;
    private string $receivedFile;

    public function __construct(string $message, string $approvedFile = '', string $receivedFile = '')
    {
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
