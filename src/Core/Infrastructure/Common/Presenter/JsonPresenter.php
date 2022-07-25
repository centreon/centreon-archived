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

namespace Core\Infrastructure\Common\Presenter;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\BodyResponseInterface;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Application\Common\UseCase\CreatedResponse;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Application\Common\UseCase\PaymentRequiredResponse;
use Core\Application\Common\UseCase\ForbiddenResponse;
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
    private mixed $data = null;

    /**
     * @var mixed[] $responseHeaders
     */
    private array $responseHeaders = [];

    /**
     * @inheritDoc
     */
    public function setResponseHeaders(array $responseHeaders): void
    {
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * @inheritDoc
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * @inheritDoc
     */
    public function present(mixed $data): void
    {
        $this->data = $data;
    }

    public function show(): JsonResponse
    {
        switch (true) {
            case is_a($this->data, NotFoundResponse::class, false):
                $this->debug('Data not found. Generating a not found response');
                return new JsonResponse(
                    $this->formatErrorContent($this->data, JsonResponse::HTTP_NOT_FOUND),
                    JsonResponse::HTTP_NOT_FOUND,
                    $this->responseHeaders,
                );
            case is_a($this->data, ErrorResponse::class, false):
                $this->debug('Data error. Generating an error response');
                return new JsonResponse(
                    $this->formatErrorContent($this->data, JsonResponse::HTTP_INTERNAL_SERVER_ERROR),
                    JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    $this->responseHeaders,
                );
            case is_a($this->data, InvalidArgumentResponse::class, false):
                $this->debug('Invalid argument. Generating an error response');
                return new JsonResponse(
                    $this->formatErrorContent($this->data, JsonResponse::HTTP_BAD_REQUEST),
                    JsonResponse::HTTP_BAD_REQUEST,
                    $this->responseHeaders,
                );
            case is_a($this->data, UnauthorizedResponse::class, false):
                $this->debug('Unauthorized. Generating an error response');
                return new JsonResponse(
                    $this->formatErrorContent($this->data, JsonResponse::HTTP_UNAUTHORIZED),
                    JsonResponse::HTTP_UNAUTHORIZED,
                    $this->responseHeaders,
                );
            case is_a($this->data, PaymentRequiredResponse::class, false):
                $this->debug('Payment required. Generating an error response');
                return new JsonResponse(
                    $this->formatErrorContent($this->data, JsonResponse::HTTP_PAYMENT_REQUIRED),
                    JsonResponse::HTTP_PAYMENT_REQUIRED,
                    $this->responseHeaders,
                );
            case is_a($this->data, ForbiddenResponse::class, false):
                $this->debug('Forbidden. Generating an error response');
                return new JsonResponse(
                    $this->formatErrorContent($this->data, JsonResponse::HTTP_FORBIDDEN),
                    JsonResponse::HTTP_FORBIDDEN,
                    $this->responseHeaders,
                );
            case is_a($this->data, CreatedResponse::class, false):
                return new JsonResponse(
                    null,
                    JsonResponse::HTTP_CREATED,
                    $this->responseHeaders,
                );
            case is_a($this->data, NoContentResponse::class, false):
                return new JsonResponse(
                    null,
                    JsonResponse::HTTP_NO_CONTENT,
                    $this->responseHeaders,
                );
            default:
                return new JsonResponse(
                    $this->data,
                    JsonResponse::HTTP_OK,
                    $this->responseHeaders,
                );
        }
    }

    /**
     * Format content on error
     *
     * @return mixed[]|null
     */
    private function formatErrorContent(mixed $data, int $code): ?array
    {
        $content = null;

        if (is_a($data, ResponseStatusInterface::class)) {
            $content = [
                'code' => $code,
                'message' => $data->getMessage(),
            ];
            if (is_a($data, BodyResponseInterface::class)) {
                $content = array_merge($content, $data->getBody());
            }
        }

        return $content;
    }
}
