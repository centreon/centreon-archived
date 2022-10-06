<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Platform\Infrastructure\Validator\RequirementValidators;

use Core\Platform\Application\Validator\RequirementException;

class PhpRequirementException extends RequirementException
{
    /**
     * @return self
     */
    public static function badPhpVersion(string $requiredPhpVersion, string $installedPhpVersion): self
    {
        return new self(
            sprintf(
                _('PHP version %s required (%s installed)'),
                $requiredPhpVersion,
                $installedPhpVersion,
            ),
        );
    }

    /**
     * @param string $extensionName
     * @return self
     */
    public static function phpExtensionNotLoaded(string $extensionName): self
    {
        return new self(
            sprintf(
                _('PHP extension %s not loaded'),
                $extensionName,
            ),
        );
    }
}
