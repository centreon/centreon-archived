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

class ServiceHyperMediaProvider extends HyperMediaProvider
{
    public const PROVIDER_TYPE = 'service';

    public const SERVICE_CONFIGURATION_URI = '/main.php?p=60201&o=c&service_id={serviceId}',
                 SERVICE_EVENT_LOGS_URI = '/main.php?p=20301&svc={hostId}_{serviceId}',
                 SERVICE_REPORTING_URI =
                    '/main.php?p=30702&period=yesterday&start=&end=&host_id={hostId}&item={serviceId}';

    public const SERVICE_DETAILS_ENDPOINT = 'centreon_application_monitoring_resource_detail_service',
                 SERVICE_DOWNTIME_ENDPOINT = 'monitoring.downtime.addServiceDowntime',
                 SERVICE_ACKNOWLEDGEMENT_ENDPOINT = 'centreon_application_acknowledgement_addserviceacknowledgement',
                 SERVICE_TIMELINE_ENDPOINT = 'centreon_application_monitoring_gettimelinebyhostandservice',
                 SERVICE_STATUS_GRAPH_ENDPOINT = 'monitoring.metric.getServiceStatusMetrics',
                 SERVICE_PERFORMANCE_GRAPH_ENDPOINT = 'monitoring.metric.getServicePerformanceMetrics';

    /**
     * Generate URI to configuration page
     *
     * @param integer $serviceId
     * @param Contact $contact
     * @return string|null
     */
    public function generateConfigurationUri(int $serviceId, Contact $contact): ?string
    {
        $configurationUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_SERVICES_READ)
            || $contact->isAdmin()
        ) {
            $configurationUri = parent::getBaseUri()
                . str_replace('{serviceId}', (string) $serviceId, static::SERVICE_CONFIGURATION_URI);
        }
        return $configurationUri;
    }

    /**
     * Generate URI to reporting page
     *
     * @param array<string, int> $parameters
     * @param Contact $contact
     * @return string|null
     */
    public function generateReportingUri(array $parameters, Contact $contact): ?string
    {
        $reportingUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_SERVICES)
            || $contact->isAdmin()
        ) {
            $reportingUri = str_replace('{hostId}', (string) $parameters['hostId'], static::SERVICE_REPORTING_URI);
            $reportingUri = str_replace('{serviceId}', (string) $parameters['serviceId'], (string) $reportingUri);
            $reportingUri = parent::getBaseUri() . $reportingUri;
        }
        return $reportingUri;
    }

    /**
     * Generate URI for Event Logs page filtered
     *
     * @param array<string, int> $parameters
     * @param Contact $contact
     * @return string|null
     */
    public function generateEventLogsUri(array $parameters, Contact $contact): ?string
    {
        $eventLogsUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
            || $contact->isAdmin()
        ) {
            $eventLogsUri = str_replace('{hostId}', (string) $parameters['hostId'], static::SERVICE_EVENT_LOGS_URI);
            $eventLogsUri = str_replace('{serviceId}', (string) $parameters['serviceId'], (string) $eventLogsUri);
            $eventLogsUri = parent::getBaseUri() . $eventLogsUri;
        }
        return $eventLogsUri;
    }

    /**
     * Generate the details endpoint for the resource type service
     *
     * @param array<string, int> $parameters contains hostId and serviceId
     * @return string
     */
    public function generateDetailsEndpoint(array $parameters): string
    {
        return $this->router->generate(static::SERVICE_DETAILS_ENDPOINT, $parameters);
    }

    /**
     * Generate the endpoint for downtime actions
     *
     * @param array<string, int> $parameters
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
        return $this->router->generate(static::SERVICE_DOWNTIME_ENDPOINT, array_merge($parameters, $downtimeFilter));
    }

    /**
     * Generate the endpoint for acknowledgement actions
     *
     * @param array<string, int> $parameters
     * @return string
     */
    public function generateAcknowledgementEndpoint(array $parameters): string
    {
        return $this->router->generate(
            static::SERVICE_ACKNOWLEDGEMENT_ENDPOINT,
            array_merge($parameters, ['limit' => 1])
        );
    }

    /**
     * Generate the endpoint to get the timeline
     *
     * @param array<string, int>  $parameters
     * @return string
     */
    public function generateTimelineEndpoint(array $parameters): string
    {
        return $this->router->generate(static::SERVICE_TIMELINE_ENDPOINT, $parameters);
    }

    /**
     * Generate the endpoint to get the status graph
     *
     * @param array<string, int>  $parameters
     * @return string
     */
    public function generateStatusGraphEndpoint(array $parameters): string
    {
        return $this->router->generate(static::SERVICE_STATUS_GRAPH_ENDPOINT, $parameters);
    }

    /**
     * Generate the endpoint to get the performance data graph
     *
     * @param array<string, int>  $parameters
     * @return string
     */
    public function generatePerformanceDataEndpoint(array $parameters): string
    {
        return $this->router->generate(static::SERVICE_PERFORMANCE_GRAPH_ENDPOINT, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function generateUris(array $monitoringResource, Contact $contact): array
    {
        $parameters = [
            'hostId' => $monitoringResource['parent']['id'],
            'serviceId' => $monitoringResource['id']
        ];
        return [
            'configuration' => $this->generateConfigurationUri($parameters['serviceId'], $contact),
            'logs' => $this->generateEventLogsUri($parameters, $contact),
            'reporting' => $this->generateReportingUri($parameters, $contact)
        ];
    }

    /**
     * @inheritDoc
     */
    public function generateEndpoints(array $monitoringResource): array
    {
        $parameters = [
            'hostId' => $monitoringResource['parent']['id'],
            'serviceId' => $monitoringResource['id']
        ];
        return [
            'detail' => $this->generateDetailsEndpoint($parameters),
            'downtime' => $this->generateDowntimeEndpoint($parameters),
            'acknowledgement' => $this->generateAcknowledgementEndpoint($parameters),
            'timeline' => $this->generateTimelineEndpoint($parameters),
            'status_graph' => $this->generateStatusGraphEndpoint($parameters),
            'performance_graph' => $monitoringResource['has_graph_data']
                ? $this->generatePerformanceDataEndpoint($parameters)
                : null
        ];
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return self::PROVIDER_TYPE;
    }
}
