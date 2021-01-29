<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Centreon\Domain\PlatformInformation\Model;

use Centreon\Domain\PlatformInformation\Interfaces\DtoValidatorInterface;
use Centreon\Domain\PlatformInformation\Exception\PlatformInformationDtoValidatorException;

class PlatformInformationDtoValidator implements DtoValidatorInterface
{
    /**
     * Schema Key use to validate.
     */
    public const ADDITIONAL_PROPERTIES_KEY = "additionalProperties",
                 PROPERTIES_KEY = "properties",
                 REQUIRED_KEY = "required";

    /**
     * Valid properties type.
     */
    public const TYPE_KEY = "type",
                 STRING_TYPE = "string",
                 BOOLEAN_TYPE = "boolean",
                 INTEGER_TYPE = "integer",
                 OBJECT_TYPE = "object",
                 NULL_TYPE = "null";

    /**
     * @param string $jsonSchemaPath
     */
    private $jsonSchemaPath;

    public function __construct(string $jsonSchemaPath)
    {
        $this->jsonSchemaPath = $jsonSchemaPath;
    }

    /**
     * validate the request DTO.
     *
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    public function validateOrFail(array $dto): void
    {
        $schema = file_get_contents($this->jsonSchemaPath);
        if ($schema === false) {
            throw PlatformInformationDtoValidatorException::BadJSONSchemaFilePathException();
        }
        $schema = json_decode($schema, true);

        if (!is_array($schema)) {
            throw PlatformInformationDtoValidatorException::BadJSONSchemaFormatException();
        }
        $this->validateAdditionalPropertiesRecursivelyOrFail($schema, $dto);
        $this->validateRequiredPropertiesRecursivelyOrFail($schema, $dto);
        $this->validateNonNullPropertiesRecursivelyOrFail($schema, $dto);
        $this->validatePropertiesTypeRecursivelyOrFail($schema, $dto);
    }

    /**
     * Validate that no additional properties has been sent.
     *
     * @param array $schema
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    private function validateAdditionalPropertiesRecursivelyOrFail(array $schema, array $dto): void
    {
        if (
            array_key_exists(self::ADDITIONAL_PROPERTIES_KEY, $schema)
            && $schema[self::ADDITIONAL_PROPERTIES_KEY] === false
        ) {
            foreach ($dto as $key => $value) {
                /**
                 * As the schema has no properties with type 'array', is_array is inevitably a json object decoded,
                 * So it is needed to check its own properties with recursion.
                 */
                if (is_array($value)) {
                    $this->validateAdditionalPropertiesRecursivelyOrFail($schema[self::PROPERTIES_KEY][$key], $value);
                }

