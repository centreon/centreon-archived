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
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HypermediaCreatorHelper
{
    use HttpUrlTrait;

    /**
     * @param UrlGeneratorInterface $router
     */
    public function __construct(protected UrlGeneratorInterface $router)
    {
    }

    /**
     * Checks if contact has access to pages defined in roles
     *
     * @param ContactInterface $contact
     * @param string[] $topologyRoles
     * @return boolean
     */
    public function canContactAccessPages(ContactInterface $contact, array $topologyRoles): bool
    {
        if (! $contact->isAdmin() && ! $this->hasTopologyAccess($contact, $topologyRoles)) {
            return false;
        }

        return true;
    }

    /**
     * Checks if contact has topology roles submited
     *
     * @param ContactInterface $contact
     * @param string[] $topologyRoles
     * @return boolean
     */
    private function hasTopologyAccess(ContactInterface $contact, array $topologyRoles): bool
    {
        foreach ($topologyRoles as $topologyRole) {
            if ($contact->hasTopologyRole($topologyRole)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generates endpoint call URI with parameters
     *
     * @param string $endpoint
     * @param array<string, mixed> $parameters
     * @return string
     */
    public function generateEndpoint(string $endpoint, array $parameters): string
    {
        return $this->router->generate($endpoint, $parameters);
    }

    /**
     * Generates a uri where place holders are replaced by their values
     * Format of the parameters
     * [
     *   '{hostId}' => 10,
     *   '{serviceId} => 20,
     *    ...
     * ]
     *
     * @param string $uri
     * @param array<string, mixed> $parameters
     * @return string
     */
    public function generateUri(string $uri, array $parameters): string
    {
        $generatedUri = $this->getBaseUri() . $uri;
        foreach ($parameters as $placeHolder => $value) {
            $generatedUri = str_replace($placeHolder, (string) $value, $generatedUri);
        }

        return $generatedUri;
    }
}
