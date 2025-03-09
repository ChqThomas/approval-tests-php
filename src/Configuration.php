<?php

namespace ChqThomas\ApprovalTests;

use ChqThomas\ApprovalTests\FileApprover\FileApprover;
use ChqThomas\ApprovalTests\FileApprover\FileApproverFactory;
use ChqThomas\ApprovalTests\FileApprover\FileApproverInterface;
use ChqThomas\ApprovalTests\Formatter\DefaultObjectFormatter;
use ChqThomas\ApprovalTests\Formatter\ObjectFormatterInterface;
use ChqThomas\ApprovalTests\Formatter\SymfonyObjectFormatter;
use ChqThomas\ApprovalTests\Namer\NamerInterface;
use ChqThomas\ApprovalTests\Namer\TestNamer;
use ChqThomas\ApprovalTests\Reporter\ReporterFactory;
use ChqThomas\ApprovalTests\Reporter\ReporterInterface;
use ChqThomas\ApprovalTests\Scrubber\CsvScrubber;
use ChqThomas\ApprovalTests\Scrubber\HtmlScrubber;
use ChqThomas\ApprovalTests\Scrubber\JsonScrubber;
use ChqThomas\ApprovalTests\Scrubber\RegexScrubber;
use ChqThomas\ApprovalTests\Scrubber\ScrubberInterface;
use ChqThomas\ApprovalTests\Scrubber\TextScrubber;
use ChqThomas\ApprovalTests\Scrubber\XmlScrubber;

class Configuration
{
    private static ?Configuration $instance = null;
    private ?ReporterInterface $reporter = null;
    /** @var class-string<NamerInterface>|null  */
    private ?string $namerClass = null;
    private ?ObjectFormatterInterface $objectFormatter = null;
    private ?FileApproverFactory $fileApproverFactory = null;

    /** @var array<string, ScrubberInterface> */
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
            ->setDefaultScrubber('text', new TextScrubber())
            ->setReporter(ReporterFactory::getDefaultReporter())
            ->setNamerClass(TestNamer::class)
            ->setFileApproverFactory(new FileApproverFactory(FileApprover::class, TestNamer::class))
        ;

        if (class_exists(\Symfony\Component\Serializer\Serializer::class)) {
            $this->objectFormatter = new SymfonyObjectFormatter();
        } else {
            $this->objectFormatter = new DefaultObjectFormatter();
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
    public function setReporter(ReporterInterface $reporter): self
    {
        $this->reporter = $reporter;

        return $this;
    }

    public function getReporter(): ReporterInterface
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

    public function setDefaultScrubber(string $format, ScrubberInterface $scrubber): self
    {
        $this->defaultScrubbers[$format] = $scrubber;
        return $this;
    }

    public function getDefaultScrubber(string $format): ?ScrubberInterface
    {
        return $this->defaultScrubbers[$format] ?? null;
    }

    public function setAutoApprove(bool $autoApprove): self
    {
        $this->autoApprove = $autoApprove;

        return $this;
    }

    public function getNamerClass(): string
    {
        return $this->namerClass;
    }

    public function setNamerClass(string $namerClass): self
    {
        $this->namerClass = $namerClass;

        return $this;
    }

    public function getFileApproverFactory(): ?FileApproverFactory
    {
        return $this->fileApproverFactory;
    }

    public function setFileApproverFactory(?FileApproverFactory $fileApproverFactory): self
    {
        $this->fileApproverFactory = $fileApproverFactory;

        return $this;
    }

    public function isAutoApprove(): bool
    {
        return $this->autoApprove || getenv('APPROVE_SNAPSHOTS') === 'true';
    }
}
