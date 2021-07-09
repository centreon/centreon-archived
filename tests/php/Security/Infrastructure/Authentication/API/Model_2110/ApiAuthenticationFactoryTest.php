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
declare(strict_types=1);


namespace Tests\Security\Infrastructure\Authentication\API\Model_2110;

use Security\Infrastructure\Authentication\API\Model_2110\ApiAuthenticationFactory;
use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;
use Centreon\Domain\Contact\Contact;
use PHPUnit\Framework\TestCase;

/**
 * @package Tests\Security\Infrastructure\Authentication\API\Model_2110
 */
class ApiAuthenticationFactoryTest extends TestCase
{
    /**
     * @var array<string, string|int> $rdbData
     */
    private $responseData;

    /**
     * @var Contact $contact
     */
    private $contact;

    protected function setUp(): void
    {
        $this->responseData = [
            'contact' => [
                'id' => 1,
                'name' => 'contact_name_1',
                'alias' => 'contact_alias_1',
                'email' => 'root@localhost',
                'is_admin' => true,
            ],
            'security' => [
                'token' => 'abc123'
            ],
        ];

        $this->contact = (new Contact())
            ->setId(1)
            ->setName('contact_name_1')
            ->setAlias('contact_alias_1')
            ->setEmail('root@localhost')
            ->setAdmin(true);
    }

    /**
     * Tests model is properly created
     */
    public function testCreateFromResponseWithAllProperties()
    {
        $response = new AuthenticateApiResponse();
        $response->setApiAuthentication($this->contact, 'abc123');
        $authentication = ApiAuthenticationFactory::createFromResponse($response);

        $this->assertEqualsCanonicalizing($this->responseData['contact'], $authentication->contact);
        $this->assertEqualsCanonicalizing($this->responseData['security'], $authentication->security);
    }
}
