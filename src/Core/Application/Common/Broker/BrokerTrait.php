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

namespace Core\Application\Common\Broker;

use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;

trait BrokerTrait
{
    /**
     * Checks if at least on monitoring server has BBDO protocol in version 3.0.0
     *
     * @return boolean
     */
    public function isBBDOVersionCompatible(): bool
    {
        $brokerConfigurations = $this->brokerRepository->findAllByParameterName(BrokerRepositoryInterface::BBDO_VERSION_CONFIG_KEY);
        foreach ($brokerConfigurations as $brokerConfiguration) {
            if (
                version_compare(
                    $brokerConfiguration->getConfigurationValue(),
                    BrokerRepositoryInterface::MINIMUM_BBDO_VERSION_SUPPORTED
                ) > 0
            ) {
                return true;
            }
        }
        return false;
    }
}
