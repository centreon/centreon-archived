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

namespace Centreon\Infrastructure\HostConfiguration\Repository\Model;

use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is designed to provide a way to create the HostSeverity entity from the database.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository\Model
 */
class HostSeverityFactoryRdb
{
    /**
     * Create a HostSeverity entity from database data.
     *
     * @param array<string, mixed> $data
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public static function create(array $data): HostSeverity
    {
        $icon = (new Image())
            ->setId((int)$data['img_id'])
            ->setName($data['img_name'])
            ->setComment($data['img_comment'])
            ->setPath(str_replace('//', '/', ($data['img_path'])));
        $hostSeverity = (new HostSeverity($data['hc_name'], $data['hc_alias'], (int)$data['level'], $icon))
            ->setId((int)$data['hc_id'])
            ->setActivated($data['hc_activate'] === '1');
        if ($data['hc_comment'] !== null) {
            $hostSeverity->setComments($data['hc_comment']);
        }
        return $hostSeverity;
    }
}