                /**
                 * If the properties is present into the dto but not in the schema, throw Exception.
                 */
                if (!array_key_exists($key, $schema[self::PROPERTIES_KEY])) {
                    throw PlatformInformationDtoValidatorException::additionalPropertiesNotAllowedException();
                }
            }
        }
    }

    /**
     * Validate that all required properties has been declared.
     *
     * @param array $schema
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    private function validateRequiredPropertiesRecursivelyOrFail(array $schema, array $dto): void
    {
        foreach ($dto as $key => $value) {
            if (is_array($value)) {
                $this->validateRequiredPropertiesRecursivelyOrFail($schema[self::PROPERTIES_KEY][$key], $value);
            }
        }
        /**
         * If some properties are required in schema, but missing into the dto, throw Exception.
         */
        if (array_key_exists(self::REQUIRED_KEY, $schema)) {
            foreach ($schema[self::REQUIRED_KEY] as $parameterRequired) {
                if (!array_key_exists($parameterRequired, $dto)) {
                    throw PlatformInformationDtoValidatorException::missingRequiredPropertiesException(
                        $parameterRequired
                    );
                }
            }
        }
    }

    /**
     * Validate that properties are not null if nullable is not allow.
     *
     * @param array $schema
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    private function validateNonNullPropertiesRecursivelyOrFail(array $schema, array $dto): void
    {
        $schemaProperties = $schema[self::PROPERTIES_KEY];
        foreach ($dto as $key => $value) {
            if (is_array($value)) {
                $this->validateNonNullPropertiesRecursivelyOrFail($schemaProperties[$key], $value);
            }
            if (
                /**
                 * Check for multiple allowed type if null is present.
                 * e.g type: ["string", "integer"]
                 *
                 * Throw an Exception if the dto value is null and "null" is not present into the type array.
                 */
                is_array($schemaProperties[$key][self::TYPE_KEY])
                && !in_array(self::NULL_TYPE, $schemaProperties[$key][self::TYPE_KEY])
                && $value === null
            ) {
                throw PlatformInformationDtoValidatorException::nullPropertiesNotAllowedException();
            } elseif (
                /**
                 * Check for single allowed type.
                 * e.g type: "string"
                 *
                 * Throw an Exception if the dto value is null and "null" is not the allowed type.
                 */
                !is_array($schemaProperties[$key][self::TYPE_KEY])
                && $schemaProperties[$key][self::TYPE_KEY] !== self::NULL_TYPE
                && $value === null
            ) {
                throw PlatformInformationDtoValidatorException::nullPropertiesNotAllowedException();
            }
        }
    }

    /**
     * Validate that the properties type are valid.
     *
     * @param array $schema
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    private function validatePropertiesTypeRecursivelyOrFail(array $schema, array $dto): void
    {
        $schemaProperties = $schema[self::PROPERTIES_KEY];
        foreach ($dto as $propertyName => $value) {
            $schemaPropertyType = $schemaProperties[$propertyName][self::TYPE_KEY];
            if (is_array($value)) {
                $this->validatePropertiesTypeRecursivelyOrFail($schemaProperties[$propertyName], $value);
            } else {
                if (is_array($schemaPropertyType)) {
                    $dtoPropertyType = gettype($value);
                    $this->validateArrayOfPropertyTypes($schemaPropertyType, $propertyName, $dtoPropertyType);
                } else {
                    $this->validatePropertyType($schemaPropertyType, $propertyName, $value);
                }
            }
        }
    }

    /**
     * Validate the dtoProperty against the schema type array.
     *
     * @param array $schemaPropertyType
     * @param string $propertyName
     * @param string $dtoPropertyType
     * @throws PlatformInformationDtoValidatorException
     */
    private function validateArrayOfPropertyTypes(array $schemaPropertyType, string $propertyName, string $dtoPropertyType)
    {
        /**
         * gettype(null) will return "NULL" so we need to check this value for a null type.
         */
        if ($dtoPropertyType === "NULL" && !in_array(self::NULL_TYPE, $schemaPropertyType)) {
            throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                $dtoPropertyType, $propertyName, implode(", ", $schemaPropertyType)
            );
        /**
         * array is inevitably an object, so if an array is detected, we check that object type is allow.
         */
        } elseif ($dtoPropertyType === "array" && !in_array(self::OBJECT_TYPE, $schemaPropertyType)) {
            throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                $dtoPropertyType, $propertyName, implode(", ", $schemaPropertyType)
            );
        /**
         * For other case simply check if the type is present into the allowed types.
         */
        } elseif (
            $dtoPropertyType !== "NULL"
            && $dtoPropertyType !== "array"
            && !in_array($dtoPropertyType, $schemaPropertyType)
        ) {
            throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                $dtoPropertyType, $propertyName, implode(", ", $schemaPropertyType)
            );
        }
    }

    /**
     * Validate the dtoProperty type against the schema type.
     *
     * @param string $schemaPropertyType
     * @param string $propertyName
     * @param mixed $dtoProperty
     */
    private function validatePropertyType(string $schemaPropertyType, string $propertyName, $dtoProperty)
    {
        switch ($schemaPropertyType) {
            case self::STRING_TYPE:
                if (!is_string($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $propertyName, $schemaPropertyType
                    );
                }
                break;
            case self::INTEGER_TYPE:
                if (!is_int($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $propertyName, $schemaPropertyType
                    );
                }
                break;
            case self::BOOLEAN_TYPE:
                if (!is_bool($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $propertyName, $schemaPropertyType
                    );
                }
                break;
            case self::OBJECT_TYPE:
                if (!is_array($dtoProperty) || !array_key_exists(self::PROPERTIES_KEY, $dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $propertyName, $schemaPropertyType
                    );
                }
                break;
            case self::NULL_TYPE:
                if (!is_null($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $propertyName, $schemaPropertyType
                    );
                }
                break;
        }
    }
}
