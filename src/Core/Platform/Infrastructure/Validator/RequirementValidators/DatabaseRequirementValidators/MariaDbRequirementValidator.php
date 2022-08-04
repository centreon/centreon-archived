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

namespace Core\Platform\Infrastructure\Validator\RequirementValidators\DatabaseRequirementValidators;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Infrastructure\Validator\RequirementValidators\DatabaseRequirementValidatorInterface;
use Centreon\Domain\VersionHelper;

class MariaDbRequirementValidator implements DatabaseRequirementValidatorInterface
{
    use LoggerTrait;

    /**
     * @param string $requiredMariaDbMinVersion
     */
    public function __construct(
        private string $requiredMariaDbMinVersion,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isValidFor(string $versionComment): bool
    {
        $this->info(
            'Checking if version comment contains MariaDB string',
            [
                'version_comment' => $versionComment,
            ],
        );

        return strpos($versionComment, "MariaDB") !== false;
    }

    /**
     * {@inheritDoc}
     *
     * @throws MariaDbRequirementException
     */
    public function validateRequirementOrFail(string $version): void
    {
        $currentMariaDBMajorVersion = VersionHelper::regularizeDepthVersion($version, 1);

        $this->info(
            'Comparing current MariaDB version ' . $currentMariaDBMajorVersion
            . ' to minimal required version ' . $this->requiredMariaDbMinVersion
        );

        if (
            VersionHelper::compare($currentMariaDBMajorVersion, $this->requiredMariaDbMinVersion, VersionHelper::LT)
        ) {
            $this->error('MariaDB requirement is not validated');

            throw MariaDbRequirementException::badMariaDbVersion(
                $this->requiredMariaDbMinVersion,
                $currentMariaDBMajorVersion,
            );
        }
    }
}
