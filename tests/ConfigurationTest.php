<?php

namespace ChqThomas\ApprovalTests\Tests;

use ChqThomas\ApprovalTests\Reporter\ReporterInterface;
use PHPUnit\Framework\TestCase;
use ChqThomas\ApprovalTests\Configuration;
use ChqThomas\ApprovalTests\Formatter\DefaultObjectFormatter;
use ChqThomas\ApprovalTests\Formatter\SymfonyObjectFormatter;
use ChqThomas\ApprovalTests\Scrubber\JsonScrubber;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->configuration = Configuration::getInstance();
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $instance1 = Configuration::getInstance();
        $instance2 = Configuration::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testReporterGetterSetter(): void
    {
        $mockReporter = $this->createMock(ReporterInterface::class);
        $this->configuration->setReporter($mockReporter);

        $this->assertSame($mockReporter, $this->configuration->getReporter());
    }

    public function testDefaultObjectFormatterIsSet(): void
    {
        $formatter = $this->configuration->getObjectFormatter();

        if (class_exists(\Symfony\Component\Serializer\Serializer::class)) {
            $this->assertInstanceOf(SymfonyObjectFormatter::class, $formatter);
        } else {
            $this->assertInstanceOf(DefaultObjectFormatter::class, $formatter);
        }
    }

    public function testDefaultScrubberGetterSetter(): void
    {
        $jsonScrubber = new JsonScrubber();
        $this->configuration->setDefaultScrubber('json', $jsonScrubber);

        $this->assertSame($jsonScrubber, $this->configuration->getDefaultScrubber('json'));
    }

    public function testNonExistentScrubberReturnsNull(): void
    {
        $this->assertNull($this->configuration->getDefaultScrubber('nonexistent'));
    }

    public function testAutoApproveGetterSetter(): void
    {
        $initialValue = $this->configuration->isAutoApprove();
        $this->configuration->setAutoApprove(true);
        $this->assertTrue($this->configuration->isAutoApprove());

        $this->configuration->setAutoApprove(false);
        $this->assertFalse($this->configuration->isAutoApprove());
        $this->configuration->setAutoApprove($initialValue);
    }

    public function testAutoApproveEnvironmentVariable(): void
    {
        $initialValue = getenv('APPROVE_SNAPSHOTS');
        putenv('APPROVE_SNAPSHOTS=true');
        $this->assertTrue($this->configuration->isAutoApprove());

        putenv('APPROVE_SNAPSHOTS=false');
        $this->configuration->setAutoApprove(false);
        $this->assertFalse($this->configuration->isAutoApprove());

        putenv('APPROVE_SNAPSHOTS='.$initialValue);
    }

    public function testDefaultScrubberInitialization(): void
    {
        $formats = ['json', 'xml', 'csv', 'regex', 'html', 'text'];

        foreach ($formats as $format) {
            $this->assertNotNull(
                $this->configuration->getDefaultScrubber($format),
                "Default scrubber for {$format} should be initialized"
            );
        }
    }
}
