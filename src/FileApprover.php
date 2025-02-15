<?php

namespace ApprovalTests;

use ApprovalTests\Core\FileApproverBase;
use ApprovalTests\Core\ApprovalNamer;
use ApprovalTests\Namer\TestNamer;

class FileApprover extends FileApproverBase
{
    private ApprovalNamer $namer;

    public function __construct()
    {
        $this->namer = new TestNamer();
    }

    protected function getNamer(): ApprovalNamer
    {
        return $this->namer;
    }

    public function setNamer(ApprovalNamer $namer): void
    {
        $this->namer = $namer;
    }
}
