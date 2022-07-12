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

namespace Core\Platform\Infrastructure\Repository\RequirementProviders;

use Core\Platform\Application\Repository\RequirementException;

class MariaDbRequirementException extends RequirementException
{
    /**
     * @param \Throwable $e
     * @return self
     */
    public static function errorWhenGettingMariaDbVersion(\Throwable $e): self
    {
        return new self(
            _('Error when getting MariaDB version'),
            0,
            $e,
        );
    }

    /**
     * @param string $requiredMariaDbVersion
     * @param string $installedMariaDbVersion
     * @return self
     */
    public static function badMariaDbVersion(string $requiredMariaDbVersion, string $installedMariaDbVersion): self
    {
        return new self(
            sprintf(
                _('MariaDB version %s required (%s installed)'),
                $requiredMariaDbVersion,
                $installedMariaDbVersion,
            ),
        );
    }
}
