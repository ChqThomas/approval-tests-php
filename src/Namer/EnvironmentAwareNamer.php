<?php

namespace ApprovalTests\Namer;

use ApprovalTests\Core\ApprovalNamer;

class EnvironmentAwareNamer implements ApprovalNamer
{
    private ApprovalNamer $baseNamer;
    private string $environmentName;

    public function __construct(ApprovalNamer $baseNamer, string $environmentName)
    {
        $this->baseNamer = $baseNamer;
        $this->environmentName = $environmentName;
    }

    public function getApprovedFile(): string
    {
        return $this->addEnvironmentToPath($this->baseNamer->getApprovedFile());
    }

    public function getReceivedFile(): string
    {
        return $this->addEnvironmentToPath($this->baseNamer->getReceivedFile());
    }

    private function addEnvironmentToPath(string $path): string
    {
        $pathInfo = pathinfo($path);
        return sprintf(
            '%s/%s.%s.%s',
            $pathInfo['dirname'],
            $pathInfo['filename'],
            $this->sanitizeEnvironmentName($this->environmentName),
            $pathInfo['extension']
        );
    }

    private function sanitizeEnvironmentName(string $name): string
    {
        return str_replace(['/', '\\', ' '], '_', $name);
    }
}
