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

use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\ServiceResourceValidator;
use Centreon\Domain\Common\Assertion\AssertionException;
use PHPUnit\Framework\TestCase;

class ServiceResourceValidatorTest extends TestCase
{
    /**
     * @var ServiceResourceValidator
     */
    protected $serviceResourceValidator;

    protected function setUp(): void
    {
        $this->serviceResourceValidator = new ServiceResourceValidator();
    }

    /**
     * test Missing parent definition for the service monitoring resource
     */
    public function testMissingParentForServiceResource(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'service',
            'name' => 'serviceName',
            'parent' => null
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::notNull(
                'resource::parent'
            )->getMessage()
        );
        $this->serviceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Missing mandatory property of service resource monitoring definition
     */
    public function testMissingMandatoryProperty(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'service',
            'name' => 'serviceName',
            'parent' => [
                'id' => 1,
                'type' => 'host'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'resource::parent::name'
            )->getMessage()
        );
        $this->serviceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Wrong Resource Type sent for Service Monitoring Resource
     */
    public function testWrongResourceType(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'host',
            'name' => 'serviceName',
            'parent' => [
                'id' => 1,
                'type' => 'host',
                'name' => 'hostName'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::eq(
                'resource::type'
            )->getMessage()
        );
        $this->serviceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Wrong property type sent for Service Monitoring Resource (id)
     */
    public function testWrongPropertyIntegerType(): void
    {
        $monitoringResource = [
            'id' => '10',
            'type' => 'service',
            'name' => 'serviceName',
            'parent' => [
                'id' => 1,
                'type' => 'host',
                'name' => 'hostName'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::integer(
                'resource::id'
            )->getMessage()
        );
        $this->serviceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Wrong property type sent for Service Monitoring Resource (type)
     */
    public function testWrongPropertyIntegerString(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'service',
            'name' => 10,
            'parent' => [
                'id' => 1,
                'type' => 'host',
                'name' => 'hostName'
            ]
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::string(
                'resource::name'
            )->getMessage()
        );
        $this->serviceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test ValidateOrFail successful
     */
    public function testValidateOrFailSuccess(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'service',
            'name' => 'serviceName',
            'parent' => [
                'id' => 1,
                'type' => 'host',
                'name' => 'hostName'
            ]
        ];
        $this->serviceResourceValidator->validateOrFail($monitoringResource);
        $this->assertTrue(true);
    }
}
