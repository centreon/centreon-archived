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

namespace Core\Application\Configuration\Broker;

use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;
use Centreon\Domain\VersionHelper;

abstract class BrokerBBDO
{
    public const MINIMUM_BBDO_VERSION_SUPPORTED = '3.0.0',
                 BBDO_VERSION_CONFIG_KEY = 'bbdo_version';

    /**
     * @param BrokerRepositoryInterface $brokerRepository
     */
    public function __construct(protected BrokerRepositoryInterface $brokerRepository)
    {
        $this->brokerRepository = $brokerRepository;
    }
    /**
     * Checks if at least on monitoring server has BBDO protocol in version 3.0.0
     *
     * @return boolean
     */
    public function isBBDOVersionCompatible(): bool
    {
        $brokerConfigurations = $this->brokerRepository->findAllConfigurations();
        foreach ($brokerConfigurations as $brokerConfiguration) {
            if (
                VersionHelper::compare(
                    (string) $brokerConfiguration->getConfigurationValue(),
                    self::MINIMUM_BBDO_VERSION_SUPPORTED,
                    VersionHelper::GT
                )
            ) {
                return true;
            }
        }
        return false;
    }
}
