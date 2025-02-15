<?php

namespace ApprovalTests\Reporter;

class DiffReporter implements ReporterInterface
{
    private array $diffTools = [
        'PhpStorm' => [
            'Windows' => 'phpstorm diff',
            'Linux' => 'phpstorm diff',
            'Mac' => 'phpstorm diff'
        ],
        'VSCode' => [
            'Windows' => 'code --diff',
            'Linux' => 'code --diff',
            'Mac' => 'code --diff'
        ],
    ];

    public function report(string $approvedFile, string $receivedFile): void
    {
        $os = $this->getOperatingSystem();

        foreach ($this->diffTools as $tool => $paths) {
            if (isset($paths[$os])) {
                if ($this->isWsl()) {
                    $command = sprintf(
                        'nohup %s "$(wslpath -w "%s")" "$(wslpath -w "%s")" > /dev/null 2>&1 &',
                        $paths[$os],
                        $receivedFile,
                        $approvedFile
                    );
                } elseif ($os === 'Windows') {
                    $command = sprintf(
                        'START "" %s "%s" "%s"',
                        $paths[$os],
                        $receivedFile,
                        $approvedFile
                    );
                } else {
                    $command = sprintf(
                        'nohup %s "%s" "%s" > /dev/null 2>&1 &',
                        $paths[$os],
                        $receivedFile,
                        $approvedFile
                    );
                }

                if ($this->canLaunchCommand($paths[$os])) {
                    exec($command);
                    return;
                }
            }
        }

//        throw new \RuntimeException(
//            "Aucun outil de diff n'a été trouvé. Veuillez installer l'un des outils suivants : " .
//            implode(', ', array_keys($this->diffTools))
//        );
    }

    private function getOperatingSystem(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'Windows';
        }
        if (PHP_OS_FAMILY === 'Darwin') {
            return 'Mac';
        }
        return 'Linux';
    }

    private function canLaunchCommand(string $command): bool
    {
        $baseCommand = explode(' ', $command)[0];

        if (PHP_OS_FAMILY === 'Windows') {
            return file_exists($baseCommand);
        }

        // Pour Linux/Mac, vérifie si la commande existe dans le PATH
        $whichCommand = "which " . escapeshellarg($baseCommand);
        exec($whichCommand, $output, $returnVar);

        return $returnVar === 0;
    }

    private function isWsl(): bool
    {
        return file_exists('/proc/version') &&
               strpos(strtolower(file_get_contents('/proc/version')), 'microsoft') !== false;
    }

}
