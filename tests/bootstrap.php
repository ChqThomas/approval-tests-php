<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ChqThomas\ApprovalTests\Configuration;
use ChqThomas\ApprovalTests\Scrubber\CsvScrubber;
use ChqThomas\ApprovalTests\Scrubber\JsonScrubber;
use ChqThomas\ApprovalTests\Scrubber\RegexScrubber;
use ChqThomas\ApprovalTests\Scrubber\XmlScrubber;
use ChqThomas\ApprovalTests\Formatter\DefaultObjectFormatter;

Configuration::getInstance()
    ->setAutoApprove(true)
//    ->setDefaultScrubber('json', new JsonScrubber())
//    ->setDefaultScrubber('xml', new XmlScrubber())
//    ->setDefaultScrubber('csv', new CsvScrubber())
//    ->setDefaultScrubber('regex', new RegexScrubber())
//    ->setObjectFormatter(new DefaultObjectFormatter())
;
