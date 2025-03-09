<?php

namespace ChqThomas\ApprovalTests\FileApprover;

use ChqThomas\ApprovalTests\Namer\NamerInterface;

class FileApproverFactory
{
    /**
     * @var class-string<FileApproverInterface>
     */
    private string $fileApproverClass;

    /**
     * @var class-string<NamerInterface>
     */
    private string $namerClass;

    /**
     * @param class-string<FileApproverInterface> $fileApproverClass
     * @param class-string<NamerInterface> $namerClass
     */
    public function __construct(string $fileApproverClass, string $namerClass)
    {
        $this->fileApproverClass = $fileApproverClass;
        $this->namerClass = $namerClass;
    }

    public function createFileApprover(): FileApproverInterface
    {
        return new $this->fileApproverClass(new $this->namerClass());
    }

    /**
     * @param class-string<FileApproverInterface> $fileApproverClass
     * @return void
     */
    public function setFileApproverClass(string $fileApproverClass): void
    {
        $this->fileApproverClass = $fileApproverClass;
    }

    /**
     * @param class-string<NamerInterface> $namerClass
     * @return $this
     */
    public function setNamerClass(string $namerClass): self
    {
        $this->namerClass = $namerClass;

        return $this;
    }
}
