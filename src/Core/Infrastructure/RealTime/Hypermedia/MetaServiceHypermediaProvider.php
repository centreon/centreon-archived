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
use Centreon\Domain\RequestParameters\RequestParameters;
use Core\Domain\RealTime\Model\ResourceTypes\MetaServiceResourceType;

class MetaServiceHypermediaProvider extends AbstractHypermediaProvider implements HypermediaProviderInterface
{
    use HttpUrlTrait;

    public const ENDPOINT_TIMELINE = 'centreon_application_monitoring_gettimelinebymetaservices',
                 ENDPOINT_PERFORMANCE_GRAPH = 'monitoring.metric.getMetaServicePerformanceMetrics',
                 ENDPOINT_STATUS_GRAPH = 'monitoring.metric.getMetaServiceStatusMetrics',
                 ENDPOINT_METRIC_LIST = 'centreon_application_find_meta_service_metrics',
                 ENDPOINT_DETAILS = 'centreon_application_monitoring_resource_details_meta_service',
                 ENDPOINT_DOWNTIME = 'monitoring.downtime.addMetaServiceDowntime',
                 ENDPOINT_ACKNOWLEDGEMENT = 'centreon_application_acknowledgement_addmetaserviceacknowledgement',
                 ENDPOINT_NOTIFICATION_POLICY = 'configuration.metaservice.notification-policy',
                 URI_CONFIGURATION = '/main.php?p=60204&o=c&meta_id={metaId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}';

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
    public function isValidFor(string $resourceType): bool
    {
        return $resourceType === MetaServiceResourceType::TYPE_NAME;
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
     * @param array<string, int> $parameters
     * @return string
     */
    private function generateDowntimeEndpoint(array $parameters): string
    {
        $downtimeFilter = [
            'search' => json_encode([
                RequestParameters::AGGREGATE_OPERATOR_AND => [
                    [
                        'start_time' => [
                            RequestParameters::OPERATOR_LESS_THAN => time(),
                        ],
                        'end_time' => [
                            RequestParameters::OPERATOR_GREATER_THAN => time(),
                        ],
                        [
                            RequestParameters::AGGREGATE_OPERATOR_OR => [
                                'is_cancelled' => [
                                    RequestParameters::OPERATOR_NOT_EQUAL => 1,
                                ],
                                'deletion_time' => [
                                    RequestParameters::OPERATOR_GREATER_THAN => time(),
                                ],
                            ],
                        ]
                    ]
                ]
            ])
        ];

        return $this->uriGenerator->generateEndpoint(
            self::ENDPOINT_DOWNTIME,
            array_merge($parameters, $downtimeFilter)
        );
    }

    /**
     * @param array<string, int> $parameters
     * @return string
     */
    private function generateAcknowledgementEndpoint(array $parameters): string
    {
        $acknowledgementFilter = ['limit' => 1];

        return $this->uriGenerator->generateEndpoint(
            self::ENDPOINT_ACKNOWLEDGEMENT,
            array_merge($parameters, $acknowledgementFilter)
        );
    }

    /**
     * @inheritDoc
     */
    public function createEndpoints(array $parameters): array
    {
        $parametersId = [
            'metaId' => $parameters['internalId']
        ];

        return [
            'details' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_DETAILS, $parametersId),
            'acknowledgement' => $this->generateAcknowledgementEndpoint($parametersId),
            'downtime' => $this->generateDowntimeEndpoint($parametersId),
            'timeline' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_TIMELINE, $parametersId),
            'status_graph' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_STATUS_GRAPH, $parametersId),
            'metrics' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_METRIC_LIST, $parametersId),
            'performance_graph' => $parameters['hasGraphData']
                ? $this->uriGenerator->generateEndpoint(self::ENDPOINT_PERFORMANCE_GRAPH, $parametersId)
                : null,
            'notification_policy' => $this->uriGenerator->generateEndpoint(
                self::ENDPOINT_NOTIFICATION_POLICY,
                $parametersId
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function createInternalUris(array $parameters): array
    {
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
