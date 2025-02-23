<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ApprovalTests\Configuration;
use ApprovalTests\Scrubber\CsvScrubber;
use ApprovalTests\Scrubber\JsonScrubber;
use ApprovalTests\Scrubber\RegexScrubber;
use ApprovalTests\Scrubber\XmlScrubber;

Configuration::getInstance()
    ->setAutoApprove(true)
//    ->setDefaultScrubber('json', new JsonScrubber())
//    ->setDefaultScrubber('xml', new XmlScrubber())
//    ->setDefaultScrubber('csv', new CsvScrubber())
//    ->setDefaultScrubber('regex', new RegexScrubber())
//    ->setObjectFormatter(new \ApprovalTests\Formatter\DefaultObjectFormatter())
;
