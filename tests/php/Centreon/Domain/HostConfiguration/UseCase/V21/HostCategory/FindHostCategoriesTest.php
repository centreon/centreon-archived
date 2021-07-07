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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostCategory;

use Centreon\Domain\Contact\Contact;
use Centreon\Domain\HostConfiguration\HostCategoryService;
use Centreon\Domain\HostConfiguration\UseCase\V21\HostCategory\FindHostCategories;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostCategoryTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostCategory
 */
class FindHostCategoriesTest extends TestCase
{
    /**
     * @var HostCategoryService&\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostCategoryService;
    /**
     * @var \Centreon\Domain\HostConfiguration\Model\HostCategory
     */
    private $hostCategory;

    protected function setUp(): void
    {
        $this->hostCategoryService = $this->createMock(HostCategoryService::class);
        $this->hostCategory = hostCategoryTest::createEntity();
    }

    /**
     * @return FindHostCategories
     */
    private function createHostCategoryUseCase(): FindHostCategories
    {
        $contact = new Contact();
        $contact->setAdmin(true);

        return (new FindHostCategories($this->hostCategoryService, $contact));
    }

    /**
     * Test as admin user
     */
    public function testExecuteAsAdmin(): void
    {
        $this->hostCategoryService
            ->expects($this->once())
            ->method('findAllWithoutAcl')
            ->willReturn([$this->hostCategory]);

        $contact = new Contact();
        $contact->setAdmin(true);
        $findHostCategories = new FindHostCategories($this->hostCategoryService, $contact);
        $response = $findHostCategories->execute();
        $this->assertCount(1, $response->getHostCategories());
    }

    /**
     * Test as non admin user
     */
    public function testExecuteAsNonAdmin(): void
    {
        $this->hostCategoryService
            ->expects($this->once())
            ->method('findAllWithAcl')
            ->willReturn([$this->hostCategory]);

        $contact = new Contact();
        $contact->setAdmin(false);
        $findHostCategories = new FindHostCategories($this->hostCategoryService, $contact);
        $response = $findHostCategories->execute();
        $this->assertCount(1, $response->getHostCategories());
    }
}
