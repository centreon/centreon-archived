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

namespace Core\Domain\RealTime\Model;

class ServiceStatus extends Status
{
    public const SERVICE_STATUS_NAME_OK = 'OK';
    public const SERVICE_STATUS_NAME_WARNING = 'WARNING';
    public const SERVICE_STATUS_NAME_CRITICAL = 'CRITICAL';
    public const SERVICE_STATUS_NAME_UNKNOWN = 'UNKNOWN';
    public const SERVICE_STATUS_CODE_OK = 0;
    public const SERVICE_STATUS_CODE_WARNING = 1;
    public const SERVICE_STATUS_CODE_CRITICAL = 2;
    public const SERVICE_STATUS_CODE_UNKNOWN = 3;

    /**
     * @param string $name
     * @param int $code
     * @param int $type
     */
    public function __construct(
        private string $name,
        private int $code,
        private int $type
    ) {
        parent::__construct($name, $code, $type);
    }
}
