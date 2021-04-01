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

namespace Centreon\Domain\Monitoring\MetaService\Interfaces\MetaServiceMetric;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetric;

/**
 * This interface gathers all the reading operations on the meta service metric repository.
 *
 * @package Centreon\Domain\Monitoring\MetaService\Interfaces\MetaServiceMetric
 */
interface MetaServiceMetricRepositoryInterface
{
    /**
     * Find Meta Service Metrics for a non-admin user.
     *
     * @param integer $metaId
     * @param ContactInterface $contact
     * @return MetaServiceMetric[]|null
     */
    public function findByMetaIdAndContact(int $metaId, ContactInterface $contact): ?array;

    /**
     * Find Meta Service Metrics for a non-admin user using SQL regexp service search and metric.
     *
     * @param string $metricName
     * @param string $regexpString
     * @param ContactInterface $contact
     * @return MetaServiceMetric[]|null
     */
    public function findByContactAndSqlRegexp(
        string $metricName,
        string $regexpString,
        ContactInterface $contact
    ): ?array;

    /**
     * Find Meta Service Metrics for an admin user.
     *
     * @param integer $metaId
     * @return MetaServiceMetric[]|null
     */
    public function findByMetaId(int $metaId): ?array;

    /**
     * Find Meta Service Metrics for an admin user using SQL regexp service search and metric.
     * @param string $metricName
     * @param string $regexpString
     * @return MetaServiceMetric[]|null
     */
    public function findBySqlRegexp(string $metricName, string $regexpString): ?array;
}
