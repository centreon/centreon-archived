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

namespace Tests\Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator;

use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator as Validator;
use Centreon\Domain\Common\Assertion\AssertionException;
use PHPUnit\Framework\TestCase;

class MassiveCheckValidatorTest extends TestCase
{
    /**
     * @var MassiveCheckValidator
     */
    protected $massiveCheckValidator;

    /**
     * @var MonitoringResourceValidatorInterface&\PHPUnit\Framework\MockObject\MockObject $monitoringResourceValidator
     */
    private $monitoringResourceValidator;

    protected function setUp(): void
    {
        $this->monitoringResourceValidator = $this->createMock(
            Validator\Interfaces\MonitoringResourceValidatorInterfaceMonitoringResourceValidatorInterface::class
        );
        $monitoringResourceValidators = [$this->monitoringResourceValidator];
        $this->massiveCheckValidator = new Validator\MassiveCheckValidator($monitoringResourceValidators);
    }

    /**
     * test Missing resources key
     */
    public function testResourcesKeyNotProvided(): void
    {
        $payload = [];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::resources'
            )->getMessage()
        );
        $this->massiveCheckValidator->validateOrFail($payload);
    }

    /**
     * test Validation OK
     */
    public function testValidateOrFailSucess(): void
    {
        $payload = [
            'resources' => [
                [
                    'id' => 10,
                    'type' => 'service',
                    'name' => 'serviceName',
                    'parent' => [
                        'id' => 1,
                        'type' => 'host',
                        'name' => 'hostName'
                    ]
                ]
            ]
        ];
        $this->monitoringResourceValidator->expects($this->once())
            ->method('isValidFor')
            ->willReturn(true);

        $this->monitoringResourceValidator->expects($this->once())
            ->method('validateOrFail')
            ->with($payload['resources'][0]);

        $this->massiveCheckValidator->validateOrFail($payload);
        $this->assertTrue(true);
    }
}
