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

namespace Core\Infrastructure\Common\Api;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Contracts\Service\Attribute\Required;

trait HttpUrlTrait
{
    /**
     * @var ServerBag
     */
    private ServerBag $httpServerBag;

    /**
     * @param RequestStack $requestStack
     */
    #[Required]
    public function setHttpServerBag(RequestStack $requestStack): void
    {
        $this->httpServerBag = $requestStack->getCurrentRequest()->server;
    }

    /**
     * Get base URL (example: https://127.0.0.1/centreon)
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        if (! $this->httpServerBag->has('SERVER_NAME')) {
            return '';
        }

        $protocol = $this->httpServerBag->has('HTTPS') && $this->httpServerBag->get('HTTPS') !== 'off'
            ? 'https'
            : 'http';

        $port = null;
        if ($this->httpServerBag->get('SERVER_PORT')) {
            if (
                ($protocol === 'http' && $this->httpServerBag->get('SERVER_PORT') !== '80')
                || ($protocol === 'https' && $this->httpServerBag->get('SERVER_PORT') !== '443')
            ) {
                $port = (int) $this->httpServerBag->get('SERVER_PORT');
            }
        }

        $serverName = $this->httpServerBag->get('SERVER_NAME');

        $baseUri = trim($this->getBaseUri(), '/');

        return rtrim(
            $protocol . '://' . $serverName . ($port !== null ? ':' . $port : '') . '/' . $baseUri,
            '/'
        );
    }

    /**
     * Get base URI (example: /centreon)
     *
     * @return string
     */
    protected function getBaseUri(): string
    {
        $baseUri = '';

        $routeSuffixPatterns = [
            '(api|widgets|modules|include)\/.+',
            'main(\.get)?\.php',
            '(?<!administration\/)authentication\/.+',
        ];

        if (
            $this->httpServerBag->has('REQUEST_URI')
            && preg_match(
                '/^(.+?)\/?(' . implode('|', $routeSuffixPatterns) . ')/',
                $this->httpServerBag->get('REQUEST_URI'),
                $matches,
            )
        ) {
            $baseUri = $matches[1];
        }

        return rtrim($baseUri, '/');
    }
}
