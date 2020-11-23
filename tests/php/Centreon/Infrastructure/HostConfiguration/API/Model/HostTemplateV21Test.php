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

namespace Tests\Centreon\Infrastructure\HostConfiguration\API\Model;

use Centreon\Domain\HostConfiguration\UseCase\v2_1\FindHostTemplatesResponse;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostTemplateV21;
use PHPUnit\Framework\TestCase;
use Tests\Centreon\Domain\HostConfiguration\Model\HostTemplateTest;

class HostTemplateV21Test extends TestCase
{
    private $hostTemplate;

    protected function setUp()
    {
        $this->hostTemplate = HostTemplateTest::createEntity();
    }

    /**
     * We check the format sent for the API request (v2.1)
     */
    public function testCreateFromResponse(): void
    {
        $response = new FindHostTemplatesResponse();
        $response->setHostTemplates([$this->hostTemplate]);
        $hostTemplateV21 = HostTemplateV21::createFromResponse($response)[0];

        $hostTemplates = $response->getHostTemplates();
        // for this case the test is very simple
        $reflection = new \ReflectionClass(HostTemplateV21::class);
        $properties = $reflection->getProperties();
        $numberOfProperties = 0; // the variable properties cannot be used with the count() function.
        /**
         * @var $propertiesConcordanceTable array<string, string>
         * Keys are the property names of the HostTempleV21 entity.
         * Values are the property names of the FindHostTemplateResponse entity.
         */
        $propertiesConcordanceTable = ['parents' => 'parent_ids'];
        foreach ($properties as $reflectionProperty) {
            $numberOfProperties++;
            $propertyName = $this->convertCamelCaseToSnakeCase($reflectionProperty->getName());
            if (array_key_exists($propertyName, $propertiesConcordanceTable)) {
                $propertyName = $propertiesConcordanceTable[$propertyName];
            }
            $this->assertEquals(
                $hostTemplates[0][$propertyName],
                $reflectionProperty->getValue($hostTemplateV21),
                'Failed asserting the property ' . $reflectionProperty->getName()
            );
        }
        $this->assertEquals(29, $numberOfProperties);
    }

    /**
     * Converts a string from camel case format to snake case format.
     *
     * @param string $camelCasePropertyName Property name in camel case format
     * @return string Property name in snake case format
     */
    private function convertCamelCaseToSnakeCase(string $camelCasePropertyName): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($camelCasePropertyName)));
    }
}
