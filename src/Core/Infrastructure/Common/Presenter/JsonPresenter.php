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

use Core\Application\Common\UseCase\ErrorResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;

class JsonPresenter implements PresenterFormatterInterface
{
    /**
     * @var mixed $data
     */
    private $data;

    public function present(mixed $data): void
    {
        $this->data = $data;
    }

    /**
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        if ($this->data instanceof NotFoundResponse) {
            return new JsonResponse(
                [
                    'code' => JsonResponse::HTTP_NOT_FOUND,
                    'message' => $this->data->getMessage()
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
        } elseif ($this->data instanceof ErrorResponse) {
            return new JsonResponse(
                [
                    'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => $this->data->getMessage()
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return new JsonResponse($this->data, JsonResponse::HTTP_OK);
    }
}
