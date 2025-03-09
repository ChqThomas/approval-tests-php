<?php

namespace ChqThomas\ApprovalTests\Formatter;

class DefaultObjectFormatter implements ObjectFormatterInterface
{
    /**
     * @var array<ObjectFormatterInterface>
     */
    private array $formatters = [];

    /**
     * @var array<string, bool>
     */
    private array $processedObjects = [];

    public function __construct()
    {
        $this->addFormatter(new DateTimeFormatter());
    }

    public function addFormatter(ObjectFormatterInterface $formatter): self
    {
        $this->formatters[] = $formatter;
        return $this;
    }

    public function canFormat($object): bool
    {
        return is_object($object) || is_array($object);
    }

    public function format($input): string
    {
        $this->processedObjects = []; // Reset processed objects for new format operation

        if (is_array($input)) {
            return $this->formatArray($input);
        }

        return $this->formatWithCircularCheck($input);
    }

    private function formatWithCircularCheck($object): string
    {
        foreach ($this->formatters as $formatter) {
            if ($formatter->canFormat($object)) {
                return $formatter->format($object);
            }
        }

        return $this->formatObjectRecursively($object);
    }

    private function formatObjectRecursively($object): string
    {
        $objectHash = spl_object_hash($object);

        if (isset($this->processedObjects[$objectHash])) {
            return "** Circular Reference to " . get_class($object) . " **";
        }

        $this->processedObjects[$objectHash] = true;

        $reflection = new \ReflectionClass($object);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];
        $result[] = "Class: " . get_class($object);

        foreach ($properties as $property) {
            $value = $property->getValue($object);
            $formattedValue = $this->formatValue($value);
            $result[] = "  {$property->getName()}: {$formattedValue}";
        }

        unset($this->processedObjects[$objectHash]);

        return implode("\n", $result);
    }

    private function formatValue($value): string
    {
        if (is_object($value)) {
            return $this->formatWithCircularCheck($value);
        } elseif (is_array($value)) {
            return $this->formatArray($value);
        } else {
            return var_export($value, true);
        }
    }

    private function formatArray($array): string
    {
        $formatted = [];
        foreach ($array as $value) {
            if (is_object($value)) {
                $formatted[] = $this->formatWithCircularCheck($value);
            } elseif (is_array($value)) {
                $formatted[] = $this->formatArray($value);
            } else {
                $formatted[] = var_export($value, true);
            }
        }
        return implode("\n", $formatted);
    }
}
