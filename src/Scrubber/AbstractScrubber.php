<?php

namespace ChqThomas\ApprovalTests\Scrubber;

abstract class AbstractScrubber implements ScrubberInterface
{
    protected int $guidCounter = 1;
    protected int $dateCounter = 1;
    protected array $dateMap = [];
    protected array $guidMap = [];
    /** @var array<callable|ScrubberInterface> */
    protected array $additionalScrubbers = [];
    protected array $ignoredMembers = [];
    protected array $scrubbedMembers = [];

    /**
     * @param callable|ScrubberInterface $scrubber
     */
    public function addScrubber($scrubber): self
    {
        $this->additionalScrubbers[] = $scrubber;
        return $this;
    }

    public function ignoreMember(string ...$members): self
    {
        $this->ignoredMembers = array_merge($this->ignoredMembers, $members);
        return $this;
    }

    public function scrubMember(string ...$members): self
    {
        $this->scrubbedMembers = array_merge($this->scrubbedMembers, $members);
        return $this;
    }

    public function resetCounters(): self
    {
        $this->guidCounter = 1;
        $this->dateCounter = 1;
        $this->dateMap = [];
        $this->guidMap = [];
        return $this;
    }

    protected function scrubGuids(string $content): string
    {
        return preg_replace_callback(
            '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i',
            function ($matches) {
                $guid = $matches[0];
                if (!isset($this->guidMap[$guid])) {
                    $this->guidMap[$guid] = 'Guid_' . $this->guidCounter++;
                }
                return $this->guidMap[$guid];
            },
            $content
        );
    }

    protected function scrubDates(string $content): string
    {
        return preg_replace_callback(
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?/',
            function ($matches) {
                $date = $matches[0];
                if (!isset($this->dateMap[$date])) {
                    $this->dateMap[$date] = 'DateTimeOffset_' . $this->dateCounter++;
                }
                return $this->dateMap[$date];
            },
            $content
        );
    }

    protected function applyAdditionalScrubbers(string $content): string
    {
        foreach ($this->additionalScrubbers as $scrubber) {
            if ($scrubber instanceof ScrubberInterface) {
                $content = $scrubber->scrub($content);
            } elseif (\is_callable($scrubber)) {
                $content = $scrubber($content);
            }
        }
        return $content;
    }

    protected function handleMembers(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (in_array($key, $this->ignoredMembers)) {
                unset($data[$key]);
                continue;
            }

            if (in_array($key, $this->scrubbedMembers)) {
                $data[$key] = '[scrubbed]';
                continue;
            }

            if (is_array($value)) {
                $this->handleMembers($value);
                $data[$key] = $value;
            }
        }
    }

    /**
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }
}
