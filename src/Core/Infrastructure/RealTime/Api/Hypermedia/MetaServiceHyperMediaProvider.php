<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastructure\RealTime\Api\Hypermedia;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaServiceResponse;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderTrait;

class MetaServiceHypermediaProvider implements HypermediaProviderInterface
{
    use HypermediaProviderTrait;

    public const URI_CONFIGURATION = '/main.php?p=60204&o=c&meta_id={metaId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}';

    public const ENDPOINT_SERVICE_TIMELINE = 'centreon_application_monitoring_gettimelinebymetaservices',
                 ENDPOINT_PERFORMANCE_GRAPH = 'monitoring.metric.getMetaServicePerformanceMetrics',
                 ENDPOINT_STATUS_GRAPH = 'monitoring.metric.getMetaServiceStatusMetrics',
                 ENDPOINT_METRIC_LIST = 'centreon_application_find_meta_service_metrics';

    /**
     * @param ContactInterface $contact
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        private ContactInterface $contact,
        protected UrlGeneratorInterface $router
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isValidFor(mixed $data): bool
    {
        return ($data instanceof FindMetaServiceResponse);
    }

    /**
     * @inheritDoc
     */
    public function createForConfiguration(mixed $data): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForReporting(mixed $data): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForEventLog(mixed $data): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForTimelineEndpoint(mixed $data): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function createForStatusGraphEndpoint(mixed $data): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function createForPerformanceDataEndpoint(mixed $response): string
    {
        return '';
    }

    public function createForMetricListEndpoint(mixed $response): string
    {
        return '';
    }
}
