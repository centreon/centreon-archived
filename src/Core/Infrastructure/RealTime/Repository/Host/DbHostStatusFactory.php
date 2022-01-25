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

namespace Core\Infrastructure\RealTime\Repository\Host;

use Core\Domain\RealTime\Model\HostStatus;

class DbHostStatusFactory
{
    /**
     * @param array<string, mixed> $data
     * @return HostStatus
     */
    public static function createFromRecord(array $data): HostStatus
    {
        $statusType = (int) $data['state_type'];

        switch ((int) $data['status_code']) {
            case HostStatus::STATUS_CODE_UP:
                return (new HostStatus(
                    HostStatus::STATUS_NAME_UP,
                    HostStatus::STATUS_CODE_UP,
                    $statusType
                ))
                ->setOrder(HostStatus::STATUS_ORDER_UP);
            case HostStatus::STATUS_CODE_DOWN:
                return (new HostStatus(
                    HostStatus::STATUS_NAME_DOWN,
                    HostStatus::STATUS_CODE_DOWN,
                    $statusType
                ))
                ->setOrder(HostStatus::STATUS_ORDER_DOWN);
            case HostStatus::STATUS_CODE_UNREACHABLE:
                return (new HostStatus(
                    HostStatus::STATUS_NAME_UNREACHABLE,
                    HostStatus::STATUS_CODE_UNREACHABLE,
                    $statusType
                ))
                ->setOrder(HostStatus::STATUS_ORDER_UNREACHABLE);
            default:
                return (new HostStatus(
                    HostStatus::STATUS_NAME_PENDING,
                    HostStatus::STATUS_CODE_PENDING,
                    $statusType
                ))
                ->setOrder(HostStatus::STATUS_ORDER_PENDING);
        }
    }
}
