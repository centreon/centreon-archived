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

class HostHyperMediaProvider extends HyperMediaProvider
{
    public const PROVIDER_TYPE = 'host';

    public const HOST_CONFIGURATION_URI = '/main.php?p=60101&o=c&host_id={hostId}',
                 HOST_EVENT_LOGS_URI = '/main.php?p=20301&h={hostId}',
                 HOST_REPORTING_URI = '/main.php?p=307&host={hostId}';

    public const HOST_DETAILS_ENDPOINT = 'centreon_application_monitoring_resource_detail_host',
                 HOST_DOWNTIME_ENDPOINT = 'monitoring.downtime.addHostDowntime',
                 HOST_ACKNOWLEDGEMENT_ENDPOINT = 'centreon_application_acknowledgement_addhostacknowledgement',
                 HOST_TIMELINE_ENDPOINT = 'centreon_application_monitoring_gettimelinebyhost';

    public function generateDetailsEndpoint(int $hostId): string
    {
        return $this->router->generate(static::HOST_DETAILS_ENDPOINT, ['hostId' => $hostId]);
    }

    /**
     * Generate the downtime endpoint for the host
     *
     * @param integer $hostId
     * @return string
     */
    public function generateDowntimeEndpoint(int $hostId): string
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
            static::HOST_DOWNTIME_ENDPOINT,
            array_merge(['hostId' => $hostId], $downtimeFilter)
        );
    }

    /**
     * Generate the acknowledgement endpoint for the host
     *
     * @param integer $hostId
     * @return string
     */
    public function generateAcknowledgementEndpoint(int $hostId): string
    {
        $acknowledgementFilter = ['limit' => 1];
        return $this->router->generate(
            static::HOST_DOWNTIME_ENDPOINT,
            array_merge(['hostId' => $hostId], $acknowledgementFilter)
        );
    }

    /**
     * Generate the timeline endpoint for the host
     *
     * @param integer $hostId
     * @return string
     */
    public function generateTimelineEndpoint(int $hostId): string
    {
        return $this->router->generate(static::HOST_TIMELINE_ENDPOINT, ['hostId' => $hostId]);
    }

    /**
     * Generate the configuration uri on host regarding ACLs
     *
     * @param integer $hostId
     * @param Contact $contact
     * @return string|null
     */
    public function generateConfigurationUri(int $hostId, Contact $contact): ?string
    {
        $configurationUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_WRITE)
            || $contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_READ)
        ) {
            $configurationUri = parent::getBaseUri()
                . str_replace('{hostId}', (string) $hostId, static::HOST_CONFIGURATION_URI);
        }
        return $configurationUri;
    }

    /**
     * Generate the reporting uri on host regarding ACLs
     *
     * @param integer $hostId
     * @param Contact $contact
     * @return string|null
     */
    public function generateReportingUri(int $hostId, Contact $contact): ?string
    {
        $reportingUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_HOSTS)
            || $contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_HOSTS)
        ) {
            $reportingUri = parent::getBaseUri()
                . str_replace('{hostId}', (string) $hostId, static::HOST_REPORTING_URI);
        }
        return $reportingUri;
    }

    /**
     * Generate the Log events uri on host regarding ACLs
     *
     * @param integer $hostId
     * @param Contact $contact
     * @return string|null
     */
    public function generateEventLogsUri(int $hostId, Contact $contact): ?string
    {
        $logsUri = null;
        if (
            $contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
            || $contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
        ) {
            $logsUri = parent::getBaseUri() . str_replace('{hostId}', (string) $hostId, static::HOST_EVENT_LOGS_URI);
        }
        return $logsUri;
    }

    /**
     * @inheritDoc
     */
    public function generateUris(array $monitoringResource, Contact $contact): array
    {
        // Assertion::notEmpty($monitoringResource, 'id');
        $hostId = $monitoringResource['id'];
        return [
            'configuration' => $this->generateConfigurationUri($hostId, $contact),
            'logs' => $this->generateEventLogsUri($hostId, $contact),
            'reporting' => $this->generateReportingUri($hostId, $contact),
        ];
    }

    /**
     * @inheritDoc
     */
    public function generateEndpoints(array $monitoringResource): array
    {
        $hostId = $monitoringResource['id'];
        return [
            'details' => $this->generateDetailsEndpoint($hostId),
            'downtime' => $this->generateDowntimeEndpoint($hostId),
            'acknowledgement' => $this->generateAcknowledgementEndpoint($hostId),
            'timeline' => $this->generateTimelineEndpoint($hostId)
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
