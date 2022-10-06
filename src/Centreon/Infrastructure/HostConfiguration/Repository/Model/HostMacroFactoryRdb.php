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

namespace Centreon\Infrastructure\HostConfiguration\Repository\Model;

use Centreon\Domain\HostConfiguration\HostMacro;

/**
 * This class is designed to provide a way to create the HostMacro entity from the database.
 */
class HostMacroFactoryRdb
{
    /**
     * Create a HostMacro entity from database data.
     *
     * @param array<string, mixed> $data
     * @return HostMacro
     */
    public static function create(array $data): HostMacro
    {
        return (new HostMacro())
            ->setId((int) $data['host_macro_id'])
            ->setName($data['host_macro_name'])
            ->setValue($data['host_macro_value'])
            ->setDescription($data['description'])
            ->setOrder((int) $data['macro_order'])
            ->setHostId((int) $data['host_host_id'])
            ->setPassword($data['is_password'] === 1);
    }
}
