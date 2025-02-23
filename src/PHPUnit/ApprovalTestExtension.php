<?php

namespace ChqThomas\ApprovalTests\PHPUnit;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class ApprovalTestExtension extends AbstractApprovalTestHandler implements Extension, FailedSubscriber
{
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters
    ): void {
        if ($configuration->noOutput()) {
            return;
        }

        $facade->registerSubscriber($this);
    }

    public function notify(Failed $event): void
    {
        $this->handleThrowable($event->throwable());
    }

    /**
     * For PHPUnit Extension
     */
    protected function handleThrowable(\PHPUnit\Event\Code\Throwable $throwable): void
    {
        // @todo find a way to get the original exception
    }
}
