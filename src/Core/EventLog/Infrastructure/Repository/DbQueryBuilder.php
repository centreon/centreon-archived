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

namespace Core\EventLog\Infrastructure\Repository;

class DbQueryBuilder
{
    private const SELECTED_TABLE_COLS = [
        'logs.ctime',
        'logs.host_id',
        'logs.host_name',
        'logs.service_id',
        'logs.service_description',
        'logs.msg_type',
        'logs.notification_cmd',
        'logs.notification_contact',
        'logs.output',
        'logs.retry',
        'logs.status',
        'logs.type',
        'logs.instance_name'
    ];

}