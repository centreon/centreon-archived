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

namespace Core\Infrastructure\RealTime\Hypermedia;

use Centreon\Domain\Contact\Contact;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Application\RealTime\UseCase\FindMetaService\FindMetaServiceResponse;

class MetaServiceHypermediaProvider extends AbstractHypermediaProvider implements HypermediaProviderInterface
{
    use HttpUrlTrait;

    public const URI_CONFIGURATION = '/main.php?p=60204&o=c&meta_id={metaId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}';

    public const ENDPOINT_TIMELINE = 'centreon_application_monitoring_gettimelinebymetaservices',
                 ENDPOINT_PERFORMANCE_GRAPH = 'monitoring.metric.getMetaServicePerformanceMetrics',
                 ENDPOINT_STATUS_GRAPH = 'monitoring.metric.getMetaServiceStatusMetrics',
                 ENDPOINT_METRIC_LIST = 'centreon_application_find_meta_service_metrics',
                 ENDPOINT_NOTIFICATION_POLICY = 'configuration.metaservice.notification-policy';

    /**
     * @param ContactInterface $contact
     * @param UriGenerator $uriGenerator
     */
    public function __construct(
        private ContactInterface $contact,
        private UriGenerator $uriGenerator
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
        $roles = [
            Contact::ROLE_CONFIGURATION_SERVICES_WRITE,
            Contact::ROLE_CONFIGURATION_SERVICES_READ
        ];

        if (! $this->canContactAccessPages($this->contact, $roles)) {
            return null;
        }

        return $this->uriGenerator->generateUri(
            self::URI_CONFIGURATION,
            ['{metaId}' => $parameters['metaId']]
        );
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
        if (! $this->canContactAccessPages($this->contact, [Contact::ROLE_MONITORING_EVENT_LOGS])) {
            return null;
        }

        return $this->uriGenerator->generateUri(
            self::URI_EVENT_LOGS,
            [
                '{hostId}' => $parameters['hostId'],
                '{serviceId}' => $parameters['serviceId']
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function createEndpoints(mixed $response): array
    {
        $parameters = ['metaId' => $response->id];
        return [
            'timeline' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_TIMELINE, $parameters),
            'status_graph' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_STATUS_GRAPH, $parameters),
            'metrics' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_METRIC_LIST, $parameters),
            'performance_graph' => $response->hasGraphData
                ? $this->uriGenerator->generateEndpoint(self::ENDPOINT_PERFORMANCE_GRAPH, $parameters)
                : null,
            'notification_policy' => $this->uriGenerator->generateEndpoint(
                self::ENDPOINT_NOTIFICATION_POLICY,
                $response
            ),
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
    public function convertGroupsForPresenter(array $groups): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function convertCategoriesForPresenter(array $categories): array
    {
        return [];
    }
}
