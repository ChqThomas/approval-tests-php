<?php

namespace ApprovalTests\Core;

interface ApprovalReporter
{
    public function report(string $receivedFile, string $approvedFile): void;
} 