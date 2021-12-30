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

namespace Tests\Core\Application\RealTime\UseCase\FindHost;

use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostPresenterInterface;
use Core\Application\RealTime\UseCase\FindHost\FindHostResponse;
use Symfony\Component\HttpFoundation\Response;

class FindHostPresenterStub implements FindHostPresenterInterface
{
    /**
     * @var FindHostResponse
     */
    public $response;

    /**
     * @var ResponseStatusInterface|null
     */
    private $responseStatus;

    /**
     * @return Response
     */
    public function show(): Response
    {
        return new Response();
    }

    /**
     * @param FindHostResponse $response
     * @return void
     */
    public function present(FindHostResponse $response): void
    {
        $this->response = $response;
    }

    /**
     * @param ResponseStatusInterface|null $responseStatus
     * @return void
     */
    public function setResponseStatus(?ResponseStatusInterface $responseStatus): void
    {
        $this->responseStatus = $responseStatus;
    }

    /**
     * @return ResponseStatusInterface|null
     */
    public function getResponseStatus(): ?ResponseStatusInterface
    {
        return $this->responseStatus;
    }
}
