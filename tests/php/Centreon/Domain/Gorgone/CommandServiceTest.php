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

use Centreon\Domain\Gorgone\Command\Internal\ThumbprintCommand;
use Centreon\Domain\Gorgone\GorgoneResponse;
use Centreon\Domain\Gorgone\GorgoneService;
use Centreon\Domain\Gorgone\Interfaces\GorgoneResponseInterface;
use Centreon\Domain\Option\Interfaces\OptionRepositoryInterface;
use Centreon\Infrastructure\Gorgone\GorgoneCommandRepositoryAPI;
use Centreon\Infrastructure\Gorgone\GorgoneResponseRepositoryAPI;
use PHPUnit\Framework\TestCase;

class CommandServiceTest extends TestCase
{
    public function testSendCommand()
    {
        $mockThumprint = '6pX4rBssjlEV1YBHwLFRPyfRE_MvdwTKY5wsBq48cRw';
        $mockToken = '86ca0747484d947';

        $firstGorgoneResponse = file_get_contents('first_gorgone_response.json');
        $secondGorgoneResponse = file_get_contents('second_gorgone_response.json');

        $optionRepository = $this
            ->getMockBuilder(OptionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $optionRepository->expects(self::any())->method('findSelectedOptions')->willReturn([]);

        $thumbprintCommand = new ThumbprintCommand(2);
        $commandRepository = $this
            ->getMockBuilder(GorgoneCommandRepositoryAPI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandRepository->expects(self::any())->method('defineConnectionParameters');

        $commandRepository->expects(self::any())
            ->method('send')
            //->with($thumbprintCommand)
            ->willReturn($mockToken); // values returned for the all next tests

        $responseRepository = $this
            ->getMockBuilder(GorgoneResponseRepositoryAPI::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseRepository->expects(self::any())->method('defineConnectionparameters');

        $responseRepository->expects($this->at(1))
            ->method('getResponse')
            ->with($thumbprintCommand)
            ->willReturn($firstGorgoneResponse);

        $responseRepository->expects($this->at(2))
            ->method('getResponse')
            ->with($thumbprintCommand)
            ->willReturn($secondGorgoneResponse);

        $service = new GorgoneService($responseRepository, $commandRepository, $optionRepository);
        GorgoneResponse::setRepository($responseRepository);

        /**
         * @var $gorgoneResponse GorgoneResponseInterface
         */
        $gorgoneResponse = $service->send($thumbprintCommand);
        do {
            $lastResponse = $gorgoneResponse->getLastActionLog();
        } while ($lastResponse == null || $lastResponse->getCode() === GorgoneResponseInterface::STATUS_BEGIN);
        $this->assertEquals($lastResponse->getToken(), $mockToken);
        $data = json_decode($lastResponse->getData(), true);
        $this->assertEquals($data['data']['thumbprint'], $mockThumprint);
    }
}
