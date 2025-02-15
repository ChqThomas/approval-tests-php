<?php

namespace ApprovalTests\Core;

interface ApprovalNamer
{
    public function getApprovedFile(): string;
    public function getReceivedFile(): string;
}
