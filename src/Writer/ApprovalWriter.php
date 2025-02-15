<?php

namespace ApprovalTests\Writer;

interface ApprovalWriter
{
    public function getReceivedText(): string;
    public function write(string $received): void;
    public function getFileExtension(): string;
}
