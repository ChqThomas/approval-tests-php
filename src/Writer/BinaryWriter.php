<?php

namespace ApprovalTests\Writer;

class BinaryWriter implements ApprovalWriter
{
    private string $filePath;
    private string $extension;
    private string $content;

    public function __construct(string $filePath, string $extension)
    {
        $this->filePath = $filePath;
        $this->extension = $extension;
        $this->content = file_get_contents($filePath);
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
