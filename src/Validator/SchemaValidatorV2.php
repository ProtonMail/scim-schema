<?php

namespace Tmilos\ScimSchema\Validator;

use Tmilos\ScimSchema\Helper;
use Tmilos\ScimSchema\Model\Schema\Attribute;
use Tmilos\ScimSchema\Model\Schema;
use Tmilos\ScimSchema\ScimConstants;

class SchemaValidatorV2
{
    private static array $commonAttributes = [
        'id' => 1,
        'schemas' => 1,
        'externalId' => 1,
        'meta' => 1,
    ];

    /**
     * @param Schema[] $schemaExtensions
     */
    public function validate(array $object, Schema $schema, array $schemaExtensions = []): ValidationResult
    {
        $validationResult = new ValidationResult();
        /** @var Schema[] $otherSchemas */
        $otherSchemas = [];
        foreach ($schemaExtensions as $schemaExtension) {
            $otherSchemas[$schemaExtension->getId()] = $schemaExtension;
        }

        $this->validateByAttributes(
            $object,
            $schema->getId(),
            $schema->getAttributes(),
            $otherSchemas,
            $validationResult,
            null,
        );

        foreach ($otherSchemas as $schemaId => $otherSchema) {
            if (isset($object[$schemaId])) {
                $this->validateByAttributes(
                    $object[$schemaId],
                    $otherSchema->getId(),
                    $otherSchema->getAttributes(),
                    [],
                    $validationResult,
                    null,
                );
            }
        }

        return $validationResult;
    }

    /**
     * @param Attribute[] $attributes
     */
    private function validateByAttributes(
        array $object,
        string $schemaId,
        array $attributes,
        array $ignoreAttributes,
        ValidationResult $validationResult,
        ?string $parentPath,
    ): void {
        foreach ($object as $propertyName => $value) {
            if ($parentPath === null && isset(self::$commonAttributes[$propertyName])) {
                // skip common resource attributes
                continue;
            }
            if ($parentPath === null && isset($ignoreAttributes[$propertyName])) {
                continue;
            }

            $attribute = Helper::findAttribute($propertyName, $attributes);
            if (!$attribute) {
                continue;
            }

            if ($attribute->isRequired() === false && $value === null) {
                continue;
            }

            if ($this->isArray($value)) {
                if (!$attribute->isMultiValued()) {
                    $validationResult->add(
                        $propertyName,
                        $parentPath,
                        $schemaId,
                        'Attribute is not defined in schema as multi-valued, but got array',
                    );
                    continue;
                } else {
                    foreach ($value as $item) {
                        $this->validateByAttributes(
                            $item,
                            $schemaId,
                            $attribute->getSubAttributes(),
                            [],
                            $validationResult,
                            $propertyName,
                        );
                    }
                }
            } elseif ($this->isObject($value)) {
                if ($attribute->isMultiValued()) {
                    $validationResult->add(
                        $propertyName,
                        $parentPath,
                        $schemaId,
                        'Attribute is defined in schema as multi-valued, but got object',
                    );
                    continue;
                } elseif ($attribute->getType() !== ScimConstants::ATTRIBUTE_TYPE_COMPLEX) {
                    $validationResult->add(
                        $propertyName,
                        $parentPath,
                        $schemaId,
                        'Attribute is not defined in schema as complex, but got object',
                    );
                    continue;
                }
                $this->validateByAttributes(
                    $value,
                    $schemaId,
                    $attribute->getSubAttributes(),
                    [],
                    $validationResult,
                    $propertyName,
                );
            } else {
                if ($attribute->isMultiValued()) {
                    $validationResult->add(
                        $propertyName,
                        $parentPath,
                        $schemaId,
                        'Attribute is defined in schema as multi-valued, but got scalar',
                    );
                    continue;
                } elseif ($attribute->getType() === ScimConstants::ATTRIBUTE_TYPE_COMPLEX) {
                    $validationResult->add(
                        $propertyName,
                        $parentPath,
                        $schemaId,
                        'Attribute is defined in schema as complex, but got scalar',
                    );
                    continue;
                } elseif (!$attribute->isValueValid($value)) {
                    $validationResult->add(
                        $propertyName,
                        $parentPath,
                        $schemaId,
                        sprintf('Attribute has invalid value for type "%s"', $attribute->getType()),
                    );
                    continue;
                }
            }
        }
    }

    private function isArray(mixed $value): bool
    {
        return is_array($value) && Helper::hasAllIntKeys($value);
    }

    private function isObject(mixed $value): bool
    {
        return is_array($value) && Helper::hasAllStringKeys($value);
    }
}