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
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Core\Infrastructure\Security\Api\FindProviderConfigurations;

use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindProviderConfigurationsResponse;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindProviderConfigurationsPresenterInterface;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;

class FindProviderConfigurationsPresenter extends AbstractPresenter implements FindProviderConfigurationsPresenterInterface
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        private RequestParametersInterface $requestParameters,
        protected PresenterFormatterInterface $presenterFormatter,
    ) {
    }

    /**
     * {@inheritDoc}
     * @param FindProviderConfigurationsResponse $response
     */
    public function present(mixed $response): void
    {
        $formattedResponse = [];
        foreach ($response->configurations as $configuration) {
            if ($configuration['type'] === 'local') {
                $formattedResponse[] = $configuration;
            } elseif ($configuration['type'] === 'openid') {
                $formattedResponse[] = [
                    'id' => 2,
                    'type' => 'openid',
                    'name' => 'openid',
                    'authentication_uri' => $configuration['base_url'] . '/' . $configuration['authorization_endpoint']
                        . '?'
                        . http_build_query(
                            [
                                'client_id' => $configuration['client_id'],
                                'response_type' => 'code',
                                'redirect_uri' => $this->getBaseUri(),
                                'state' => uniqid(),
                            ],
                        ),
                    'is_active' => $configuration['is_active'],
                    'is_forced' => $configuration['is_forced'],
                ];
            }
        }

        $this->presenterFormatter->present($response->configurations);
    }

   /**
    * Get base URI
    *
    * @return string
    */
   private function getBaseUri(): string
   {
       $baseUri = '';

       if (
           isset($_SERVER['REQUEST_URI'])
           && preg_match(
               '/^(.+)\/((api|widgets|modules|include)\/|main(\.get)?\.php).+/',
               $_SERVER['REQUEST_URI'],
               $matches
           )
       ) {
           $baseUri = $matches[1];
       }

       return $baseUri;
   }
}
