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

namespace Centreon\Domain\Authentication\UseCase;

use Centreon\Domain\Contact\Interfaces\ContactInterface;

class AuthenticateApiResponse
{
    /**
     * @var array<string,array<string,mixed>>
     */
    private $apiAuthentication = [];

    /**
     * Return the redirection URI.
     *
     * @return array<string,array<string,mixed>>
     */
    public function getApiAuthentication(): array
    {
        return $this->apiAuthentication;
    }

    /**
     * @param ContactInterface $contact
     * @param string $token
     */
    public function setApiAuthentication(ContactInterface $contact, string $token): void
    {
        $this->apiAuthentication = [
            'contact' => [
                'id' => $contact->getId(),
                'name' => $contact->getName(),
                'alias' => $contact->getAlias(),
                'email' => $contact->getEmail(),
                'is_admin' => $contact->isAdmin(),
            ],
            'security' => [
                'token' => $token,
            ]
        ];
    }
}
