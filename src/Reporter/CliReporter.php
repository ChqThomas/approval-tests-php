<?php

namespace ApprovalTests\Reporter;

use ApprovalTests\Core\ApprovalReporter;

class CliReporter implements ApprovalReporter
{
    public function report(string $receivedFile, string $approvedFile): void
    {
        // Ne rien afficher car PHPUnit gère déjà l'affichage des erreurs
    }
}
