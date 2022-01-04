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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Core\Application\RealTime\UseCase\FindService\FindServiceResponse;
use Core\Infrastructure\RealTime\Api\Hypermedia\HypermediaProviderTrait;

class ServiceHypermediaProvider implements HypermediaProviderInterface
{
    use HypermediaProviderTrait;

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
        return ($data instanceof FindServiceResponse);
    }

    /**
     * @inheritDoc
     */
    public function createForConfiguration(mixed $response): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForReporting(mixed $response): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForEventLog(mixed $response): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function createForTimelineEndpoint(mixed $response): string
    {
        return '';
    }
}
