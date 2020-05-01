<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

/**
 * This class is designed to represent a proxy configuration.
 *
 * @package Centreon\Domain\Proxy
 */
class Proxy
{
    public const PROTOCOL_HTTP = 'http://';
    public const PROTOCOL_HTTPS = 'https://';
    /**
     * @link https://metacpan.org/pod/LWP::Protocol::connect
     */
    public const PROTOCOL_CONNECT = 'connect://';

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string|null
     */
    private $user;

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var string Proxy connection protocol (default: Proxy::PROTOCOL_HTTP)
     */
    private $protocol;

    /**
     * @var string[]
     */
    private $protocolAvailable = [];

    public function __construct()
    {
        $this->protocolAvailable = [self::PROTOCOL_HTTP, self::PROTOCOL_HTTPS, self::PROTOCOL_CONNECT];
        $this->protocol = self::PROTOCOL_HTTP;
    }

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

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     * @return Proxy
     * @throws \InvalidArgumentException
     * @see Proxy::PROTOCOL_HTTP
     * @see Proxy::PROTOCOL_HTTPS
     * @see Proxy::PROTOCOL_CONNECT
     */
    public function setProtocol(string $protocol): Proxy
    {
        if (!in_array($protocol, $this->protocolAvailable)) {
            throw new \InvalidArgumentException('Protocol \'' . $protocol . '\' is not allowed');
        }
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * **Formats available:**
     *
     * <<procotol>>://<<user>>:<<password>>@<<url>>:<<port>>
     *
     * <<procotol>>://<<user>>:<<password>>@<<url>>
     *
     * <<procotol>>://<<url>>:<<port>>
     *
     * <<procotol>>://<<url>>
     */
    public function __toString()
    {
        $uri = $this->protocol;
        if ($this->url !== null) {
            if ($this->user !== null) {
                $uri .= $this->user . ':' . $this->password . '@';
            }
            if ($this->port !== null && $this->port >= 0) {
                $uri .= $this->url . ':' . $this->port;
            } else {
                $uri .= $this->url;
            }
        }
        return $uri;
    }
}
