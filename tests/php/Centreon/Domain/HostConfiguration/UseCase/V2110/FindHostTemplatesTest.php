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

namespace Tests\Centreon\Domain\HostConfiguration\UseCase\V2110;

use Centreon\Domain\HostConfiguration\Interfaces\HostConfigurationReadRepositoryInterface;
use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\HostConfiguration\UseCase\V2110\HostTemplate\FindHostTemplates;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostTemplateTest;

/**
 * @package Tests\Centreon\Domain\HostConfiguration\UseCase\V2110
 */
class FindHostTemplatesTest extends TestCase
{
    /**
     * @var HostConfigurationReadRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hostConfigurationRepository;
    /**
     * @var HostTemplate
     */
    private $hostTemplate;

    protected function setUp(): void
    {
        $this->hostConfigurationRepository = $this->createMock(HostConfigurationReadRepositoryInterface::class);
        $this->hostTemplate = HostTemplateTest::createEntity();
    }

    private function createHostTemplateUseCase(): FindHostTemplates
    {
        return (new FindHostTemplates($this->hostConfigurationRepository));
    }

    public function testExecute(): void
    {
        $this->hostConfigurationRepository->expects($this->once())
            ->method('findHostTemplates')
            ->willReturn([$this->hostTemplate]);
        $findHostTemplate = $this->createHostTemplateUseCase();
        $response = $findHostTemplate->execute();
        $this->assertCount(1, $response->getHostTemplates());
    }
}
