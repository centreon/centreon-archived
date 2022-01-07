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

namespace Core\Infrastructure\Common\Presenter;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\CreatedResponse;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;

class JsonPresenter implements PresenterFormatterInterface
{
    use LoggerTrait;

    /**
     * @var mixed $data
     */
    private $data;

    /**
     * @param mixed $data
     */
    public function present(mixed $data): void
    {
        $this->data = $data;
    }

    /**
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        switch (true) {
            case is_subclass_of($this->data, NotFoundResponse::class, false):
                $this->debug('Data not found. Generating a not found response');
                return new JsonResponse(
                    [
                        'code' => JsonResponse::HTTP_NOT_FOUND,
                        'message' => $this->data->getMessage()
                    ],
                    JsonResponse::HTTP_NOT_FOUND
                );
            case is_subclass_of($this->data, ErrorResponse::class, false):
                $this->debug('Data error. Generating an error response');
                return new JsonResponse(
                    [
                        'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => $this->data->getMessage()
                    ],
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                );
            case is_subclass_of($this->data, CreatedResponse::class, false):
                return new JsonResponse(
                    null,
                    JsonResponse::HTTP_CREATED
                );
            case is_subclass_of($this->data, NoContentResponse::class, false):
                return new JsonResponse(
                    null,
                    JsonResponse::HTTP_NO_CONTENT
                );
            default:
                return new JsonResponse($this->data, JsonResponse::HTTP_OK);
        }
    }
}
