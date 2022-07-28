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

use Symfony\Component\HttpFoundation\JsonResponse;
use Core\Application\Common\UseCase\BodyResponseInterface;
use Core\Application\Common\UseCase\ResponseStatusInterface;

abstract class AbstractPresenter
{
    /**
     * @var array<string, string>
     */
    protected array $responseHeaders = [];

    /**
     * @param array<string, string> $responseHeaders
     */
    public function setResponseHeaders(array $responseHeaders): void
    {
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * @return array<string, string>
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
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

    /**
     * Generates json response with error message and http code
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function generateJsonErrorResponse(mixed $data, int $code): JsonResponse
    {
        $errorData = $this->formatErrorContent($data, $code);

        return $this->generateJsonResponse($errorData, $code);
    }

    /**
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function generateJsonResponse(mixed $data, int $code): JsonResponse
    {
        if (is_a($data, \Generator::class)) {
            $data = iterator_to_array($data);
        }
        return new JsonResponse($data, $code, $this->responseHeaders);
    }
}
