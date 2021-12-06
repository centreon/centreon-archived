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
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

class IconRepositoryRDB extends AbstractRepositoryDRB implements IconRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

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
     * Initialized by the dependency injector.
     *
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function getIconsWithRequestParameters(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'vi.img_id',
            'name' => 'vi.img_name',
        ]);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();

        // Pagination
        $paginationRequest = $this->sqlRequestTranslator->translatePaginationToSql();

        return $this->getIcons($searchRequest, $sortRequest, $paginationRequest);
    }

    /**
     * @inheritDoc
     */
    public function getIconsWithoutRequestParameters(): array
    {
        return $this->getIcons(null, null, null);
    }

    /**
     * Retrieve all icons according to ACL of contact
     *
     * @param string|null $searchRequest search request
     * @param string|null $sortRequest sort request
     * @param string|null $paginationRequest pagination request
     * @return Icon[]
     */
    private function getIcons(
        ?string $searchRequest = null,
        ?string $sortRequest = null,
        ?string $paginationRequest = null
    ): array {
        $request = $this->translateDbName('
            SELECT vi.*, vid.dir_name AS `img_dir`
            FROM `view_img` AS `vi`
            LEFT JOIN `:db`.`view_img_dir_relation` AS `vidr` ON vi.img_id = vidr.img_img_id
            LEFT JOIN `:db`.`view_img_dir` AS `vid` ON vid.dir_id = vidr.dir_dir_parent_id
        ');

        // Search
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY vi.img_id ASC';

        // Pagination
        $request .= !is_null($paginationRequest) ? $paginationRequest : '';

        $statement = $this->db->prepare($request);
        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        $icons = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $icon = new Icon();
            $icon
                ->setId((int) $row['img_id'])
                ->setDirectory($row['img_dir'])
                ->setName($row['img_name'])
                ->setUrl($row['img_dir'] . '/' . $row['img_path']);
            $icons[] = $icon;
        }

        return $icons;
    }
}
