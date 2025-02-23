<?php

namespace ChqThomas\ApprovalTests\Formatter;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CustomNormalizer implements NormalizerInterface
{
    private string $class;
    private $callback;

    public function __construct(string $class, callable $callback)
    {
        $this->class = $class;
        $this->callback = $callback;
    }

    public function normalize($object, ?string $format = null, array $context = [])
    {
        return call_user_func($this->callback, $object);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof $this->class;
    }
}
