<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Tests\Core\Infrastructure\Configuration\User\Api\FindUsers;

use PHPUnit\Framework\TestCase;
use Core\Infrastructure\Configuration\User\Api\FindUsers\FindUsersPresenter;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Configuration\User\UseCase\FindUsers\FindUsersResponse;
use Core\Domain\Configuration\User\Model\User;

class FindUsersPresenterTest extends TestCase
{
    /**
     * @var RequestParametersInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestParameters;

    /**
     * @var PresenterFormatterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenterFormatter;


    public function setUp(): void
    {
        $this->requestParameters = $this->createMock(RequestParametersInterface::class);
        $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    }

    /**
     * Test that the controller calls properly the usecase
     */
    public function testFindControllerExecute(): void
    {
        $presenter = new FindUsersPresenter(
            $this->requestParameters,
            $this->presenterFormatter,
        );

        $requestParameterValues = [
            'page' => 1,
            'limit' => 1,
            'search' => '',
            'sort_by' => '',
            'total' => 3,
        ];

        $user = [
            'id' => 1,
            'alias' => 'alias',
            'name' => 'name',
            'email' => 'root@localhost',
            'is_admin' => true,
        ];

        $userModel = new User(
            1,
            'alias',
            'name',
            'root@localhost',
            true
        );

        $this->requestParameters->expects($this->once())
            ->method('toArray')
            ->willReturn($requestParameterValues);

        $this->presenterFormatter->expects($this->once())
            ->method('present')
            ->with(
                [
                    'result' => [$user],
                    'meta' => $requestParameterValues,
                ]
            );

        $response = new FindUsersResponse([$userModel]);
        $presenter->present($response);
    }
}
