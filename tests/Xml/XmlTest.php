<?php

namespace Tests\Xml;

use ApprovalTests\Approvals;
use ApprovalTests\Scrubber\RegexScrubber;
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

    public function testXml(): void
    {
        Approvals::verifyXml($this->xml);
    }


    public function testNoDeclaration(): void
    {
        $xml = <<<XML
<body>
  <node>text</node>
</body>
XML;
        Approvals::verifyXml($xml);
    }

    public function testComment(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- name is John Doe --></person>
XML;
        Approvals::verifyXml($xml);
    }


    public function testCommentWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- value --></person>
XML;
        Approvals::verifyXml($xml);
    }

    public function testCommentMix(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- name is John Doe -->value</person>
XML;
        Approvals::verifyXml($xml);
    }

    public function testCommentMixWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><!-- value -->value</person>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(fn ($content) => str_replace('value', 'replaced', $content)));
    }

    public function testCdata(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[name is John Doe]]></person>
XML;
        Approvals::verifyXml($xml);
    }

    public function testCdataWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[value]]></person>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(fn ($content) => str_replace('value', 'replaced', $content)));
    }

    public function testCdataMix(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[name is John Doe]]>value</person>
XML;
        Approvals::verifyXml($xml);
    }

    public function testCdataMixWithScrub(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<person><![CDATA[value]]>value</person>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(fn ($content) => str_replace('value', 'replaced', $content)));
    }

    public function testScrubbing(): void
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

    public function testEmptyTag(): void
    {
        $xml = <<<XML
<body>
  <empty />
  <node>text</node>
</body>
XML;
        Approvals::verifyXml($xml);
    }

    public function testEmptyTagWithAttributes(): void
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

    public function testRegexScrubbing(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<body>
  <node>ABC123</node>
  <node>DEF456</node>
  <node>GHI789</node>
</body>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(RegexScrubber::create(['/[A-Z]{3}\d{3}/' => 'MATCHED'])));
    }

    /**
     * @test
     */
    public function testMultipleRegexScrubbing(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<body>
  <node id="user123">John Doe</node>
  <node id="user456">Jane Smith</node>
</body>
XML;

        Approvals::verifyXml($xml, XmlScrubber::create()
            ->addScrubber(RegexScrubber::create([
                '/user\d{3}/' => 'userXXX',
                '/[A-Z][a-z]+ [A-Z][a-z]+/' => 'PERSON_NAME'
            ])));
    }
}
