<?php

namespace ApprovalTests\Scrubber;

use ApprovalTests\Core\Scrubber;

class JsonScrubber extends AbstractScrubber
{
    public function scrub(string $content): string
    {
        // Décoder le JSON
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        // Gérer les membres à ignorer/scrubber
        $this->handleMembers($data);

        // Réencoder en JSON
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

        // Appliquer les autres scrubbers (GUIDs, dates, etc.)
        $json = $this->scrubGuids($json);
        $json = $this->scrubDates($json);
        $json = $this->applyAdditionalScrubbers($json);

        return $json;
    }
}
