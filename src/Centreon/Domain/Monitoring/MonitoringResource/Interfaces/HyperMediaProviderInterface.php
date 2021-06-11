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

namespace Centreon\Domain\Monitoring\MonitoringResource\Interfaces;

use Centreon\Domain\Contact\Contact;

interface HyperMediaProviderInterface
{
    /**
     * Returns the type of hyper media provider.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * This function will generate the endpoints for the provided resource
     *
     * @param array<string, mixed> $monitoringResource
     * @return array<string, string>
     */
    public function generateEndpoints(array $monitoringResource): array;

    /**
     * This function will generate the internal uris regarding provided resource with ACL considerations.
     *
     * @param array<string, mixed> $monitoringResource
     * @param Contact $contact
     * @return array<string, mixed>
     */
    public function generateUris(array $monitoringResource, Contact $contact): array;
}
