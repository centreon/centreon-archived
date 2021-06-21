<?php

namespace Security\Infrastructure\Authentication\API\Model;

use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurationsResponse;

class ProvidersConfigurationsV21Factory
{

    /**
     * @return \stdClass[]
     */
    public static function createFromResponse(FindProvidersConfigurationsResponse $response): array
    {
        $providersConfigurations = [];
        foreach ($response->getProvidersConfigurations() as $providerConfiguration) {
            $newProviderConfiguration = self::createEmptyClass();
            $newProviderConfiguration->id = $providerConfiguration['id'];
            $newProviderConfiguration->type = $providerConfiguration['type'];
            $newProviderConfiguration->name = $providerConfiguration['name'];
            $newProviderConfiguration->centreonBaseUri = $providerConfiguration['centreonBaseUri'];
            $newProviderConfiguration->isForced = $providerConfiguration['isForced'];
            $newProviderConfiguration->isActive = $providerConfiguration['isActive'];
            $newProviderConfiguration->authenticationUri = $providerConfiguration['authenticationUri'];

            $providersConfigurations[] = $newProviderConfiguration;
        }
        return $providersConfigurations;
    }
    /**
     * @return \stdClass
     */
    private static function createEmptyClass(): \stdClass
    {
        return new class extends \stdClass {
            /**
             * @var int|null
             */
            public $id;

            /**
             * @var string
             */
            public $type;

            /**
             * @var string
             */
            public $name;

            /**
             * @var string
             */
            public $centreonBaseUri;

            /**
             * @var string
             */
            public $authenticationUri;

            /**
             * @var bool
             */
            public $isActive;

            /**
             * @var bool
             */
            public $isForced;
        };
    }
}
