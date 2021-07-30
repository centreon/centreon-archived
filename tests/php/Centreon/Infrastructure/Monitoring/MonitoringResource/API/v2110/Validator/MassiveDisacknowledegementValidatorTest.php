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

use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\MassiveDisacknowledgementValidator;
use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\Interfaces\MonitoringResourceValidatorInterface;
use PHPUnit\Framework\TestCase;

class MassiveDisacknowledgementValidatorTest extends TestCase
{
    /**
     * @var MassiveDisacknowledgementValidator
     */
    protected $massiveDisacknowledgementValidator;

    /**
     * @var MonitoringResourceValidatorInterface&MockObject $monitoringResourceValidator
     */
    private $monitoringResourceValidator;

    protected function setUp(): void
    {
        $this->monitoringResourceValidator = $this->createMock(MonitoringResourceValidatorInterface::class);
        $monitoringResourceValidators = [$this->monitoringResourceValidator];
        $this->massiveDisacknowledgementValidator = new MassiveDisacknowledgementValidator($monitoringResourceValidators);
    }

    /**
     * test Missing disacknowledgement key in payload
     */
    public function testNoDisacknowledgementKeyProvided(): void
    {
        $payload = [
            'resources' => []
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::disacknowledgement'
            )->getMessage()
        );
        $this->massiveDisacknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Missing with_services key
     */
    public function testWithServicesKeyNotProvided(): void
    {
        $payload = [
            'disacknowledgement' => []
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::disacknowledgement::with_services'
            )->getMessage()
        );
        $this->massiveDisacknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test with_services value not a boolean
     */
    public function testWithServicesValueNotBoolean(): void
    {
        $payload = [
            'disacknowledgement' => [
                'with_services' => 'true'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::boolean(
                'payload::disacknowledgement::with_services'
            )->getMessage()
        );
        $this->massiveDisacknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Missing resources key
     */
    public function testResourcesKeyNotProvided(): void
    {
        $payload = [
            'disacknowledgement' => [
                'with_services' => false
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::resources'
            )->getMessage()
        );
        $this->massiveDisacknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Validation OK
     */
    public function testValidateOrFailSucess(): void
    {
        $payload = [
            'disacknowledgement' => [
                'with_services' => false
            ],
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

        $this->massiveDisacknowledgementValidator->validateOrFail($payload);
        $this->assertTrue(true);
    }
}
