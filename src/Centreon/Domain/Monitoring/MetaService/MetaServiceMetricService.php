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

namespace Centreon\Domain\Monitoring\MetaService;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration;
use Centreon\Domain\Monitoring\MetaService\Exception\MetaServiceMetricException;
use Centreon\Domain\MetaServiceConfiguration\Interfaces\MetaServiceConfigurationServiceInterface;
use Centreon\Domain\Monitoring\MetaService\Interfaces\MetaServiceMetric\MetaServiceMetricServiceInterface;
use Centreon\Domain\Monitoring\MetaService\Interfaces\MetaServiceMetric\MetaServiceMetricRepositoryInterface;

/**
 * This class is designed to manage Meta Service Metrics.
 *
 * @package Centreon\Domain\Monitoring\MetaService
 */
class MetaServiceMetricService implements MetaServiceMetricServiceInterface
{
    /**
     * @var MetaServiceConfigurationServiceInterface
     */
    private $metaServiceConfigurationService;
    /**
     * @var MetaServiceMetricRepositoryInterface
     */
    private $repository;

    /**
     * @var ContactInterface
     */
    private $contact;

    /**
     * @param MetaServiceMetricRepositoryInterface $repository
     * @param MetaServiceConfigurationServiceInterface $metaServiceConfigurationService
     * @param ContactInterface $contact
     */
    public function __construct(
        MetaServiceMetricRepositoryInterface $repository,
        MetaServiceConfigurationServiceInterface $metaServiceConfigurationService,
        ContactInterface $contact
    ) {
        $this->contact = $contact;
        $this->repository = $repository;
        $this->metaServiceConfigurationService = $metaServiceConfigurationService;
    }

    /**
     * @inheritDoc
     */
    public function findWithAcl(int $metaId): ?array
    {
        /**
         * Find the Meta Service configuration and Metric selection mode
         */
        $metaServiceConfiguration = $this->metaServiceConfigurationService->findWithAcl($metaId);
        if (is_null($metaServiceConfiguration)) {
            throw MetaServiceMetricException::findMetaServiceException($metaId);
        }
        $metaServiceMetricSelectionMode = $metaServiceConfiguration->getMetaSelectMode();
        if ($metaServiceMetricSelectionMode === MetaServiceConfiguration::META_SELECT_MODE_LIST) {
            try {
                return $this->repository->findByMetaIdAndContact($metaId, $this->contact);
            } catch (\Throwable $ex) {
                throw MetaServiceMetricException::findMetaServiceMetricsException($ex, $metaId);
            }
        } elseif ($metaServiceMetricSelectionMode === MetaServiceConfiguration::META_SELECT_MODE_SQL_REGEXP) {
            try {
                return $this->repository->findByContactAndSqlRegexp(
                    $metaServiceConfiguration->getMetric(),
                    $metaServiceConfiguration->getRegexpString(),
                    $this->contact
                );
            } catch (\Throwable $ex) {
                throw MetaServiceMetricException::findMetaServiceMetricsException($ex, $metaId);
            }
        } else {
            throw MetaServiceMetricException::unknownMetaMetricSelectionMode($metaId);
        }
    }

    /**
     * @inheritDoc
     */
    public function findWithoutAcl(int $metaId): ?array
    {
        /**
         * Find the Meta Service configuration
         */
        $metaServiceConfiguration = $this->metaServiceConfigurationService->findWithoutAcl($metaId);
        if (is_null($metaServiceConfiguration)) {
            throw MetaServiceMetricException::findMetaServiceException($metaId);
        }
        $metaServiceMetricSelectionMode = $metaServiceConfiguration->getMetaSelectMode();
        if ($metaServiceMetricSelectionMode === MetaServiceConfiguration::META_SELECT_MODE_LIST) {
            try {
                return $this->repository->findByMetaId($metaId);
            } catch (\Throwable $ex) {
                throw MetaServiceMetricException::findMetaServiceMetricsException($ex, $metaId);
            }
        } elseif ($metaServiceMetricSelectionMode === MetaServiceConfiguration::META_SELECT_MODE_SQL_REGEXP) {
            try {
                return $this->repository->findBySqlRegexp(
                    $metaServiceConfiguration->getMetric(),
                    $metaServiceConfiguration->getRegexpString()
                );
            } catch (\Throwable $ex) {
                throw MetaServiceMetricException::findMetaServiceMetricsException($ex, $metaId);
            }
        } else {
            throw MetaServiceMetricException::unknownMetaMetricSelectionMode($metaId);
        }
    }
}
