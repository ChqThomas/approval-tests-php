<?php

namespace ChqThomas\ApprovalTests\Formatter;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContextObjectNormalizer implements NormalizerInterface
{
    private NormalizerInterface $decoratedNormalizer;

    public function __construct(
        NormalizerInterface $decoratedNormalizer
    ) {
        $this->decoratedNormalizer = $decoratedNormalizer;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|bool|float|int|mixed|string|string[]
     * @throws ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = $this->decoratedNormalizer->normalize($object, $format, $context);

        return array_merge(
            ['__class' => get_class($object)],
            is_string($data) ? ['value' => $data] : $data
        );
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->decoratedNormalizer->getSupportedTypes($format);
    }
}
