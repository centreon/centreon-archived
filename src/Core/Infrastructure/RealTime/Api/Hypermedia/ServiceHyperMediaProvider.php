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
use Core\Application\RealTime\UseCase\FindService\FindServiceResponse;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderTrait;

class ServiceHypermediaProvider implements HypermediaProviderInterface
{
    use HypermediaProviderTrait;

    public const URI_CONFIGURATION = '/main.php?p=60201&o=c&service_id={serviceId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}',
                 URI_REPORTING = '/main.php?p=30702&period=yesterday&start=&end=&host_id={hostId}&item={serviceId}';

    public const ENDPOINT_SERVICE_TIMELINE = 'centreon_application_monitoring_gettimelinebyhostandservice',
                 ENDPOINT_PERFORMANCE_GRAPH = 'monitoring.metric.getServicePerformanceMetrics',
                 ENDPOINT_STATUS_GRAPH = 'monitoring.metric.getServiceStatusMetrics';

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
        return ($data instanceof FindServiceResponse);
    }

    /**
     * @inheritDoc
     */
    public function createForConfiguration(mixed $data): ?string
    {
        $configurationUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
            || $this->contact->isAdmin()
        ) {
            $configurationUri = $this->getBaseUri()
                . str_replace('{serviceId}', (string) $data->id, self::URI_CONFIGURATION);
        }
        return $configurationUri;
    }

    /**
     * @inheritDoc
     */
    public function createForReporting(mixed $data): ?string
    {
        $reportingUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_SERVICES)
            || $this->contact->isAdmin()
        ) {
            $reportingUri = str_replace('{hostId}', (string) $data->hostId, self::URI_REPORTING);
            $reportingUri = str_replace('{serviceId}', (string) $data->id, (string) $reportingUri);
            $reportingUri = $this->getBaseUri() . $reportingUri;
        }
        return $reportingUri;
    }

    /**
     * @inheritDoc
     */
    public function createForEventLog(mixed $data): ?string
    {
        $eventLogsUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
            || $this->contact->isAdmin()
        ) {
            $eventLogsUri = str_replace('{hostId}', (string) $data->hostId, self::URI_EVENT_LOGS);
            $eventLogsUri = str_replace('{serviceId}', (string) $data->id, (string) $eventLogsUri);
            $eventLogsUri = $this->getBaseUri() . $eventLogsUri;
        }
        return $eventLogsUri;
    }

    /**
     * @inheritDoc
     */
    public function createForTimelineEndpoint(mixed $data): string
    {
        return $this->router->generate(
            self::ENDPOINT_SERVICE_TIMELINE,
            [
                'serviceId' => $data->id,
                'hostId' => $data->hostId
             ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createForStatusGraphEndpoint(mixed $data): string
    {
        return $this->router->generate(
            self::ENDPOINT_STATUS_GRAPH,
            [
                'hostId' => $data->hostId,
                'serviceId' => $data->id
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createForPerformanceDataEndpoint(mixed $response): string
    {
        return $this->router->generate(
            self::ENDPOINT_PERFORMANCE_GRAPH,
            [
                'hostId' => $response->hostId,
                'serviceId' => $response->id
            ]
        );
    }
}
