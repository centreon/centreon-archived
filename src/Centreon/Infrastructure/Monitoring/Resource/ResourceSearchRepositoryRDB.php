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

namespace Centreon\Infrastructure\Monitoring\Resource;

use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Monitoring\ResourceFilter;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * Database repository for the real time monitoring of services and host.
 *
 * @package Centreon\Infrastructure\Monitoring\Resource
 */
final class ResourceSearchRepositoryRDB
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    /**
     * @var array Association of service search parameters
     */
    private $serviceConcordances = [];

    /**
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT)
            ->setConcordanceErrorMode(RequestParameters::CONCORDANCE_ERRMODE_SILENT);
    }

    /**
     * Setter for serviceConcordances
     *
     * @param array $serviceConcordances
     * @return ResourceSearchRepositoryRDB
     */
    public function setServiceConcordances(array $serviceConcordances): ResourceSearchRepositoryRDB
    {
        $this->serviceConcordances = $serviceConcordances;
        return $this;
    }

    /**
     * Check if the filters are compatible to extract services
     *
     * @param ResourceFilter $filter
     * @return bool
     */
    public function shouldSearchServices(ResourceFilter $filter): bool
    {
        if (
            ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_SERVICE)) ||
            ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_SERVICE
            ))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the filters are compatible to extract hosts
     *
     * @param ResourceFilter $filter
     * @return bool
     */
    public function shouldSearchHosts(ResourceFilter $filter): bool
    {
        if (
            $this->hasServiceSearch() ||
            ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_HOST)) ||
            ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_HOST
            )) ||
            $filter->getServicegroupIds()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the filters are compatible to extract services
     *
     * @param ResourceFilter $filter
     * @return bool
     */
    public function shouldSearchMetaServices(ResourceFilter $filter): bool
    {
        if (
            $this->hasServiceSearch() ||
            ($filter->getTypes() && !$filter->hasType(ResourceFilter::TYPE_META)) ||
            ($filter->getStatuses() && !ResourceFilter::map(
                $filter->getStatuses(),
                ResourceFilter::MAP_STATUS_SERVICE
            ))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if a service filter is given in request parameters
     *
     * @return bool
     */
    private function hasServiceSearch(): bool
    {
        $search = $this->sqlRequestTranslator->getRequestParameters()->getSearch();

        if (empty($search)) {
            return false;
        }

        $operator = array_keys($search)[0];

        if ($operator === RequestParameters::AGGREGATE_OPERATOR_OR) {
            return !$this->extractSpecificSearchCriteria('/^h\./');
        }

        return $this->extractSpecificSearchCriteria('/^s\./');
    }

    /**
     * Extract request parameters
     *
     * @param string $key
     * @return bool
     */
    private function extractSpecificSearchCriteria(string $key)
    {
        $requestParameters = $this->sqlRequestTranslator->getRequestParameters();
        $search = $requestParameters->getSearch();

        $serviceConcordances = array_reduce(
            array_keys($this->serviceConcordances),
            function ($acc, $concordanceKey) use ($key) {
                if (preg_match($key, $concordanceKey)) {
                    $acc[] = $concordanceKey;
                }
                return $acc;
            },
            []
        );

        foreach ($serviceConcordances as $serviceConcordance) {
            if ($requestParameters->hasSearchParameter($serviceConcordance, $search)) {
                return true;
            }
        }

        return false;
    }
}
