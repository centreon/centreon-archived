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

use Centreon\Infrastructure\Monitoring\MonitoringResource\API\v2110\Validator\MetaServiceResourceValidator;
use Centreon\Domain\Common\Assertion\AssertionException;
use PHPUnit\Framework\TestCase;

class MetaServiceResourceValidatorTest extends TestCase
{
    /**
     * @var MetaServiceResourceValidator
     */
    protected $metaServiceResourceValidator;

    protected function setUp(): void
    {
        $this->metaServiceResourceValidator = new MetaServiceResourceValidator();
    }

    /**
     * test Parent not null for MetaService Monitoring Resource validation
     */
    public function testParentNotNull(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'metaservice',
            'name' => 'metaName',
            'parent' => [
                'id' => 1,
                'name' => 'MetaMetaServiceName',
                'type' => 'MetaMetaService'
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::null(
                'resource::parent'
            )->getMessage()
        );
        $this->metaServiceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Missing mandatory property of Host Monitoring Resource definition
     */
    public function testMissingMandatoryProperty(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'metaservice',
            'parent' => null
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::keyExists(
                'resource::name'
            )->getMessage()
        );
        $this->metaServiceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Wrong Resource Type sent for Host Monitoring Resource
     */
    public function testWrongResourceType(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'host',
            'name' => 'metaName',
            'parent' => null,
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::eq(
                'resource::type'
            )->getMessage()
        );
        $this->metaServiceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Wrong property type sent for Host Monitoring Resource (id)
     */
    public function testWrongPropertyIntegerType(): void
    {
        $monitoringResource = [
            'id' => '10',
            'type' => 'metaservice',
            'name' => 'hostName',
            'parent' => null,
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::integer(
                'resource::id'
            )->getMessage()
        );
        $this->metaServiceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test Wrong property type sent for Host Monitoring Resource (type)
     */
    public function testWrongPropertyIntegerString(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'metaservice',
            'name' => 10,
            'parent' => null,
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::string(
                'resource::name'
            )->getMessage()
        );
        $this->metaServiceResourceValidator->validateOrFail($monitoringResource);
    }

    /**
     * test ValidateOrFail successful
     */
    public function testValidateOrFailSuccess(): void
    {
        $monitoringResource = [
            'id' => 10,
            'type' => 'metaservice',
            'name' => 'metaName',
            'parent' => null
        ];
        $this->metaServiceResourceValidator->validateOrFail($monitoringResource);
        $this->assertTrue(true);
    }
}
