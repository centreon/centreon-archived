<?php
/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
 * See the License for the spceific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Security\Authentication\Infrastructure\Provider;

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Authentication\Application\Provider\ProviderInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\LocalProviderInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Model\LocalProvider;

final class Local implements ProviderInterface
{
    use LoggerTrait;

    /**
     * @param LocalProviderInterface $provider
     * @param ProviderServiceInterface $providerService
     */
    public function __construct(private LocalProviderInterface $provider, private ProviderServiceInterface $providerService)
    {
    }

    /**
     * @param LoginRequest $request
     * @return void
     * @throws ProviderException
     */
    public function authenticateOrFail(LoginRequest $request): void
    {
        $this->debug(
            '[AUTHENTICATE] Authentication using provider',
            ['provider_name' => LocalProvider::NAME]
        );

        $this->provider->authenticateOrFail([
            'login' => $request->getUsername(),
            'password' => $request->getPassword()
        ]);
    }

//    /**
//     * Find a provider or throw an Exception.
//     *
//     * @param string $providerConfigurationName
//     * @return ProviderInterface
//     * @throws ProviderException
//     */
//    private function findProviderOrFail(string $providerConfigurationName): ProviderInterface
//    {
//        $this->debug(
//            '[AUTHENTICATE] Beginning authentication on provider',
//            ['provider_name' => $providerConfigurationName]
//        );
//
//        if ($authenticationProvider === null) {
//            throw ProviderException::providerConfigurationNotFound(
//                $providerConfigurationName
//            );
//        }
//
//        return $authenticationProvider;
//    }

    /**
     * @param LoginRequest $request
     * @return ContactInterface
     */
    public function findUserOrFail(LoginRequest $request): ContactInterface
    {
        $this->info('[AUTHENTICATE] Retrieving user informations from provider');
        $providerUser = $this->provider->getUser();
        if ($providerUser === null) {
            $this->critical(
                '[AUTHENTICATE] No contact could be found from provider',
                ['provider_name' => $this->provider->getConfiguration()->getName()]
            );
            throw LegacyAuthenticationException::userNotFound(); // FIXME
        }

        return $providerUser;
    }
}