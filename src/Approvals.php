<?php

namespace ApprovalTests;

use ApprovalTests\Writer\TextWriter;
use ApprovalTests\Writer\BinaryWriter;
use ApprovalTests\Namer\EnvironmentAwareNamer;
use ApprovalTests\Namer\TestNamer;
use ApprovalTests\Scrubber\HtmlScrubber;
use ApprovalTests\Scrubber\JsonScrubber;
use ApprovalTests\Scrubber\XmlScrubber;
use ApprovalTests\Combinations\CombinationApprovals;
use ApprovalTests\Scrubber\CsvScrubber;
use ApprovalTests\Scrubber\Scrubber;

class Approvals
{
    public static function verify($objectToVerify): void
    {
        $approver = new FileApprover();
        $text = is_string($objectToVerify) ? $objectToVerify : json_encode($objectToVerify, JSON_PRETTY_PRINT);
        $writer = new TextWriter($text);
        $approver->verify($text, null, $writer);
    }

    public static function verifyHtml(string $html): void
    {
        $approver = new FileApprover();
        $writer = new TextWriter($html, 'html');
        $approver->verify($html, new HtmlScrubber(), $writer);
    }

    public static function verifyJson(string $json, ?JsonScrubber $scrubber = null): void
    {
        $approver = new FileApprover();
        $scrubber = $scrubber ?? new JsonScrubber();
        
        $scrubbedJson = $scrubber->scrub($json);
        $writer = new TextWriter($scrubbedJson, 'json');
        $approver->verify($scrubbedJson, $scrubber, $writer);
    }

    public static function verifyXml(string $xml, ?XmlScrubber $scrubber = null): void
    {
        $approver = new FileApprover();
        $scrubber = $scrubber ?? new XmlScrubber();
        
        $scrubbedXml = $scrubber->scrub($xml);
        $writer = new TextWriter($scrubbedXml, 'xml');
        $approver->verify($scrubbedXml, $scrubber, $writer);
    }

    public static function verifyFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        $approver = new FileApprover();
        $approver->verify($content);
    }

    public static function verifyBinaryFile(string $filePath, string $extension): void
    {
        $approver = new FileApprover();
        $content = file_get_contents($filePath);
        $writer = new BinaryWriter($filePath, $extension);
        $approver->verify($content, null, $writer);
    }

    public static function verifyAll(array $items, ?callable $formatter = null): void
    {
        $formatter = $formatter ?? fn ($item) => (string)$item;
        $text = implode("\n", array_map($formatter, $items));
        $approver = new FileApprover();
        $approver->verify($text);
    }

    public static function verifyWithExtension(string $text, string $extension, ?callable $scrubber = null): void
    {
        $approver = new FileApprover();
        $writer = new TextWriter($text, $extension);

        if ($scrubber) {
            $scrubbedText = $scrubber($text);
            $writer = new TextWriter($scrubbedText, $extension);
            $approver->verify($scrubbedText, null, $writer);
        } else {
            $approver->verify($text, null, $writer);
        }
    }

    public static function verifyAllCombinations(
        callable $func,
        array $parameters,
        ?callable $formatter = null
    ): void {
        CombinationApprovals::verifyAllCombinations($func, $parameters, $formatter);
    }

    public static function verifyWithEnvironment(string $content, string $environmentName): void
    {
        $approver = new FileApprover();
        $namer = new EnvironmentAwareNamer(new TestNamer(), $environmentName);
        $approver->setNamer($namer);
        $approver->verify($content);
    }

    public static function verifyCsv(string $csv): void
    {
        $approver = new FileApprover();
        $writer = new TextWriter($csv, 'csv');
        $approver->verify($csv, new CsvScrubber(), $writer);
    }
}
