<?php

namespace ApprovalTests\Reporter;

use ApprovalTests\Core\ApprovalReporter;

class ReporterFactory
{
    private static array $reporters = [];
    private static ?ApprovalReporter $defaultReporter = null;

    public static function get(string $reporterClass): ApprovalReporter
    {
        if (!isset(self::$reporters[$reporterClass])) {
            self::$reporters[$reporterClass] = new $reporterClass();
        }
        return self::$reporters[$reporterClass];
    }

    public static function getDefaultReporter(): ApprovalReporter
    {
        if (self::$defaultReporter === null) {
            self::$defaultReporter = new CliReporter();
        }
        return self::$defaultReporter;
    }

    public static function setDefaultReporter(ApprovalReporter $reporter): void
    {
        self::$defaultReporter = $reporter;
    }
}
