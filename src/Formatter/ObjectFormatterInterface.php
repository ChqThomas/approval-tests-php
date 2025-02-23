<?php

namespace ChqThomas\ApprovalTests\Formatter;

interface ObjectFormatterInterface
{
    /**
     * Check if the formatter can format the given object.
     *
     * @param mixed $object Object to check
     */
    public function canFormat($object): bool;

    /**
     * Format the given object.
     *
     * @param mixed $object Object to format
     */
    public function format($object): string;
}
