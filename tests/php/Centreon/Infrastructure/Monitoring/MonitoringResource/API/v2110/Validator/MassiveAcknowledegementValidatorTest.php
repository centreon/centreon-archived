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

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator as Validator;
use PHPUnit\Framework\TestCase;

class MassiveAcknowledgementValidatorTest extends TestCase
{
    /**
     * @var Validator\MassiveAcknowledgementValidator
     */
    protected $massiveAcknowledgementValidator;

    /**
     * @var Validator\Interfaces\MonitoringResourceValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $monitoringResourceValidator;

    protected function setUp(): void
    {
        $this->monitoringResourceValidator = $this->createMock(
            Validator\Interfaces\MonitoringResourceValidatorInterface::class
        );
        $monitoringResourceValidators = [$this->monitoringResourceValidator];
        $this->massiveAcknowledgementValidator = new Validator\MassiveAcknowledgementValidator($monitoringResourceValidators);
    }

    /**
     * test Missing acknowledgement key in payload
     */
    public function testNoAcknowledgementKeyProvided(): void
    {
        $payload = [
            'resources' => []
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::acknowledgement'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Missing comment key
     */
    public function testCommentKeyNotProvided(): void
    {
        $payload = [
            'acknowledgement' => []
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::acknowledgement::comment'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Comment value not a string
     */
    public function testCommentValueNotString(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 10
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::string(
                'payload::acknowledgement::comment'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Missing with_services key
     */
    public function testWithServicesKeyNotProvided(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 'this is an acknowledgement',
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::acknowledgement::with_services'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test with_services value not a boolean
     */
    public function testWithServicesValueNotBoolean(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 'this is an acknowledgement',
                'with_services' => 'true'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::boolean(
                'payload::acknowledgement::with_services'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Missing is_notify_contacts key
     */
    public function testIsNotifyContactsKeyNotProvided(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 'this is an acknowledgement',
                'with_services' => true
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::acknowledgement::is_notify_contacts'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test is_notify_contacts value not a boolean
     */
    public function testIsNotifyContactsValueNotBoolean(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 'this is an acknowledgement',
                'with_services' => true,
                'is_notify_contacts' => 'true'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::boolean(
                'payload::acknowledgement::is_notify_contacts'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Missing resources key
     */
    public function testResourcesKeyNotProvided(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 'This is an acknowledgement',
                'with_services' => false,
                'is_notify_contacts' => true
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::resources'
            )->getMessage()
        );
        $this->massiveAcknowledgementValidator->validateOrFail($payload);
    }

    /**
     * test Validation OK
     */
    public function testValidateOrFailSucess(): void
    {
        $payload = [
            'acknowledgement' => [
                'comment' => 'This is an acknowledgement',
                'with_services' => false,
                'is_notify_contacts' => true
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

        $this->massiveAcknowledgementValidator->validateOrFail($payload);
        $this->assertTrue(true);
    }
}
