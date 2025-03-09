<?php

namespace ChqThomas\ApprovalTests\Symfony;

use ChqThomas\ApprovalTests\Approvals;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Test\Constraint\CrawlerSelectorExists;

trait ApprovalCrawlerAssertionsTrait
{
    public static function verifySelectorHtml(string $selector): void
    {
        Approvals::verifyHtml(self::getCrawler()->filter($selector)->html());
    }

    public static function verifySelectorText(string $selector): void
    {
        Approvals::verify(self::getCrawler()->filter($selector)->text());
    }

    private static function getCrawler(): Crawler
    {
        if (!method_exists(static::class, 'getClient')) {
            static::fail('You must use the ApprovalCrawlerAssertionsTrait in a class that has a getClient() method');
        }

        if (!$crawler = self::getClient()->getCrawler()) {
            static::fail('A client must have a crawler to make assertions. Did you forget to make an HTTP request?');
        }

        return $crawler;
    }
}
