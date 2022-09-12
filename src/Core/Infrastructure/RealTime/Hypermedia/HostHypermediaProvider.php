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
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Core\Domain\RealTime\Model\ResourceTypes\HostResourceType;
use Core\Application\RealTime\UseCase\FindHost\FindHostResponse;

class HostHypermediaProvider extends AbstractHypermediaProvider implements HypermediaProviderInterface
{
    public const URI_CONFIGURATION = '/main.php?p=60101&o=c&host_id={hostId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&h={hostId}',
                 URI_REPORTING = '/main.php?p=307&host={hostId}',
                 URI_HOSTGROUP_CONFIGURATION = '/main.php?p=60102&o=c&hg_id={hostgroupId}',
                 URI_HOST_CATEGORY_CONFIGURATION = '/main.php?p=60104&o=c&hc_id={hostCategoryId}',
                 ENDPOINT_HOST_ACKNOWLEDGEMENT = 'centreon_application_acknowledgement_addhostacknowledgement',
                 ENDPOINT_HOST_DETAILS = 'centreon_application_monitoring_resource_details_host',
                 ENDPOINT_HOST_DOWNTIME = 'monitoring.downtime.addHostDowntime',
                 ENDPOINT_HOST_NOTIFICATION_POLICY = 'configuration.host.notification-policy',
                 ENDPOINT_HOST_TIMELINE = 'centreon_application_monitoring_gettimelinebyhost',
                 ENDPOINT_HOST_TIMELINE_DOWNLOAD = 'centreon_application_monitoring_download_timeline_by_host';

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
        return $resourceType === HostResourceType::TYPE_NAME;
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
            self::ENDPOINT_HOST_DOWNTIME,
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
            self::ENDPOINT_HOST_ACKNOWLEDGEMENT,
            array_merge($parameters, $acknowledgementFilter)
        );
    }

    /**
     * @inheritDoc
     */
    public function createEndpoints(array $parameters): array
    {
        $parametersIds = [
            'hostId' => $parameters['hostId']
        ];

        return [
            'timeline' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_HOST_TIMELINE, $parametersIds),
            'notification_policy' => $this->uriGenerator->generateEndpoint(
                self::ENDPOINT_HOST_NOTIFICATION_POLICY,
                $parametersIds
            ),
            'timeline_download' => $this->uriGenerator->generateEndpoint(
                self::ENDPOINT_HOST_TIMELINE_DOWNLOAD,
                $parametersIds
            ),
            'details' => $this->uriGenerator->generateEndpoint(self::ENDPOINT_HOST_DETAILS, $parametersIds),
            'downtime' => $this->generateDowntimeEndpoint($parametersIds),
            'acknowledgement' => $this->generateAcknowledgementEndpoint($parametersIds)
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
     * Create configuration redirection uri
     *
     * @param array<string, mixed> $parameters
     * @return string|null
     */
    public function createForConfiguration(array $parameters): ?string
    {
        $roles = [
            Contact::ROLE_CONFIGURATION_HOSTS_WRITE,
            Contact::ROLE_CONFIGURATION_HOSTS_READ
        ];

        if (! $this->canContactAccessPages($this->contact, $roles)) {
            return null;
        }

        return $this->uriGenerator->generateUri(
            self::URI_CONFIGURATION,
            ['{hostId}' => $parameters['hostId']]
        );
    }

    /**
     * Create reporting redirection uri
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    public function createForReporting(array $parameters): ?string
    {
        if (! $this->canContactAccessPages($this->contact, [Contact::ROLE_REPORTING_DASHBOARD_HOSTS])) {
            return null;
        }

        return $this->uriGenerator->generateUri(
            self::URI_REPORTING,
            ['{hostId}' => $parameters['hostId']]
        );
    }

    /**
     * Create event logs redirection uri
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
            ['{hostId}' => $parameters['hostId']]
        );
    }

    /**
     * Create hostgroup configuration redirection uri
     *
     * @param array<string, mixed> $parameters
     * @return string|null
     */
    public function createForGroup(array $parameters): ?string
    {
        $roles = [
            Contact::ROLE_CONFIGURATION_HOSTS_HOST_GROUPS_READ_WRITE,
            Contact::ROLE_CONFIGURATION_HOSTS_HOST_GROUPS_READ
        ];

        if (! $this->canContactAccessPages($this->contact, $roles)) {
            return null;
        }

        return $this->uriGenerator->generateUri(
            self::URI_HOSTGROUP_CONFIGURATION,
            ['{hostgroupId}' => $parameters['hostgroupId']]
        );
    }

    /**
     * Create host category configuration redirection uri
     *
     * @param array<string, mixed> $parameters
     * @return string|null
     */
    public function createForCategory(array $parameters): ?string
    {
        $roles = [
            Contact::ROLE_CONFIGURATION_HOSTS_CATEGORIES_READ_WRITE,
            Contact::ROLE_CONFIGURATION_HOSTS_CATEGORIES_READ
        ];

        if (! $this->canContactAccessPages($this->contact, $roles)) {
            return null;
        }

        return $this->uriGenerator->generateUri(
            self::URI_HOST_CATEGORY_CONFIGURATION,
            ['{hostCategoryId}' => $parameters['categoryId']]
        );
    }

    /**
     * @inheritDoc
     */
    public function convertGroupsForPresenter(array $groups): array
    {
        return array_map(
            fn (array $group) => [
                'id' => $group['id'],
                'name' => $group['name'],
                'configuration_uri' => $this->createForGroup(['hostgroupId' => $group['id']])
            ],
            $groups
        );
    }

    /**
     * @inheritDoc
     */
    public function convertCategoriesForPresenter(array $categories): array
    {
        return array_map(
            fn (array $category) => [
                'id' => $category['id'],
                'name' => $category['name'],
                'configuration_uri' => $this->createForCategory(['categoryId' => $category['id']])
            ],
            $categories
        );
    }
}
