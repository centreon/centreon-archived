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

namespace Centreon\Infrastructure\Monitoring\Resource\Provider;

use Centreon\Infrastructure\Monitoring\Resource\Provider\ProviderInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\RequestParameters\RequestParameters;

abstract class Provider implements ProviderInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    protected $sqlRequestTranslator;

    /**
     * @var array Association of host search parameters
     */
    protected $hostConcordances = [
        'h.name' => 'h.name',
        'h.alias' => 'h.alias',
        'h.address' => 'h.address',
        'h.fqdn' => 'h.address',
    ];

    /**
     * @var array Association of service search parameters
     */
    protected $serviceConcordances = [
        'h.name' => 'sh.name',
        'h.alias' => 'sh.alias',
        'h.address' => 'sh.address',
        'h.fqdn' => 'sh.address',
        's.description' => 's.description',
    ];

    /**
     * @inheritDoc
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT)
            ->setConcordanceErrorMode(RequestParameters::CONCORDANCE_ERRMODE_SILENT);
    }

    /**
     * Check if a service filter is given in request parameters
     *
     * @return bool
     */
    protected function hasOnlyHostSearch(): bool
    {
        return $this->hasOnlyConcordanceSearch($this->hostConcordances);
    }

    /**
     * Check if a service filter is given in request parameters
     *
     * @return bool
     */
    protected function hasOnlyServiceSearch(): bool
    {
        return $this->hasOnlyConcordanceSearch($this->serviceConcordances);
    }

    private function hasOnlyConcordanceSearch(array $concordances): bool
    {
        $search = $this->sqlRequestTranslator->getRequestParameters()->getSearch();
        $searchNames = $this->sqlRequestTranslator->getRequestParameters()->extractSearchNames();

        if (empty($searchNames)) {
            return false;
        }

        $concordanceMatches = [];
        foreach ($searchNames as $searchName) {
            if (in_array($searchName, array_keys($concordances))) {
                $concordanceMatches[] = $searchName;
            }
        }

        $operator = array_keys($search)[0];
        if ($operator === RequestParameters::AGGREGATE_OPERATOR_OR) {
            return count($searchNames) === count($concordanceMatches);
        }

        return !empty($searchNames);
    }
}