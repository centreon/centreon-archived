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

namespace Centreon\Domain\Monitoring\MonitoringResource\Model\Provider;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\RequestParameters\RequestParameters;

class MetaServiceHyperMediaProvider extends HyperMediaProvider
{
    public const PROVIDER_TYPE = 'metaservice';

    public const METASERVICE_CONFIGURATION_URI = '/main.php?p=60204&o=c&meta_id={metaId}',
                 METASERVICE_EVENT_LOGS_URI = '/main.php?p=20301&svc={hostId}_{serviceId}';

    public const METASERVICE_DETAILS_ENDPOINT = 'centreon_application_monitoring_resource_detail_meta_service',
                 METASERVICE_DOWNTIME_ENDPOINT = 'monitoring.downtime.addMetaServiceDowntime',
                 METASERVICE_ACKNOWLEDGEMENT_ENDPOINT =
                    'centreon_application_acknowledgement_addmetaserviceacknowledgement',
                 METASERVICE_TIMELINE_ENDPOINT = 'centreon_application_monitoring_gettimelinebymetaservices',
                 METASERVICE_STATUS_GRAPH_ENDPOINT = 'monitoring.metric.getMetaServiceStatusMetrics',
                 METASERVICE_METRIC_LIST_ENDPOINT = 'centreon_application_find_meta_service_metrics',
                 METASERVICE_PERFORMANCE_GRAPH_ENDPOINT = 'monitoring.metric.getMetaServicePerformanceMetrics';

    /**
     * Generate the URI for the configuration
     *
     * @param integer $metaId
     * @param Contact $contact
     * @return string
     */
    public function generateConfigurationUri(int $metaId, Contact $contact): string
    {
        $configurationUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
        ) {
            $configurationUri = parent::getBaseUri()
                . str_replace('{metaId}', (string) $metaId, static::METASERVICE_CONFIGURATION_URI);
        }
        return $configurationUri;
    }

    /**
     * Generate the URI to the Event Logs
     *
     * @param array<string, int> $parameters
     * @param Contact $contact
     * @return string
     */
    public function generateEventLogsUri(array $parameters, Contact $contact): string
    {
        $eventLogsUri = null;
        if ($contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)) {
            $eventLogsUri = str_replace('{hostId}', (string) $parameters['hostId'], static::METASERVICE_EVENT_LOGS_URI);
            $eventLogsUri = str_replace('{serviceId}', (string) $parameters['serviceId'], (string) $eventLogsUri);
            $eventLogsUri = parent::getBaseUri() . $eventLogsUri;
        }
        return $eventLogsUri;
    }

    /**
     * Generate the endpoint to get the details
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generateDetailsEndpoint(array $parameters): string
    {
        return $this->router->generate(static::METASERVICE_DETAILS_ENDPOINT, $parameters);
    }

    /**
     * Generate the endpoint for downtime actions
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generateDowntimeEndpoint(array $parameters): string
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
        return $this->router->generate(
            static::METASERVICE_DOWNTIME_ENDPOINT,
            array_merge($parameters, $downtimeFilter)
        );
    }

    /**
     * Generate the endpoint for acknowledgement actions
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generateAcknowledgementEndpoint(array $parameters): string
    {
        return $this->router->generate(
            static::METASERVICE_ACKNOWLEDGEMENT_ENDPOINT,
            array_merge($parameters, ['limit' => 1])
        );
    }

    /**
     * Generate endpoint to get the timeline
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generateTimelineEndpoint(array $parameters): string
    {
        return $this->router->generate(static::METASERVICE_TIMELINE_ENDPOINT, $parameters);
    }

    /**
     * Generate endpoint to get status graph
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generateStatusGraphEndpoint(array $parameters): string
    {
        return $this->router->generate(static::METASERVICE_STATUS_GRAPH_ENDPOINT, $parameters);
    }

    /**
     * Generate endpoint to list metrics
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generateMetricListEndpoint(array $parameters): string
    {
        return $this->router->generate(static::METASERVICE_METRIC_LIST_ENDPOINT, $parameters);
    }

    /**
     * Generate performance data endpoint
     *
     * @param array<string, string>  $parameters
     * @return string
     */
    public function generatePerformanceDataEndpoint(array $parameters): string
    {
        return $this->router->generate(static::METASERVICE_PERFORMANCE_GRAPH_ENDPOINT, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function generateUris(array $monitoringResource, Contact $contact): array
    {
        $parameters = [
            'metaId' => $monitoringResource['id'],
            'serviceId' => $monitoringResource['service_id'],
            'hostId' => $monitoringResource['host_id'],
        ];
        return [
            'configuration' => $this->generateConfigurationUri($parameters['metaId'], $contact),
            'logs' => $this->generateEventLogsUri($parameters, $contact)
        ];
    }

    /**
     * @inheritDoc
     */
    public function generateEndpoints(array $monitoringResource): array
    {
        $parameters = [
            'metaId' => $monitoringResource['id'],
        ];
        return [
            'detail' => $this->generateDetailsEndpoint($parameters),
            'downtime' => $this->generateDowntimeEndpoint($parameters),
            'acknowledgement' => $this->generateAcknowledgementEndpoint($parameters),
            'timeline' => $this->generateTimelineEndpoint($parameters),
            'status_graph' => $this->generateStatusGraphEndpoint($parameters),
            'performance_graph' => $monitoringResource['has_graph_data']
                ? $this->generatePerformanceDataEndpoint($parameters)
                : null,
            'metrics' => $this->generateMetricListEndpoint($parameters)
        ];
    }

    public function getType(): string
    {
        return self::PROVIDER_TYPE;
    }
}
