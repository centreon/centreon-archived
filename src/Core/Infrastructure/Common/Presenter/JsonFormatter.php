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
use Core\Application\Common\UseCase\CreatedResponse;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Application\Common\UseCase\UnauthorizedResponse;
use Core\Application\Common\UseCase\PaymentRequiredResponse;
use Core\Application\Common\UseCase\ForbiddenResponse;
use Core\Application\Common\UseCase\NoContentResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Core\Application\Common\UseCase\NotFoundResponse;

class JsonFormatter extends AbstractFormatter implements PresenterFormatterInterface
{
    use LoggerTrait;

    /**
     * @inheritDoc
     */
    public function format(mixed $data): JsonResponse
    {
        switch (true) {
            case is_a($data, NotFoundResponse::class, false):
                $this->debug('Data not found. Generating a not found response');
                return $this->generateJsonErrorResponse($data, JsonResponse::HTTP_NOT_FOUND);
            case is_a($data, ErrorResponse::class, false):
                $this->debug('Data error. Generating an error response');
                return $this->generateJsonErrorResponse($data, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            case is_a($data, InvalidArgumentResponse::class, false):
                $this->debug('Invalid argument. Generating an error response');
                return $this->generateJsonErrorResponse($data, JsonResponse::HTTP_BAD_REQUEST);
            case is_a($data, UnauthorizedResponse::class, false):
                $this->debug('Unauthorized. Generating an error response');
                return $this->generateJsonErrorResponse($data, JsonResponse::HTTP_UNAUTHORIZED);
            case is_a($data, PaymentRequiredResponse::class, false):
                $this->debug('Payment required. Generating an error response');
                return $this->generateJsonErrorResponse($data, JsonResponse::HTTP_PAYMENT_REQUIRED);
            case is_a($data, ForbiddenResponse::class, false):
                $this->debug('Forbidden. Generating an error response');
                return $this->generateJsonErrorResponse($data, JsonResponse::HTTP_FORBIDDEN);
            case is_a($data, CreatedResponse::class, false):
                return $this->generateJsonResponse(null, JsonResponse::HTTP_CREATED);
            case is_a($data, NoContentResponse::class, false):
                return $this->generateJsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
            default:
                return $this->generateJsonResponse($data, JsonResponse::HTTP_OK);
        }
    }

    /**
     * Generates json response with error message and http code
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    private function generateJsonErrorResponse(mixed $data, int $code): JsonResponse
    {
        $errorData = $this->formatErrorContent($data, $code);

        return $this->generateJsonResponse($errorData, $code);
    }

    /**
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    private function generateJsonResponse(mixed $data, int $code): JsonResponse
    {
        if (is_a($data, \Generator::class)) {
            $data = iterator_to_array($data);
        }
        return new JsonResponse($data, $code, $this->responseHeaders);
    }

    /**
     * Format content on error
     *
     * @param mixed $data
     * @param integer $code
     * @return mixed[]|null
     */
    protected function formatErrorContent(mixed $data, int $code): ?array
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
