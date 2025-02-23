<?php

namespace ChqThomas\ApprovalTests\Formatter;

use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class SymfonyObjectFormatter implements ObjectFormatterInterface
{
    private SerializerInterface $serializer;

    /**
     * @param string[] $ignoredAttributes
     * @param array<string, callable> $customNormalizers
     */
    public function __construct(
        array $ignoredAttributes = [],
        array $customNormalizers = []
    ) {
        $circularReferenceHandler = function ($object) {
            if (method_exists($object, '__toString')) {
                return ['__toString' => $object->__toString()];
            }
            return [];
        };

        $defaultContext = [
            'attributes' => null,
            'enable_max_depth' => true,
            'ignored_attributes' => $ignoredAttributes,
            'circular_reference_handler' => $circularReferenceHandler,
        ];

        $objectNormalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

        // Compatibility with Symfony Serializer 4.x
        if (method_exists($objectNormalizer, 'setCircularReferenceHandler')) {
            $objectNormalizer->setCircularReferenceHandler($circularReferenceHandler);
        }
        if (method_exists($objectNormalizer, 'setIgnoredAttributes')) {
            $objectNormalizer->setIgnoredAttributes($ignoredAttributes);
        }

        $normalizers = [
            new DateTimeNormalizer(),
            new ContextObjectNormalizer($objectNormalizer),
        ];

        foreach ($customNormalizers as $class => $callback) {
            $normalizers[] = new CustomNormalizer($class, $callback);
        }

        $this->serializer = new Serializer(
            $normalizers,
            [new YamlEncoder()]
        );

        $objectNormalizer->setSerializer($this->serializer);
    }

    public function canFormat($object): bool
    {
        return is_object($object);
    }

    public function format($object): string
    {
        return $this->serializer->serialize($object, 'yaml', [
            'yaml_inline' => 99,
            'yaml_indent' => 0,
            'yaml_flags' => Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
        ]);
    }

    public static function create(): self
    {
        return new self();
    }
}
