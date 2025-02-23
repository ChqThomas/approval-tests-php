<?php

namespace ApprovalTests;

use ApprovalTests\Core\ApprovalReporter;
use ApprovalTests\Core\Scrubber;
use ApprovalTests\Formatter\ObjectFormatterInterface;
use ApprovalTests\Reporter\ReporterFactory;
use ApprovalTests\Scrubber\CsvScrubber;
use ApprovalTests\Scrubber\HtmlScrubber;
use ApprovalTests\Scrubber\JsonScrubber;
use ApprovalTests\Scrubber\RegexScrubber;
use ApprovalTests\Scrubber\TextScrubber;
use ApprovalTests\Scrubber\XmlScrubber;

class Configuration
{
    private static ?Configuration $instance = null;
    private ?ApprovalReporter $reporter = null;
    private ?ObjectFormatterInterface $objectFormatter = null;
    /** @var array<string, Scrubber> */
    private array $defaultScrubbers = [];
    private bool $autoApprove = false;

    private function __construct()
    {
        $this
            ->setDefaultScrubber('json', new JsonScrubber())
            ->setDefaultScrubber('xml', new XmlScrubber())
            ->setDefaultScrubber('csv', new CsvScrubber())
            ->setDefaultScrubber('regex', new RegexScrubber())
            ->setDefaultScrubber('html', new HtmlScrubber())
            ->setDefaultScrubber('text', new TextScrubber());
        ;

        $this->setReporter(ReporterFactory::getDefaultReporter());

        if (class_exists(\Symfony\Component\Serializer\Serializer::class)) {
            $this->objectFormatter = new Formatter\SymfonyObjectFormatter();
        } else {
            $this->objectFormatter = new Formatter\DefaultObjectFormatter();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Getters et Setters
    public function setReporter(ApprovalReporter $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getReporter(): ApprovalReporter
    {
        return $this->reporter;
    }

    public function setObjectFormatter(ObjectFormatterInterface $formatter): self
    {
        $this->objectFormatter = $formatter;

        return $this;
    }

    public function getObjectFormatter(): ObjectFormatterInterface
    {
        return $this->objectFormatter;
    }

    public function setDefaultScrubber(string $format, Scrubber $scrubber): self
    {
        $this->defaultScrubbers[$format] = $scrubber;
        return $this;
    }

    public function getDefaultScrubber(string $format): ?Scrubber
    {
        return $this->defaultScrubbers[$format] ?? null;
    }

    public function setAutoApprove(bool $autoApprove): self
    {
        $this->autoApprove = $autoApprove;

        return $this;
    }

    public function isAutoApprove(): bool
    {
        return $this->autoApprove || getenv('APPROVE_SNAPSHOTS') === 'true';
    }
}
