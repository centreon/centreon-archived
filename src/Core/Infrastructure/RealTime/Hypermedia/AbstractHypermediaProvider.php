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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Domain\Contact\Contact;

abstract class AbstractHypermediaProvider
{
    public const URI_EVENT_LOGS = '/main.php?p=20301&svc={hostId}_{serviceId}';
    public const ENDPOINT_SERVICE_DOWNTIME = '';
    public const ENDPOINT_DETAILS = '';

    /**
     * @param ContactInterface $contact
     * @param UriGenerator $uriGenerator
     */
    public function __construct(
        protected ContactInterface $contact,
        protected UriGenerator $uriGenerator
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, string|null>
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
    abstract public function createForConfiguration(array $parameters): ?string;

    /**
     * Create event logs redirection uri
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    abstract public function createForEventLog(array $parameters): ?string;

    /**
     * Create reporting redirection uri
     *
     * @param array<string, int> $parameters
     * @return string|null
     */
    abstract public function createForReporting(array $parameters): ?string;

    /**
     * Checks if contact has access to pages defined in roles
     *
     * @param ContactInterface $contact
     * @param string[] $topologyRoles
     * @return boolean
     */
    protected function canContactAccessPages(ContactInterface $contact, array $topologyRoles): bool
    {
        return $contact->isAdmin() || $this->hasTopologyAccess($contact, $topologyRoles);
    }

    /**
     * Checks if contact has topology roles submited
     *
     * @param ContactInterface $contact
     * @param string[] $topologyRoles
     * @return boolean
     */
    protected function hasTopologyAccess(ContactInterface $contact, array $topologyRoles): bool
    {
        foreach ($topologyRoles as $topologyRole) {
            if ($contact->hasTopologyRole($topologyRole)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Proxy method to generates endpoint call URI with parameters
     *
     * @param string $endpoint
     * @param array<string, mixed> $parameters
     * @return string
     */
    protected function generateEndpoint(string $endpoint, array $parameters): string
    {
        return $this->uriGenerator->generateEndpoint($endpoint, $parameters);
    }

    /**
     * Proxy method to generate an uri
     * @param string $uri
     * @param array<string, mixed> $parameters
     * @return string
     */
    protected function generateUri(string $uri, array $parameters): string
    {
        return $this->uriGenerator->generateUri($uri, $parameters);
    }

    /**
     * @param array<string, integer> $parameters
     * @return string
     */
    protected function generateDowntimeEndpoint(array $parameters): string
    {
        $downtimeFilter = [
            'search' => json_encode([
                RequestParameters::AGGREGATE_OPERATOR_AND => [
                    [
                        'start_time' => [RequestParameters::OPERATOR_LESS_THAN => time(),],
                        'end_time' => [RequestParameters::OPERATOR_GREATER_THAN => time(),],
                        [
                            RequestParameters::AGGREGATE_OPERATOR_OR => [
                                'is_cancelled' => [RequestParameters::OPERATOR_NOT_EQUAL => 1,],
                                'deletion_time' => [RequestParameters::OPERATOR_GREATER_THAN => time(),],
                            ],
                        ]
                    ]
                ]
            ])
        ];

        return $this->generateEndpoint(
            static::ENDPOINT_SERVICE_DOWNTIME,
            array_merge($parameters, $downtimeFilter)
        );
    }

    /**
     * @param array<string, int> $urlParams
     */
    protected function createUrlForEventLog(array $urlParams): ?string
    {
        if (! $this->canContactAccessPages($this->contact, [Contact::ROLE_MONITORING_EVENT_LOGS])) {
            return null;
        }

        return $this->generateUri(static::URI_EVENT_LOGS, $urlParams);
    }

    /**
     * @param array<string, int> $parameters
     */
    public function generateResourceDetailsUri(array $parameters): string
    {
        return $this->generateEndpoint(static::ENDPOINT_DETAILS, $parameters);
    }
}
