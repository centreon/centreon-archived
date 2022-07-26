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

namespace Core\Security\ProviderConfiguration\Infrastructure\Api\FindProviderConfigurations;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\ProviderConfiguration\Infrastructure\Api\FindProviderConfigurations\ProviderPresenter\{
    ProviderPresenterInterface
};
use Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations\{
    FindProviderConfigurationsPresenterInterface
};

class FindProviderConfigurationsPresenter extends AbstractPresenter implements
    FindProviderConfigurationsPresenterInterface
{
    use HttpUrlTrait;

    /**
     * @var ProviderPresenterInterface[]
     */
    private $providerPresenters;

    /**
     * @param \Traversable<ProviderPresenterInterface> $presenters
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        \Traversable $presenters,
        protected PresenterFormatterInterface $presenterFormatter
    ) {
        if (iterator_count($presenters) === 0) {
            throw new NotFoundException(_('No provider presenters could be found'));
        }
        $this->providerPresenters = iterator_to_array($presenters);
    }

    /**
     * {@inheritDoc}
     * @param mixed[] $data
     */
    public function present(mixed $data): void
    {
        $formattedResponse = [];

        foreach ($data as $response) {
            foreach ($this->providerPresenters as $presenterProvider) {
                if ($presenterProvider->isValidFor($response)) {
                    $formattedResponse[] = $presenterProvider->present($response);
                }
            }
        }
        $this->presenterFormatter->present($formattedResponse);
    }
}
