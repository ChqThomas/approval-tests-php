<?php

namespace ChqThomas\ApprovalTests\Namer;

interface ApprovalNamer
{
    public function getApprovedFile(): string;
    public function getReceivedFile(): string;
}
