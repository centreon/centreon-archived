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

namespace Core\Infrastructure\RealTime\Api\Hypermedia;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostResponse;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderTrait;

class HostHypermediaProvider implements HypermediaProviderInterface
{
    use HypermediaProviderTrait;

    public const URI_CONFIGURATION = '/main.php?p=60101&o=c&host_id={hostId}',
                 URI_EVENT_LOGS = '/main.php?p=20301&h={hostId}',
                 URI_REPORTING = '/main.php?p=307&host={hostId}',
                 ENDPOINT_HOST_TIMELINE = 'centreon_application_monitoring_gettimelinebyhost';

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
        return ($data instanceof FindHostResponse);
    }

    /**
     * @inheritDoc
     */
    public function createForConfiguration(mixed $response): ?string
    {
        return (
            $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_WRITE)
            || $this->contact->hasTopologyRole(Contact::ROLE_CONFIGURATION_HOSTS_READ)
            || $this->contact->isAdmin()
        )
        ? $this->getBaseUri() . str_replace('{hostId}', (string) $response->id, self::URI_CONFIGURATION)
        : null;
    }

    /**
     * @inheritDoc
     */
    public function createForReporting(mixed $response): ?string
    {
        return (
            $this->contact->hasTopologyRole(Contact::ROLE_REPORTING_DASHBOARD_HOSTS)
            || $this->contact->isAdmin()
        )
        ? $this->getBaseUri() . str_replace('{hostId}', (string) $response->id, self::URI_REPORTING)
        : null;
    }

    /**
     * @inheritDoc
     */
    public function createForEventLog(mixed $response): ?string
    {
        return (
            $this->contact->hasTopologyRole(Contact::ROLE_MONITORING_EVENT_LOGS)
            || $this->contact->isAdmin()
        )
        ? $this->getBaseUri() . str_replace('{hostId}', (string) $response->id, self::URI_EVENT_LOGS)
        : null;
    }

    /**
     * @inheritDoc
     */
    public function createForTimelineEndpoint(mixed $response): string
    {
        return $this->router->generate(static::ENDPOINT_HOST_TIMELINE, ['hostId' => $response->id]);
    }
}
