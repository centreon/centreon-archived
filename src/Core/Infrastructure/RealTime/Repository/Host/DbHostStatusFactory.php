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

namespace Core\Infrastructure\RealTime\Repository\Host;

use Core\Domain\RealTime\Model\Status;

class DbHostStatusFactory
{
    /**
     * @param array<string, mixed> $data
     * @return Status
     */
    public static function createFromRecord(array $data): Status
    {
        $statusType = (int) $data['state_type'];

        switch ((int) $data['status_code']) {
            case Status::HOST_STATUS_CODE_UP:
                return (new Status(
                    Status::HOST_STATUS_NAME_UP,
                    Status::HOST_STATUS_CODE_UP,
                    $statusType
                ))
                ->setOrder(Status::STATUS_ORDER_OK);
            case Status::HOST_STATUS_CODE_DOWN:
                return (new Status(
                    Status::HOST_STATUS_NAME_DOWN,
                    Status::HOST_STATUS_CODE_DOWN,
                    $statusType
                ))
                ->setOrder(Status::STATUS_ORDER_HIGH);
            case Status::HOST_STATUS_CODE_UNREACHABLE:
                return (new Status(
                    Status::HOST_STATUS_NAME_UNREACHABLE,
                    Status::HOST_STATUS_CODE_UNREACHABLE,
                    $statusType
                ))
                ->setOrder(Status::STATUS_ORDER_LOW);
            case Status::STATUS_CODE_PENDING:
                return (new Status(
                    Status::STATUS_NAME_PENDING,
                    Status::STATUS_CODE_PENDING,
                    $statusType
                ))
                ->setOrder(Status::STATUS_ORDER_PENDING);
        }
    }
}
