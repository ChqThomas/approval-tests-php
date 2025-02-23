<?php

namespace ApprovalTests\Formatter;

class DateTimeFormatter implements ObjectFormatterInterface
{
    public function canFormat($object): bool
    {
        return $object instanceof \DateTimeInterface;
    }

    public function format($object): string
    {
        return $object->format(\DateTimeInterface::ATOM);
    }
}
