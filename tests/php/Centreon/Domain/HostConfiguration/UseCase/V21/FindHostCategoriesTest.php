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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21;

use Centreon\Domain\HostConfiguration\Interfaces\HostCategoryReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\UseCase\V21\FindHostCategories;
use PHPStan\Testing\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostCategoryTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V21
 */
class FindHostCategoriesTest extends TestCase
{
    /**
     * @var HostCategoryReadRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostCategoryReadRepository;
    /**
     * @var \Centreon\Domain\HostConfiguration\Model\HostCategory
     */
    private $hostCategory;

    protected function setUp(): void
    {
        $this->hostCategoryReadRepository = $this->createMock(HostCategoryReadRepositoryInterface::class);
        $this->hostCategory = hostCategoryTest::createEntity();
    }

    /**
     * @return FindHostCategories
     */
    private function createHostCategoryUseCase(): FindHostCategories
    {
        return (new FindHostCategories($this->hostCategoryReadRepository));
    }

    public function testExecute(): void
    {
        $this->hostCategoryReadRepository->expects($this->once())
            ->method('findHostCategories')
            ->willReturn([$this->hostCategory]);
        $findHostCategories = $this->createHostCategoryUseCase();
        $response = $findHostCategories->execute();
        $this->assertCount(1, $response->getHostCategories());
    }
}
