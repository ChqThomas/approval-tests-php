<?php

namespace ChqThomas\ApprovalTests\Writer;

class TextWriter implements ApprovalWriter
{
    private string $text;
    private string $extension;

    public function __construct(string $text, string $extension = 'txt')
    {
        $this->text = $text;
        $this->extension = $extension;
    }

    public function getReceivedText(): string
    {
        return $this->text;
    }

    public function write(string $received): void
    {
        file_put_contents($received, $this->text);
    }

    public function getFileExtension(): string
    {
        return $this->extension;
    }
}
