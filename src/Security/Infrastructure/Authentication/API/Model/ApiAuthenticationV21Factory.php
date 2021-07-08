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

namespace Security\Infrastructure\Authentication\API\Model;

use Centreon\Domain\Authentication\UseCase\AuthenticateApiResponse;

class ApiAuthenticationV21Factory
{
    /**
     * @param AuthenticateApiResponse $response
     * @return \stdClass
     */
    public static function createFromResponse(AuthenticateApiResponse $response): \stdClass
    {
        $newApiAuthentication = self::createEmptyClass();
        $newApiAuthentication->contact['id'] = (int) $response->getApiAuthentication()['contact']['id'];
        $newApiAuthentication->contact['name'] = $response->getApiAuthentication()['contact']['name'];
        $newApiAuthentication->contact['alias'] = $response->getApiAuthentication()['contact']['alias'];
        $newApiAuthentication->contact['email'] = $response->getApiAuthentication()['contact']['email'];
        $newApiAuthentication->contact['is_admin'] = (bool) $response->getApiAuthentication()['contact']['is_admin'];
        $newApiAuthentication->security['token'] = $response->getApiAuthentication()['security']['token'];

        return $newApiAuthentication;
    }

    /**
     * @return \stdClass
     */
    private static function createEmptyClass(): \stdClass
    {
        return new class extends \stdClass {
            /**
             * @var array<string,mixed>
             */
            public $contact;

            /**
             * @var array<string,string>
             */
            public $security;
        };
    }
}
