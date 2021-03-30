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

namespace Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Monitoring\MetaService\Exception\MetaServiceMetricException;
use Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric\FindMetaServiceMetricsResponse;
use Centreon\Domain\Monitoring\MetaService\Interfaces\MetaServiceMetric\MetaServiceMetricServiceInterface;

/**
 * This class is designed to represent a use case to find all host categories.
 *
 * @package Centreon\Domain\Monitoring\MetaService\UseCase\V21
 */
class FindMetaServiceMetrics
{
    /**
     * @var MetaServiceMetricServiceInterface
     */
    private $metaServiceMetricServiceInterface;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * FindMetaServiceMetrics constructor.
     *
     * @param MetaServiceMetricServiceInterface $metaServiceMetricServiceInterface
     * @param ContactInterface $contact
     */
    public function __construct(
        MetaServiceMetricServiceInterface $metaServiceMetricServiceInterface,
        ContactInterface $contact
    ) {
        $this->metaServiceMetricServiceInterface = $metaServiceMetricServiceInterface;
        $this->contact = $contact;
    }

    /**
     * Execute the use case for which this class was designed.
     * @param int $metaId
     * @return FindMetaServiceMetricsResponse
     * @throws MetaServiceMetricException
     */
    public function execute(int $metaId): FindMetaServiceMetricsResponse
    {
        $response = new FindMetaServiceMetricsResponse();
        $metaServiceMetrics = ($this->contact->isAdmin())
            ? $this->metaServiceMetricServiceInterface->findWithoutAcl($metaId)
            : $this->metaServiceMetricServiceInterface->findWithAcl($metaId);
        $response->setMetaServiceMetrics($metaServiceMetrics);
        return $response;
    }
}
