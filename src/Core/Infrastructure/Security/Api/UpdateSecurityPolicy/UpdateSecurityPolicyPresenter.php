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

namespace Core\Infrastructure\Security\Api\FindSecurityPolicy;

use Symfony\Component\HttpFoundation\Response;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Security\UseCase\UpdateSecurityPolicy\UpdateSecurityPolicyPresenterInterface;

class UpdateSecurityPolicyPresenter implements UpdateSecurityPolicyPresenterInterface
{
    /**
     * @var ResponseStatusInterface|null
     */
    private $responseStatus;

    /**
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(private PresenterFormatterInterface $presenterFormatter)
    {
    }

    /**
     * @inheritDoc
     */
    public function present(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function show(): Response
    {
        if ($this->getResponseStatus() !== null) {
            $this->presenterFormatter->present($this->getResponseStatus());
        }
        return $this->presenterFormatter->show();
    }

    /**
     * @inheritDoc
     */
    public function setResponseStatus(?ResponseStatusInterface $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
    }

    /**
     * @inheritDoc
     */
    public function getResponseStatus(): ?ResponseStatusInterface
    {
        return $this->responseStatus;
    }
}
