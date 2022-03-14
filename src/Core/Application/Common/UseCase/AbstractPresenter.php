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

namespace Core\Application\Common\UseCase;

use Symfony\Component\HttpFoundation\Response;
use Core\Application\Common\UseCase\PresenterInterface;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;

abstract class AbstractPresenter implements PresenterInterface
{
    /**
     * @var ResponseStatusInterface|null
     */
    protected $responseStatus;

    /**
     * @var mixed
     */
    protected mixed $data;

    /**
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(protected PresenterFormatterInterface $presenterFormatter)
    {
    }

    /**
     * @inheritDoc
     */
    public function present(mixed $data): void
    {
        $this->data = $data;
        $this->presenterFormatter->present($data);
    }

    /**
     * @inheritDoc
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function show(): Response
    {
        return $this->presenterFormatter->show();
    }

    /**
     * @inheritDoc
     */
    public function setResponseStatus(?ResponseStatusInterface $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
        $this->presenterFormatter->present($responseStatus);
    }

    /**
     * @inheritDoc
     */
    public function getResponseStatus(): ?ResponseStatusInterface
    {
        return $this->responseStatus;
    }

    /**
     * @inheritDoc
     */
    public function setResponseHeaders(array $responseHeaders): void
    {
        $this->presenterFormatter->setResponseHeaders($responseHeaders);
    }

    /**
     * @inheritDoc
     */
    public function getResponseHeaders(): array
    {
        return $this->presenterFormatter->getResponseHeaders();
    }
}
