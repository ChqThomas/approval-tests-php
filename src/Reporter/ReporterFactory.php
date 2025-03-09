<?php

namespace ChqThomas\ApprovalTests\Reporter;

class ReporterFactory
{
    private static array $reporters = [];
    private static ?ReporterInterface $defaultReporter = null;

    public static function get(string $reporterClass): ReporterInterface
    {
        if (!isset(self::$reporters[$reporterClass])) {
            self::$reporters[$reporterClass] = new $reporterClass();
        }
        return self::$reporters[$reporterClass];
    }

    public static function getDefaultReporter(): ReporterInterface
    {
        if (self::$defaultReporter === null) {
            self::$defaultReporter = new CliReporter();
        }
        return self::$defaultReporter;
    }

    public static function setDefaultReporter(ReporterInterface $reporter): void
    {
        self::$defaultReporter = $reporter;
    }
}
