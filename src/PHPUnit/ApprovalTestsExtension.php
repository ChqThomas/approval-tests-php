<?php

namespace ApprovalTests\PHPUnit;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

class ApprovalTestsExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new TestListener());
    }
} 