<?php

namespace Tests\Xml;

use ApprovalTests\Approvals;
use ApprovalTests\Scrubber\XmlScrubber;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    private string $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<body>
  <node>text</node>
</body>
XML;

    /**
     * @test
     */
    public function xml(): void
    {
        Approvals::verifyXml($this->xml);
    }

    /**
     * @test
     */
    public function noDeclaration(): void
    {
        $xml = <<<XML
<body>
  <node>text</node>
</body>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function comment(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- name is John Doe --></person>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function commentWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- value --></person>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function commentMix(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- name is John Doe -->value</person>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function commentMixWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- value -->value</person>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(fn($content) => str_replace('value', 'replaced', $content)));
    }

    /**
     * @test
     */
    public function cdata(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[name is John Doe]]></person>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function cdataWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[value]]></person>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(fn($content) => str_replace('value', 'replaced', $content)));
    }

    /**
     * @test
     */
    public function cdataMix(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[name is John Doe]]>value</person>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function cdataMixWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[value]]>value</person>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(fn($content) => str_replace('value', 'replaced', $content)));
    }

    /**
     * @test
     */
    public function scrubbing(): void
    {
        $date = date('Y-m-d\TH:i:s.u\Z');
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<body>
  <node att="$date">$date</node>
</body>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function emptyTag(): void
    {
        $xml = <<<XML
<body>
  <empty />
  <node>text</node>
</body>
XML;
        Approvals::verifyXml($xml);
    }

    /**
     * @test
     */
    public function emptyTagWithAttributes(): void
    {
        $guid = '550e8400-e29b-41d4-a716-446655440000';
        $xml = <<<XML
<body>
  <empty id="$guid" att="asdf" />
  <node>text</node>
</body>
XML;
        Approvals::verifyXml($xml);
    }
} 