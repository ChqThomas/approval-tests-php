<?php

namespace ApprovalTests\Writer;

class BinaryFileWriter implements ApprovalWriter
{
    private string $content;
    private string $extension;

    public function __construct(string $content, string $extension)
    {
        $this->content = $content;
        $this->extension = $extension;
    }

    public function getReceivedText(): string
    {
        return $this->content;
    }

    public function write(string $received): void
    {
        file_put_contents($received, $this->content);
    }

    public function getFileExtension(): string
    {
        return $this->extension;
    }
}
