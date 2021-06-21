<?php

namespace Security\Infrastructure\Authentication\API\Model;

use Centreon\Domain\Authentication\UseCase\RedirectResponse;

class ProviderRedirectionV21Factory
{

    public static function createFromResponse(RedirectResponse $response): \stdClass
    {
        $newRedirect = self::createEmptyClass();
        $newRedirect->authenticationUri = $response->getRedirectionUri();
        return $newRedirect;
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
            public $authenticationUri;
        };
    }
}
