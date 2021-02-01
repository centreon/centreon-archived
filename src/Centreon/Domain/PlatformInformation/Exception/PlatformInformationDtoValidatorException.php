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

namespace Centreon\Domain\PlatformInformation\Exception;

/**
 * This class is designed to validate the request body against a validation Json Schema.
 */
class PlatformInformationDtoValidatorException extends \Exception
{
    /**
     * @return self
     */
    public static function additionalPropertiesNotAllowedException(): self
    {
        return new self(_('Additional properties are not allowed'));
    }

    /**
     * @return self
     */
    public static function nullPropertiesNotAllowedException(): self
    {
        return new self(_('Nullable property is not allowed'));
    }

    /**
     * @param string $property
     * @return self
     */
    public static function missingRequiredPropertiesException(string $property): self
    {
        return new self(sprintf(_('Missing required property: %s'), $property));
    }

    /**
     * @param string $invalidType
     * @param string $propertyName
     * @param string $validType
     * @return self
     */
    public static function invalidPropertyTypeException(
        string $invalidType,
        string $propertyName,
        string $validType
    ): self {
        return new self(
            sprintf(_('Invalid property type %s for %s, type %s expected'), $invalidType, $propertyName, $validType)
        );
    }

    /**
     * @return self
     */
    public static function badJsonSchemaFormatException(): self
    {
        return new self(_('Bad JSON schema format'));
    }

    /**
     * @return self
     */
    public static function badJsonSchemaFilePathException(): self
    {
        return new self(_('Bad JSON schema path'));
    }
}
