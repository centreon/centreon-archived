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

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Validator\RequirementValidatorInterface;
use Centreon\Domain\VersionHelper;

class PhpRequirementValidator implements RequirementValidatorInterface
{
    use LoggerTrait;

    public const EXTENSION_REQUIREMENTS = [
        'pdo_mysql',
        'gd',
        'ldap',
        'xmlwriter',
        'mbstring',
        'pdo_sqlite',
        'intl',
    ];

    /**
     * @param string $requiredPhpVersion
     */
    public function __construct(private string $requiredPhpVersion)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @throws PhpRequirementException
     */
    public function validateRequirementOrFail(): void
    {
        $this->validatePhpVersionOrFail();
        $this->validatePhpExtensionsOrFail();
    }

    /**
     * Check installed php version
     *
     * @throws PhpRequirementException
     */
    private function validatePhpVersionOrFail(): void
    {
        $currentPhpMajorVersion = VersionHelper::regularizeDepthVersion(PHP_VERSION, 1);

        $this->info(
            'Comparing current PHP version ' . $currentPhpMajorVersion
            . ' to required version ' . $this->requiredPhpVersion
        );
        if (! VersionHelper::compare($currentPhpMajorVersion, $this->requiredPhpVersion, VersionHelper::EQUAL)) {
            throw PhpRequirementException::badPhpVersion($this->requiredPhpVersion, $currentPhpMajorVersion);
        }
    }

    /**
     * Check if required php extensions are loaded
     *
     * @throws PhpRequirementException
     */
    private function validatePhpExtensionsOrFail(): void
    {
        $this->info('Checking PHP extensions');
        foreach (self::EXTENSION_REQUIREMENTS as $extensionName) {
            $this->validatePhpExtensionOrFail($extensionName);
        }
    }

    /**
     * check if given php extension is loaded
     *
     * @param string $extensionName
     *
     * @throws PhpRequirementException
     */
    private function validatePhpExtensionOrFail(string $extensionName): void
    {
        $this->info('Checking PHP extension ' . $extensionName);
        if (! extension_loaded($extensionName)) {
            $this->error('PHP extension ' . $extensionName . ' is not loaded');
            throw PhpRequirementException::phpExtensionNotLoaded($extensionName);
        }
    }
}
