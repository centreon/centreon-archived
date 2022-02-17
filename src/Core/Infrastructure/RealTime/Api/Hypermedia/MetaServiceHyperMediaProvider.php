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

    public const ENDPOINT_TIMELINE = 'centreon_application_monitoring_gettimelinebymetaservices',
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
     * Create configuration redirection uri for Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForConfiguration(array $parameters): ?string
    {
        $configurationUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
            || $this->contact->isAdmin()
        ) {
            return $this->getBaseUri() .
                str_replace(
                    '{metaId}',
                    (string) $parameters['metaId'],
                    static::URI_CONFIGURATION
                );
        }
        return $configurationUri;
    }

    /**
     * Create reporting redirection uri for Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForReporting(array $parameters): ?string
    {
        return null;
    }

    /**
     * Create event logs redirection uri for Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForEventLog(array $parameters): ?string
    {
        $eventLogsUri = null;
        if (
            $this->contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
            || $this->contact->isAdmin()
        ) {
            $eventLogsUri = str_replace('{hostId}', (string) $parameters['hostId'], static::URI_EVENT_LOGS);
            $eventLogsUri = str_replace('{serviceId}', (string) $parameters['serviceId'], (string) $eventLogsUri);
            return $this->getBaseUri() . $eventLogsUri;
        }
        return $eventLogsUri;
    }

    /**
     * Create Timeline endpoint URI for the Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForTimelineEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_TIMELINE, $parameters);
    }

    /**
     * Create Status graph endpoint URI for the Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForStatusGraphEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_STATUS_GRAPH, $parameters);
    }

    /**
     * Create Performance Data endpoint URI for the Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForPerformanceDataEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_PERFORMANCE_GRAPH, $parameters);
    }

    /**
     * Create Metric List endpoint URI for the Meta Service resource
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function createForMetricListEndpoint(array $parameters): string
    {
        return $this->router->generate(self::ENDPOINT_METRIC_LIST, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function createEndpoints(mixed $response): array
    {
        $parameters = ['metaId' => $response->id];
        return [
            'timeline' => $this->createForTimelineEndpoint($parameters),
            'status_graph' => $this->createForStatusGraphEndpoint($parameters),
            'metrics' => $this->createForMetricListEndpoint($parameters),
            'performance_graph' => $response->hasGraphData
                ? $this->createForPerformanceDataEndpoint($parameters)
                : null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function createInternalUris(mixed $response): array
    {
        $parameters = [
            'metaId' => $response->id,
            'hostId' => $response->hostId,
            'serviceId' => $response->serviceId,
        ];
        return [
            'configuration' => $this->createForConfiguration($parameters),
            'logs' => $this->createForEventLog($parameters),
            'reporting' => $this->createForReporting($parameters),
        ];
    }

    /**
     * @inheritDoc
     */
    public function createInternalGroupsUri(mixed $response): array
    {
        return [];
    }
}
