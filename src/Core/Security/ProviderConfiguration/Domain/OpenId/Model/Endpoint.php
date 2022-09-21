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
 *  For more information : contact@centreon.com
 */

declare(strict_types=1);

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Model;

use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\InvalidEndpointException;

class Endpoint
{
    public const INTROSPECTION = 'introspection_endpoint';
    public const USER_INFORMATION = 'user_information_endpoint';
    public const CUSTOM = 'custom_endpoint';

    /**
     * @var string[]
     */
    private const ALLOWED_TYPES = [
        self::INTROSPECTION,
        self::USER_INFORMATION,
        self::CUSTOM
    ];

    /**
     * @param string $type
     * @param string|null $url
     * @throws InvalidEndpointException
     */
    public function __construct(
        private string $type = self::INTROSPECTION,
        private ?string $url = null,
    ) {
        $this->guardType();
        $this->guardUrl();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        if ($this->type !== self::CUSTOM) {
            return null;
        }

        return $this->url;
    }

    /**
     * @return array{"type": string, "custom_endpoint":string|null}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'custom_endpoint' => $this->url
        ];
    }

    /**
     * @throws InvalidEndpointException
     */
    private function guardType(): void
    {
        if (!in_array($this->type, self::ALLOWED_TYPES)) {
            throw InvalidEndpointException::invalidType();
        }
    }

    /**
     * @return void
     * @throws InvalidEndpointException
     */
    private function guardUrl(): void
    {
        if (
            $this->type === self::CUSTOM &&
            (
                $this->url === null ||
                (
                    !str_starts_with($this->url, '/') &&
                    filter_var($this->url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === false
                )
            )
        ) {
            throw InvalidEndpointException::invalidUrl();
        }
    }
}
