<?php

namespace ChqThomas\ApprovalTests\FileApprover;

use ChqThomas\ApprovalTests\Namer\NamerInterface;

class FileApprover extends FileApproverBase
{
    private NamerInterface $namer;

    public function __construct(NamerInterface $namer)
    {
        $this->namer = $namer;
    }

    protected function getNamer(): NamerInterface
    {
        return $this->namer;
    }

    public function setNamer(NamerInterface $namer): void
    {
        $this->namer = $namer;
    }
}
