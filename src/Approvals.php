<?php

namespace ChqThomas\ApprovalTests;

use ChqThomas\ApprovalTests\Combinations\CombinationApprovals;
use ChqThomas\ApprovalTests\Formatter\SymfonyObjectFormatter;
use ChqThomas\ApprovalTests\Scrubber\CsvScrubber;
use ChqThomas\ApprovalTests\Scrubber\HtmlScrubber;
use ChqThomas\ApprovalTests\Scrubber\JsonScrubber;
use ChqThomas\ApprovalTests\Scrubber\XmlScrubber;
use ChqThomas\ApprovalTests\Writer\BinaryWriter;
use ChqThomas\ApprovalTests\Writer\TextWriter;

class Approvals
{
    private static function getConfig(): Configuration
    {
        return Configuration::getInstance();
    }

    public static function verify($objectToVerify): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $text = is_string($objectToVerify)
            ? $objectToVerify
            : ((is_object($objectToVerify) || is_array($objectToVerify))
                ? self::getConfig()->getObjectFormatter()->format($objectToVerify)
                : json_encode($objectToVerify, JSON_PRETTY_PRINT));

        $extension = 'txt';

        // @todo should not be here
        if ((is_object($objectToVerify) || is_array($objectToVerify)) && self::getConfig()->getObjectFormatter() instanceof SymfonyObjectFormatter) {
            $extension = 'yaml';
        }

        $scrubber = $scrubber ?? self::getConfig()->getDefaultScrubber('text');
        $scrubbedJson = $scrubber->scrub($text);

        $writer = new TextWriter($scrubbedJson, $extension);
        $approver->verify($scrubbedJson, null, $writer);
    }

    public static function verifyHtml(string $html, ?HtmlScrubber $scrubber = null): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $scrubber = $scrubber ?? self::getConfig()->getDefaultScrubber('html');

        $scrubbedHtml = $scrubber->scrub($html);
        $writer = new TextWriter($scrubbedHtml, 'html');
        $approver->verify($scrubbedHtml, $scrubber, $writer);
    }

    public static function verifyJson(string $json, ?JsonScrubber $scrubber = null): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $scrubber = $scrubber ?? self::getConfig()->getDefaultScrubber('json');

        $scrubbedJson = $scrubber->scrub($json);
        $writer = new TextWriter($scrubbedJson, 'json');
        $approver->verify($scrubbedJson, $scrubber, $writer);
    }

    public static function verifyXml(string $xml, ?XmlScrubber $scrubber = null): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $scrubber = $scrubber ?? self::getConfig()->getDefaultScrubber('xml');

        $scrubbedXml = $scrubber->scrub($xml);
        $writer = new TextWriter($scrubbedXml, 'xml');
        $approver->verify($scrubbedXml, $scrubber, $writer);
    }

    public static function verifyFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $approver->verify($content);
    }

    public static function verifyBinaryFile(string $filePath, string $extension): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $content = file_get_contents($filePath);
        $writer = new BinaryWriter($filePath, $extension);
        $approver->verify($content, null, $writer);
    }

    public static function verifyAll(array $items, ?callable $formatter = null): void
    {
        $formatter = $formatter ?? fn ($item) => (string)$item;
        $text = implode("\n", array_map($formatter, $items));
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $approver->verify($text);
    }

    public static function verifyWithExtension(string $text, string $extension, ?callable $scrubber = null): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
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

    public static function verifyCsv(string $csv, ?CsvScrubber $scrubber = null): void
    {
        $approver = self::getConfig()->getFileApproverFactory()->createFileApprover();
        $scrubber = $scrubber ?? self::getConfig()->getDefaultScrubber('csv');

        $scrubbedCsv = $scrubber->scrub($csv);
        $writer = new TextWriter($scrubbedCsv, 'csv');
        $approver->verify($scrubbedCsv, $scrubber, $writer);
    }
}
