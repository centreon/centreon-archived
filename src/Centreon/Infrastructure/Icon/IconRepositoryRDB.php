<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\Icon;

use Centreon\Domain\Configuration\Icon\Interfaces\IconRepositoryInterface;
use Centreon\Domain\Configuration\Icon\Icon;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

class IconRepositoryRDB extends AbstractRepositoryDRB implements IconRepositoryInterface
{
    /**
     * IconRepositoryRDB constructor.
     *
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getIcons(): array
    {
        $icons = [];

        $request = $this->translateDbName('
            SELECT vi.*, vid.dir_name AS `img_dir`
            FROM `view_img` AS `vi`
            LEFT JOIN `:db`.`view_img_dir_relation` AS `vidr` ON vi.img_id = vidr.img_img_id
            LEFT JOIN `:db`.`view_img_dir` AS `vid` ON vid.dir_id = vidr.dir_dir_parent_id
            GROUP BY vi.img_id
        ');
        $statement = $this->db->query($request);

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $icon = new Icon();
            $icon
                ->setId((int) $row['img_id'])
                ->setDirectory($row['img_dir'])
                ->setName($row['img_name'])
                ->setUrl($row['img_dir'] . '/' . $row['img_name']);
            $icons[] = $icon;
        }

        return $icons;
    }
}
