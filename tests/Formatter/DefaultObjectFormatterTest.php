<?php

namespace ChqThomas\ApprovalTests\Tests\Formatter;

use ChqThomas\ApprovalTests\Approvals;
use ChqThomas\ApprovalTests\Configuration;
use ChqThomas\ApprovalTests\Formatter\DefaultObjectFormatter;
use ChqThomas\ApprovalTests\Formatter\SymfonyObjectFormatter;
use PHPUnit\Framework\TestCase;
use ChqThomas\ApprovalTests\Tests\fixtures\UserDto;

class DefaultObjectFormatterTest extends TestCase
{
    protected function setUp(): void
    {
        Configuration::getInstance()->setObjectFormatter(new DefaultObjectFormatter());
    }

    public function testObject(): void
    {
        $user = new UserDto();
        Approvals::verify($user);
    }

    public function testArrayOfObject(): void
    {
        $user = [new UserDto('User 1'), new UserDto('User 2'), new UserDto('User 3')];
        Approvals::verify($user);
    }

    public function testObjectWithParent(): void
    {
        $user = new UserDto('Level 1', new UserDto('Level 2', new UserDto('Level 3')));
        Approvals::verify($user);
    }

    public function testObjectWithCircularReference(): void
    {
        $parent = new UserDto('Parent 1', new UserDto('Parent 2'));
        $parent2 = new UserDto('Parent 3', $parent);
        $parent->child = $parent2;
        $user = new UserDto('Root User', $parent2);
        Approvals::verify($user);
    }
}
