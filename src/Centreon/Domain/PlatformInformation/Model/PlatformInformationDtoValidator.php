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
    public const ADDITIONAL_PROPERTIES_KEY = "additionalProperties",
                 PROPERTIES_KEY = "properties",
                 REQUIRED_KEY = "required",
                 TYPE_KEY = "type",
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
        try {
            if (!is_array($schema)) {
                throw PlatformInformationDtoValidatorException::BadJSONSchemaFormatException();
            }
            $this->validateAdditionalPropertiesRecursively($schema, $dto);
            $this->validateRequiredPropertiesRecursively($schema, $dto);
            $this->validateNonNullPropertiesRecursively($schema, $dto);
            $this->validatePropertiesTypeRecursively($schema, $dto);
        } catch (PlatformInformationDtoValidatorException $ex) {
            throw $ex;
        }
    }

    /**
     * Validate that no additional properties has been sent.
     *
     * @param array $schema
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    private function validateAdditionalPropertiesRecursively(array $schema, array $dto): void
    {
        if (
            array_key_exists(self::ADDITIONAL_PROPERTIES_KEY, $schema)
            && $schema[self::ADDITIONAL_PROPERTIES_KEY] === false
        ) {
            foreach ($dto as $key => $value) {
                if (is_array($value)) {
                    $this->validateAdditionalPropertiesRecursively($schema[self::PROPERTIES_KEY][$key], $value);
                }
                if (!array_key_exists($key, $schema[self::PROPERTIES_KEY])) {
                    throw PlatformInformationDtoValidatorException::additionalPropertiesNotAllowedException();
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
    private function validateNonNullPropertiesRecursively(array $schema, array $dto): void
    {
        $schemaProperties = $schema[self::PROPERTIES_KEY];
        foreach ($dto as $key => $value) {
            if (is_array($value)) {
                $this->validateNonNullPropertiesRecursively($schemaProperties[$key], $value);
            }
            if (
                is_array($schemaProperties[$key][self::TYPE_KEY])
                && !in_array(self::NULL_TYPE, $schemaProperties[$key][self::TYPE_KEY])
                && $value === null
            ) {
                throw PlatformInformationDtoValidatorException::nullPropertiesNotAllowedException();
            } elseif (
                !is_array($schemaProperties[$key][self::TYPE_KEY])
                && $schemaProperties[$key][self::TYPE_KEY] !== self::NULL_TYPE
                && $value === null
            ) {
                throw PlatformInformationDtoValidatorException::nullPropertiesNotAllowedException();
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
    private function validateRequiredPropertiesRecursively(array $schema, array $dto): void
    {
        foreach ($dto as $key => $value) {
            if (is_array($value)) {
                $this->validateRequiredPropertiesRecursively($schema[self::PROPERTIES_KEY][$key], $value);
            }
        }
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
     * Validate that the properties type are valid.
     *
     * @param array $schema
     * @param array $dto
     * @throws PlatformInformationDtoValidatorException
     */
    private function validatePropertiesTypeRecursively(array $schema, array $dto): void
    {
        $schemaProperties = $schema[self::PROPERTIES_KEY];
        foreach ($dto as $key => $value) {
            $schemaPropertyType = $schemaProperties[$key][self::TYPE_KEY];
            if (is_array($value)) {
                $this->validatePropertiesTypeRecursively($schemaProperties[$key], $value);
            } else {
                if (is_array($schemaPropertyType)) {
                    $dtoPropertyType = gettype($value);
                    $this->validateArrayOfPropertyTypes($schemaPropertyType, $key, $dtoPropertyType);
                } else {
                    $this->validatePropertyType($schemaPropertyType, $key, $value);
                }
            }
        }
    }

    /**
     * Validate the dtoProperty against the schema type array.
     *
     * @param array $schemaPropertyType
     * @param string $key
     * @param string $dtoPropertyType
     * @throws PlatformInformationDtoValidatorException
     */
    private function validateArrayOfPropertyTypes(array $schemaPropertyType, string $key, string $dtoPropertyType)
    {
        if ($dtoPropertyType === "NULL" && !in_array(self::NULL_TYPE, $schemaPropertyType)) {
            throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                $dtoPropertyType, $key, implode(", ", $schemaPropertyType)
            );
        } elseif ($dtoPropertyType === "array" && !in_array(self::OBJECT_TYPE, $schemaPropertyType)) {
            throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                $dtoPropertyType, $key, implode(", ", $schemaPropertyType)
            );
        } elseif (
            $dtoPropertyType !== "NULL"
            && $dtoPropertyType !== "array"
            && !in_array($dtoPropertyType, $schemaPropertyType)
        ) {
            throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                $dtoPropertyType, $key, implode(", ", $schemaPropertyType)
            );
        }
    }

    /**
     * Validate the dtoProperty against the schema type.
     *
     * @param string $schemaPropertyType
     * @param string $key
     * @param mixed $dtoProperty
     */
    private function validatePropertyType(string $schemaPropertyType, string $key, $dtoProperty)
    {
        switch ($schemaPropertyType) {
            case self::STRING_TYPE:
                if (!is_string($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $key, $schemaPropertyType
                    );
                }
                break;
            case self::INTEGER_TYPE:
                if (!is_int($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $key, $schemaPropertyType
                    );
                }
                break;
            case self::BOOLEAN_TYPE:
                if (!is_bool($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $key, $schemaPropertyType
                    );
                }
                break;
            case self::OBJECT_TYPE:
                if (!is_array($dtoProperty) || !array_key_exists(self::PROPERTIES_KEY, $dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $key, $schemaPropertyType
                    );
                }
                break;
            case self::NULL_TYPE:
                if (!is_null($dtoProperty)) {
                    throw PlatformInformationDtoValidatorException::invalidPropertyTypeException(
                        gettype($dtoProperty), $key, $schemaPropertyType
                    );
                }
                break;
        }
    }
}
