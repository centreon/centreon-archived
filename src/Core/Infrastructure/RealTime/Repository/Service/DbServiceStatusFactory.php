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

namespace Core\Infrastructure\RealTime\Repository\Service;

use Core\Domain\RealTime\Model\ServiceStatus;

class DbServiceStatusFactory
{
    /**
     * @param array<string, mixed> $data
     * @return ServiceStatus
     */
    public static function createFromRecord(array $data): ServiceStatus
    {
        $statusType = (int) $data['state_type'];
        return match ((int) $data['status_code']) {
            ServiceStatus::STATUS_CODE_OK => (new ServiceStatus(
                ServiceStatus::STATUS_NAME_OK,
                ServiceStatus::STATUS_CODE_OK,
                $statusType
            ))
            ->setOrder(ServiceStatus::STATUS_ORDER_OK),
            ServiceStatus::STATUS_CODE_WARNING => (new ServiceStatus(
                ServiceStatus::STATUS_NAME_WARNING,
                ServiceStatus::STATUS_CODE_WARNING,
                $statusType
            ))
            ->setOrder(ServiceStatus::STATUS_ORDER_WARNING),
            ServiceStatus::STATUS_CODE_CRITICAL => (new ServiceStatus(
                ServiceStatus::STATUS_NAME_CRITICAL,
                ServiceStatus::STATUS_CODE_CRITICAL,
                $statusType
            ))
            ->setOrder(ServiceStatus::STATUS_ORDER_CRITICAL),
            ServiceStatus::STATUS_CODE_UNKNOWN => (new ServiceStatus(
                ServiceStatus::STATUS_NAME_UNKNOWN,
                ServiceStatus::STATUS_CODE_UNKNOWN,
                $statusType
            ))
            ->setOrder(ServiceStatus::STATUS_ORDER_UNKNOWN),
            ServiceStatus::STATUS_CODE_PENDING => (new ServiceStatus(
                ServiceStatus::STATUS_NAME_PENDING,
                ServiceStatus::STATUS_CODE_PENDING,
                $statusType
            ))
            ->setOrder(ServiceStatus::STATUS_ORDER_PENDING),
            default => (new ServiceStatus(
                ServiceStatus::STATUS_NAME_PENDING,
                ServiceStatus::STATUS_CODE_PENDING,
                $statusType
            ))
            ->setOrder(ServiceStatus::STATUS_ORDER_PENDING)
        };
    }
}
