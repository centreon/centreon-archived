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

use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UriGenerator
{
    use HttpUrlTrait;

    /**
     * @param UrlGeneratorInterface $router
     */
    public function __construct(protected UrlGeneratorInterface $router)
    {
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
