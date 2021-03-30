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

namespace Tests\Centreon\Domain\MetaServiceConfiguration\UseCase\V21;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\MetaServiceConfiguration\MetaServiceConfigurationService;
use Centreon\Domain\MetaServiceConfiguration\UseCase\V21\FindMetaServicesConfigurations;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfigurationTest;

/**
 * @package Tests\Centreon\Domain\MetaServiceConfiguration\UseCase\V21
 */
class FindMetaServicesConfigurationsTest extends TestCase
{
    /**
     * @var MetaServiceConfigurationService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $metaServiceConfigurationService;
    /**
     * @var \Centreon\Domain\MetaServiceConfiguration\Model\MetaServiceConfiguration
     */
    private $metaServiceConfiguration;

    protected function setUp(): void
    {
        $this->metaServiceConfigurationService = $this->createMock(MetaServiceConfigurationService::class);
        $this->metaServiceConfiguration = MetaServiceConfigurationTest::createEntity();
    }

    /**
     * @return FindHostCategories
     */
    private function createMetaServiceConfigurationUseCase(): FindMetaServicesConfigurations
    {
        $contact = new Contact();
        $contact->setAdmin(true);

        return (new FindMetaServicesConfigurations($this->metaServiceConfigurationService, $contact));
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->metaServiceConfigurationService
            ->expects($this->once())
            ->method('findAllWithoutAcl')
            ->willReturn([$this->metaServiceConfiguration]);

        $contact = new Contact();
        $contact->setAdmin(true);
        $findMetaServiceConfigurations = new FindMetaServicesConfigurations(
            $this->metaServiceConfigurationService,
            $contact
        );
        $response = $findMetaServiceConfigurations->execute();
        $this->assertCount(1, $response->getMetaServicesConfigurations());
    }

    /**
     * Test as non admin user
     */
    public function testExecuteAsNonAdmin(): void
    {
        $this->metaServiceConfigurationService
            ->expects($this->once())
            ->method('findAllWithAcl')
            ->willReturn([$this->metaServiceConfiguration]);

        $contact = new Contact();
        $contact->setAdmin(false);
        $findMetaServiceConfigurations = new FindMetaServicesConfigurations(
            $this->metaServiceConfigurationService,
            $contact
        );
        $response = $findMetaServiceConfigurations->execute();
        $this->assertCount(1, $response->getMetaServicesConfigurations());
    }
}
