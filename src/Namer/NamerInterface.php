<?php

namespace ChqThomas\ApprovalTests\Namer;

interface NamerInterface
{
    public function getApprovedFile(): string;
    public function getReceivedFile(): string;
}
