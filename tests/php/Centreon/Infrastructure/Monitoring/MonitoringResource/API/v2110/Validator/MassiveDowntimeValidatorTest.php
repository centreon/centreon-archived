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

use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\MassiveDowntimeValidator;
use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\Interfaces\MonitoringResourceValidatorInterface;
use PHPUnit\Framework\TestCase;

class MassiveDowntimeValidatorTest extends TestCase
{
    /**
     * @var MassiveDowntimeValidator
     */
    protected $massiveDowntimeValidator;

    /**
     * @var MonitoringResourceValidatorInterface&\PHPUnit\Framework\MockObject\MockObject $monitoringResourceValidator
     */
    private $monitoringResourceValidator;

    protected function setUp(): void
    {
        $this->monitoringResourceValidator = $this->createMock(MonitoringResourceValidatorInterface::class);
        $monitoringResourceValidators = [$this->monitoringResourceValidator];
        $this->massiveDowntimeValidator = new MassiveDowntimeValidator($monitoringResourceValidators);
    }

    /**
     * test Missing downtime key in payload
     */
    public function testNoDowntimeKeyProvided(): void
    {
        $payload = [
            'resources' => []
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing comment key
     */
    public function testCommentKeyNotProvided(): void
    {
        $payload = [
            'downtime' => []
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime::comment'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Comment value not a string
     */
    public function testCommentValueNotString(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 10
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::string(
                'payload::downtime::comment'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing with_services key
     */
    public function testWithServicesKeyNotProvided(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime::with_services'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test with_services value not a boolean
     */
    public function testWithServicesValueNotBoolean(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => 'true'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::boolean(
                'payload::downtime::with_services'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing is_fixed key
     */
    public function testIsFixedKeyNotProvided(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime::is_fixed'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test is_fixed value not a boolean
     */
    public function testIsFixedValueNotBoolean(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => 'true'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::boolean(
                'payload::downtime::is_fixed'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing duration key
     */
    public function testDurationKeyNotProvided(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => true
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime::duration'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test duration value not an integer
     */
    public function testDurationValueNotInteger(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => true,
                'duration' => '7200'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::integer(
                'payload::downtime::duration'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing start_time key
     */
    public function testStartTimeKeyNotProvided(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => true,
                'duration' => 7200
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime::start_time'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test start_time value not a ISO 8601 datetime formatted
     */
    public function testStartTimeValueNotDateTimeISO8601(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => true,
                'duration' => 7200,
                'start_time' => (new \DateTime())->getTimestamp()
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::date(
                'payload::downtime::start_time'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing end_time key
     */
    public function testEndTimeKeyNotProvided(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => true,
                'duration' => 7200,
                'start_time' => (new \DateTime())->format(\DateTime::ISO8601),
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::downtime::end_time'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test end_time value not a ISO 8601 datetime formatted
     */
    public function testEndTimeValueNotDateTimeISO8601(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'this is a comment',
                'with_services' => true,
                'is_fixed' => true,
                'duration' => 7200,
                'start_time' => (new \DateTime())->format(\DateTime::ISO8601),
                'end_time' => (new \DateTime())->getTimestamp()
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::date(
                'payload::downtime::end_time'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Missing end_time key
     */
    public function testResourcesKeyNotProvided(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'This is a downtime',
                'with_services' => false,
                'is_fixed' => true,
                'duration' => 7200,
                'start_time' => (new \DateTime())->format(\DateTime::ISO8601),
                'end_time' => (new \DateTime('tomorrow'))->format(\DateTime::ISO8601)
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'payload::resources'
            )->getMessage()
        );
        $this->massiveDowntimeValidator->validateOrFail($payload);
    }

    /**
     * test Validation OK
     */
    public function testValidateOrFailSucess(): void
    {
        $payload = [
            'downtime' => [
                'comment' => 'This is a downtime',
                'with_services' => false,
                'is_fixed' => true,
                'duration' => 7200,
                'start_time' => (new \DateTime())->format(\DateTime::ISO8601),
                'end_time' => (new \DateTime('tomorrow'))->format(\DateTime::ISO8601)
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

        $this->massiveDowntimeValidator->validateOrFail($payload);
        $this->assertTrue(true);
    }
}
