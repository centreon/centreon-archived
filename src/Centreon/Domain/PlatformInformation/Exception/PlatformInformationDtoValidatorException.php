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

class PlatformInformationDtoValidatorException extends \Exception
{
    public static function additionalPropertiesNotAllowedException(): self
    {
        return new self(_('Additional properties are not allowed'));
    }

    public static function nullPropertiesNotAllowedException(): self
    {
        return new self(_('Nullable property is not allowed'));
    }

    public static function missingRequiredPropertiesException(string $property): self
    {
        return new self(sprintf(_('Missing required property: %s'), $property));
    }

    public static function invalidPropertyTypeException($invalidType, $propertyName, $validType): self
    {
        return new self(
            sprintf(_('Invalid property type %s for %s, type %s expected'), $invalidType, $propertyName, $validType)
        );
    }

    public static function BadJSONSchemaFormatException(): self
    {
        return new self(_('Bad JSON schema format'));
    }

    public static function BadJSONSchemaFilePathException(): self
    {
        return new self(_('Bad JSON schema path'));
    }
}