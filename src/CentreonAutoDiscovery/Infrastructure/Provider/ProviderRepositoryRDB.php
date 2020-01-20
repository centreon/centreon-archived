<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
 */
declare(strict_types=1);

namespace CentreonAutoDiscovery\Infrastructure\Provider;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use CentreonAutoDiscovery\Domain\Provider\Interfaces\ProviderRepositoryInterface;
use CentreonAutoDiscovery\Domain\Provider\Provider;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

class ProviderRepositoryRDB extends AbstractRepositoryDRB implements ProviderRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

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
            ->setConcordanceStrictMode(
                RequestParameters::CONCORDANCE_MODE_STRICT
            );
    }

    /**
     * @inheritDoc
     */
    public function findProviders(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'provider.id',
            'name' => 'provider.name',
            'description' => 'provider.description',
            'plugin-pack.name' => 'pp.name',
            'plugin-pack.version' => 'pp.version',
            'plugin-pack.status' => 'pp.status',
            'category.name' => 'cat.name'
        ]);
        $request =
            'SELECT SQL_CALC_FOUND_ROWS provider.*, type.name AS provider_type,
                provider.host_template_id AS default_template_id, icon.icon_file AS icon
            FROM `:db`.mod_host_disco_provider provider
            INNER JOIN `:db`.mod_host_disco_provider_type type
                ON type.id = provider.type_id
            INNER JOIN `:db`.mod_ppm_pluginpack pp 
	            ON pp.pluginpack_id = provider.pluginpack_id
            INNER JOIN `:db`.mod_ppm_icons icon
	            ON icon.icon_id = pp.icon
            INNER JOIN `:db`.mod_ppm_categories cat
	            ON cat.id = pp.discovery_category_id';

        $request = $this->translateDbName($request);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY provider.id DESC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();
        $result = $this->db->query('SELECT FOUND_ROWS()');

        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $providers = [];
        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $providers[] = EntityCreator::createEntityByArray(
                Provider::class,
                $result
            );
        }
        return $providers;
    }
}
