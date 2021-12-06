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

namespace Tests\Centreon\Domain\Gorgone;

use Centreon\Domain\Gorgone\Command\Thumbprint;
use Centreon\Domain\Gorgone\GorgoneService;
use Centreon\Domain\Gorgone\Interfaces\CommandRepositoryInterface;
use Centreon\Domain\Gorgone\Interfaces\ResponseRepositoryInterface;
use Centreon\Domain\Gorgone\Response;
use Centreon\Domain\Gorgone\Interfaces\ResponseInterface;
use PHPUnit\Framework\TestCase;

class CommandServiceTest extends TestCase
{
    public function testSendCommand(): void
    {
        $mockThumprint = '6pX4rBssjlEV1YBHwLFRPyfRE_MvdwTKY5wsBq48cRw';
        $mockToken = '86ca0747484d947';

        $firstGorgoneResponse = file_get_contents(__DIR__ . '/first_gorgone_response.json');
        $secondGorgoneResponse = file_get_contents(__DIR__ . '/second_gorgone_response.json');

        $thumbprintCommand = new Thumbprint(2);
        $commandRepository = $this
            ->getMockBuilder(CommandRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandRepository->expects(self::any())
            ->method('send')
            ->willReturn($mockToken); // values returned for the all next tests

        $responseRepository = $this
            ->getMockBuilder(ResponseRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $responseRepository->expects($this->exactly(2))
            ->method('getResponse')
            ->withConsecutive(
                [$thumbprintCommand],
                [$thumbprintCommand]
            )
            ->willReturnOnConsecutiveCalls(
                $firstGorgoneResponse,
                $secondGorgoneResponse
            );

        $service = new GorgoneService($responseRepository, $commandRepository);
        Response::setRepository($responseRepository);

        /**
         * @var $gorgoneResponse ResponseInterface
         */
        $gorgoneResponse = $service->send($thumbprintCommand);
        do {
            $lastResponse = $gorgoneResponse->getLastActionLog();
        } while ($lastResponse == null || $lastResponse->getCode() === ResponseInterface::STATUS_BEGIN);
        $this->assertEquals($lastResponse->getToken(), $mockToken);
        $data = json_decode($lastResponse->getData(), true);
        $this->assertEquals($data['data']['thumbprint'], $mockThumprint);
    }
}
