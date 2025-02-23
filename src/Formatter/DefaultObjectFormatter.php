<?php

namespace ChqThomas\ApprovalTests\Formatter;

class DefaultObjectFormatter implements ObjectFormatterInterface
{
    /**
     * @var array<ObjectFormatterInterface>
     */
    private array $formatters = [];

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
        return is_object($object);
    }

    public function format($object): string
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
        $reflection = new \ReflectionClass($object);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $result = [];
        $result[] = "Class: " . get_class($object);

        foreach ($properties as $property) {
            $value = $property->getValue($object);
            $formattedValue = $this->formatValue($value);
            $result[] = "  {$property->getName()}: {$formattedValue}";
        }

        return implode("\n", $result);
    }

    private function formatValue($value): string
    {
        if (is_object($value)) {
            return $this->format($value);
        } elseif (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        } else {
            return var_export($value, true);
        }
    }
}
