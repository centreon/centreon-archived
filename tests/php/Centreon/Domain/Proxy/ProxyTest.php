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

namespace Tests\Centreon\Domain\Proxy;

use Centreon\Domain\Proxy\Proxy;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    public function testSettingProxyWithEmptyUrl(): void
    {
        $proxy = new Proxy();
        $proxy->setUrl('');
        $this->assertNull($proxy->getUrl());

        $proxy->setUrl(null);
        $this->assertNull($proxy->getUrl());
    }

    public function testSettingProxyWithNonEmptyUrl(): void
    {
        $proxy = new Proxy();
        $proxy->setUrl('centreon.com');
        $this->assertEquals('centreon.com', $proxy->getUrl());
    }

    public function testSettingProxyWithEmptyUser(): void
    {
        $proxy = new Proxy();
        $proxy->setUser('');
        $this->assertNull($proxy->getUser());

        $proxy->setUser(null);
        $this->assertNull($proxy->getUser());
    }

    public function testSettingProxyWithNonEmptyUser(): void
    {
        $proxy = new Proxy();
        $proxy->setUser('admin');
        $this->assertEquals('admin', $proxy->getUser());
    }

    public function testSettingProxyWithEmptyPassword(): void
    {
        $proxy = new Proxy();
        $proxy->setPassword('');
        $this->assertEmpty($proxy->getPassword());

        $proxy->setPassword(null);
        $this->assertNull($proxy->getPassword());
    }

    public function testSettingProxyWithNonEmptyPassword(): void
    {
        $proxy = new Proxy();
        $proxy->setPassword('my password');
        $this->assertEquals('my password', $proxy->getPassword());
    }

    public function testSettingProxyWithNotAllowedProtocol(): void
    {
        $protocol = 'http://badprotocol';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(_('Protocol %s is not allowed'), $protocol));
        $proxy = new Proxy();
        $proxy->setProtocol($protocol);
    }

    public function testSettingProxyWithAllowedProtocol(): void
    {
        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_CONNECT);
        $this->assertEquals(Proxy::PROTOCOL_CONNECT, $proxy->getProtocol());

        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTPS);
        $this->assertEquals(Proxy::PROTOCOL_HTTPS, $proxy->getProtocol());

        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTP);
        $this->assertEquals(Proxy::PROTOCOL_HTTP, $proxy->getProtocol());
    }

    public function testSettingProxyWithNegativePort(): void
    {
        $proxy = new Proxy();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The port can only be between 0 and 65535 inclusive');
        $proxy->setPort(-1);
    }

    public function testSettingProxyWithOutOfRangePort(): void
    {
        $proxy = new Proxy();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The port can only be between 0 and 65535 inclusive');
        $proxy->setPort(65536);
    }

    public function testProxySerialization(): void
    {
        // Serialization to test: <<procotol>>://<<user>>:<<password>>@<<url>>:<<port>>
        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTPS);
        $proxy->setUser('user');
        $proxy->setPassword('password');
        $proxy->setUrl('centreon.com');
        $proxy->setPort(10);
        $this->assertEquals('https://user:password@centreon.com:10', (string) $proxy);

        // Serialization to test: <<procotol>>://<<user>>:<<password>>@<<url>>
        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTPS);
        $proxy->setUser('user');
        $proxy->setPassword('password');
        $proxy->setUrl('centreon.com');
        $this->assertEquals('https://user:password@centreon.com', (string) $proxy);

        // Serialization to test: <<procotol>>://<<url>>:<<port>>
        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTPS);
        $proxy->setPassword('password'); // Without user value the password should not be taken into account
        $proxy->setUrl('centreon.com');
        $proxy->setPort(10);
        $this->assertEquals('https://centreon.com:10', (string) $proxy);

        // Serialization to test: <<procotol>>://<<url>>
        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTPS);
        $proxy->setUrl('centreon.com');
        $this->assertEquals('https://centreon.com', (string) $proxy);

        // Serialization when nothing is defined
        $proxy = new Proxy();
        $proxy->setProtocol(Proxy::PROTOCOL_HTTPS);
        $this->assertEquals('', (string) $proxy);
    }
}
