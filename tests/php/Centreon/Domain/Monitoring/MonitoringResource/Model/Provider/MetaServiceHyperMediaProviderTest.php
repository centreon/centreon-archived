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

namespace Tests\Centreon\Domain\Monitoring\MonitoringResource\Model\Provider;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Contact\Contact;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Monitoring\MonitoringResource\Model\Provider\MetaServiceHyperMediaProvider;

class MetaServiceHyperMediaProviderTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private $metaServiceMonitoringResource;

    /**
     * @var Contact
     */
    private $adminContact;

    /**
     * @var Contact
     */
    private $nonAdminContact;

    /**
     * @var array<string, string>
     */
    private $uriLinksExpected;

    /**
     * @var UrlGeneratorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlGenerator;

    /**
     * @var MetaServiceHyperMediaProvider
     */
    private $metaServiceHyperMediaProviderService;

    protected function setUp(): void
    {
        $kernel = new \App\Kernel('test', false);
        $kernel->boot();

        $this->metaServiceMonitoringResource = [
            'id' => 1,
            'service_id' => 10,
            'host_id' => 5
        ];

        $this->adminContact = (new Contact())
            ->setId(1)
            ->setName('admin')
            ->setAdmin(true);

        $this->nonAdminContact = (new Contact())
            ->setId(2)
            ->setName('nonAdmin')
            ->setAdmin(false);

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->metaServiceHyperMediaProviderService = new MetaServiceHyperMediaProvider($this->urlGenerator);

        $this->uriLinksExpected = [
            'configuration' => '/main.php?p=60204&o=c&meta_id=1',
            'logs' => '/main.php?p=20301&svc=5_10'
        ];
    }

    /**
     * test uris generation with admin contact
     */
    public function testGenerateUrisWithAdminContact(): void
    {
        $generatedUris = $this->metaServiceHyperMediaProviderService->generateUris(
            $this->metaServiceMonitoringResource,
            $this->adminContact
        );

        $this->assertEquals($this->uriLinksExpected['configuration'], $generatedUris['configuration']);
        $this->assertEquals($this->uriLinksExpected['logs'], $generatedUris['logs']);
    }

    /**
     * test uris generation with non admin contact
     */
    public function testGenerateUrisWithNonAdminContact(): void
    {
        $generatedUris = $this->metaServiceHyperMediaProviderService->generateUris(
            $this->metaServiceMonitoringResource,
            $this->nonAdminContact
        );

        $this->assertNull($generatedUris['logs']);
        $this->assertNull($generatedUris['configuration']);
    }
}
