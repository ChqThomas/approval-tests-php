<?php

namespace ApprovalTests\Reporter;

interface ReporterInterface
{
    /**
     * Lance un reporter pour comparer les fichiers approved et received
     */
    public function report(string $approvedFile, string $receivedFile): void;
} 