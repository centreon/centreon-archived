<?php

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
        $newApiAuthentication->contact = $response->getApiAuthentication()['contact'];
        $newApiAuthentication->security = $response->getApiAuthentication()['security'];

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
