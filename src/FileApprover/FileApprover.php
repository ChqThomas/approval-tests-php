<?php

namespace ChqThomas\ApprovalTests\FileApprover;

use ChqThomas\ApprovalTests\Core\ApprovalNamer;
use ChqThomas\ApprovalTests\Namer\TestNamer;

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
