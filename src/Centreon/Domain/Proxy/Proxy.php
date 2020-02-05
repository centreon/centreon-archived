<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Proxy;

use JMS\Serializer\Annotation as Serializer;

/**
 * This class is designed to represent a proxy configuration.
 *
 * @package Centreon\Domain\Proxy
 */
class Proxy
{
    /**
     * @Serializer\Type("string")
     * @var string|null
     */
    private $url;

    /**
     * @Serializer\Type("integer")
     * @var int|null
     */
    private $port;

    /**
     * @Serializer\Type("string")
     * @var string|null
     */
    private $user;

    /**
     * @Serializer\Type("string")
     * @var string|null
     */
    private $password;

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Proxy
     */
    public function setUrl(?string $url): Proxy
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @param int|null $port
     * @return Proxy
     */
    public function setPort(?int $port): Proxy
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $user
     * @return Proxy
     */
    public function setUser(?string $user): Proxy
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return Proxy
     */
    public function setPassword(?string $password): Proxy
    {
        $this->password = $password;
        return $this;
    }
}
