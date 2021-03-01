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

use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is designed to provide a way to create the HostGroup entity from the database.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository\Model
 */
class HostGroupFactoryRdb
{
    /**
     * Create a HostGroup entity from database data.
     *
     * @param array<string, mixed> $data
     * @return HostGroup
     * @throws \Assert\AssertionFailedException
     */
    public static function create(array $data): HostGroup
    {
        $hostGroup = new HostGroup($data['hg_name']);
        if (isset($data['hg_icon_image'])) {
            $hostGroup->setIcon(
                (new Image())
                    ->setId((int) $data['icon_id'])
                    ->setName($data['icon_name'])
                    ->setComment($data['icon_comment'])
                    ->setPath(str_replace('//', '/', ($data['icon_path'])))
            );
        }

        if (isset($data['hg_map_icon_image'])) {
            $hostGroup->setIconMap(
                (new Image())
                    ->setId((int) $data['imap_id'])
                    ->setName($data['imap_name'])
                    ->setComment($data['imap_comment'])
                    ->setPath(str_replace('//', '/', $data['imap_path']))
            );
        }

        $hostGroup
            ->setId((int) $data['hg_id'])
            ->setAlias($data['hg_alias'])
            ->setNotes($data['hg_notes'])
            ->setNotesUrl($data['hg_notes_url'])
            ->setActionUrl($data['hg_action_url'])
            ->setRrd(self::getIntOrNull($data['hg_rrd_retention']))
            ->setGeoCoords($data['geo_coords'])
            ->setComment($data['hg_comment'])
            ->setActivated((bool) $data['hg_activate']);
        return $hostGroup;
    }

    /**
     * @param int|string|null $property
     * @return int|null
     */
    private static function getIntOrNull($property): ?int
    {
        return ($property !== null) ? (int) $property : null;
    }
}
