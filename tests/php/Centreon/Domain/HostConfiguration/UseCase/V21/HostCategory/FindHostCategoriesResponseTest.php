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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostCategory;

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostCategory\FindHostCategoriesResponse;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostCategoryTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V21\HostCategory
 */
class FindHostCategoriesResponseTest extends TestCase
{
    /**
     * We test the transformation of an empty response into an array.
     */
    public function testEmptyResponse(): void
    {
        $response = new FindHostCategoriesResponse();
        $hostCategories = $response->getHostCategories();
        $this->assertCount(0, $hostCategories);
    }

    /**
     * We test the transformation of an entity into an array.
     */
    public function testNotEmptyResponse(): void
    {
        $hostCategory = HostCategoryTest::createEntity();
        $response = new FindHostCategoriesResponse();
        $response->setHostCategories([$hostCategory]);
        $hostCategories = $response->getHostCategories();
        $this->assertCount(1, $hostCategories);
        $this->assertEquals($hostCategory->getId(), $hostCategories[0]['id']);
        $this->assertEquals($hostCategory->getName(), $hostCategories[0]['name']);
        $this->assertEquals($hostCategory->getAlias(), $hostCategories[0]['alias']);
        $this->assertEquals($hostCategory->getComments(), $hostCategories[0]['comments']);
        $this->assertEquals($hostCategory->isActivated(), $hostCategories[0]['is_activated']);
    }
}
