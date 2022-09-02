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

namespace Core\Domain\RealTime\Model;

use Core\Domain\RealTime\Model\Status;

class HostStatus extends Status
{
    public const STATUS_NAME_UP = 'UP',
                 STATUS_NAME_DOWN = 'DOWN',
                 STATUS_NAME_UNREACHABLE = 'UNREACHABLE';

    public const STATUS_CODE_UP = 0,
                 STATUS_CODE_DOWN = 1,
                 STATUS_CODE_UNREACHABLE = 2;

    public const STATUS_ORDER_UP = parent::STATUS_ORDER_OK,
                 STATUS_ORDER_UNREACHABLE = parent::STATUS_ORDER_LOW,
                 STATUS_ORDER_DOWN = parent::STATUS_ORDER_HIGH;
}
