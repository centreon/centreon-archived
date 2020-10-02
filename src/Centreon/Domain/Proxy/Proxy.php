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
     * @var string[]
     */
    public const AVAILABLE_PROTOCOLS = [
        self::PROTOCOL_HTTP,
        self::PROTOCOL_HTTPS,
        self::PROTOCOL_CONNECT,
    ];

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

    public function __construct()
    {
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
     * @param string|null $url An empty url will not be taken into account.
     * @return Proxy
     */
    public function setUrl(?string $url): Proxy
    {
        if (!empty($url)) {
            $this->url = $url;
        }
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
     * @param int|null $port Numerical value (0 >= PORT <= 65535)
     * @return Proxy
     * @throws \InvalidArgumentException
     */
    public function setPort(?int $port): Proxy
    {
        if ($port >= 0 && $port <= 65535) {
            $this->port = $port;
        } else {
            throw new \InvalidArgumentException(
                sprintf(_('The port can only be between 0 and 65535 inclusive'))
            );
        }

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
     * @param string|null $user An empty user will not be taken into account.
     * @return Proxy
     */
    public function setUser(?string $user): Proxy
    {
        if (!empty($user)) {
            $this->user = $user;
        }
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
        if (!in_array($protocol, static::AVAILABLE_PROTOCOLS)) {
            throw new \InvalidArgumentException(
                sprintf(_('Protocol %s is not allowed'), $protocol)
            );
        }
        $this->protocol = $protocol;
        return $this;
    }

    /**
     * **Available formats:**
     *
     * <<procotol>>://<<user>>:<<password>>@<<url>>:<<port>>
     *
     * <<procotol>>://<<user>>:<<password>>@<<url>>
     *
     * <<procotol>>://<<url>>:<<port>>
     *
     * <<procotol>>://<<url>>
     *
     * @return string
     */
    public function __toString(): string
    {
        $uri = '';
        if (!empty($this->url)) {
            $uri .= $this->protocol;
            if (!empty($this->user)) {
                $uri .= $this->user . ':' . $this->password . '@';
            }
            if (!empty($this->port) && $this->port > 0 && $this->port < 65536) {
                $uri .= $this->url . ':' . $this->port;
            } else {
                $uri .= $this->url;
            }
        }
        return $uri;
    }
}
