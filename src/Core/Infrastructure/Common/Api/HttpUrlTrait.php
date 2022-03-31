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

trait HttpUrlTrait
{
    /**
     * Get base URL (example: https://127.0.0.1/centreon)
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';

        $port = null;
        if (isset($_SERVER['SERVER_PORT'])) {
            if (
                ($protocol === 'http' && $_SERVER['SERVER_PORT'] !== '80')
                || ($protocol === 'https' && $_SERVER['SERVER_PORT'] !== '443')
            ) {
                $port = (int) $_SERVER['SERVER_PORT'];
            }
        }

        $serverName = $_SERVER['SERVER_NAME'];

        $baseUri = $this->getBaseUri();

        return $protocol . '://'
            . $serverName . ($port !== null ? ':' . $port : '')
            . '/' . ltrim($baseUri, '/');
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
            '(?<!administration\/)authentication\/providers\/configurations',
        ];

        if (
            isset($_SERVER['REQUEST_URI'])
            && preg_match(
                '/^(.+?)\/(' . implode('|', $routeSuffixPatterns) . ')/',
                $_SERVER['REQUEST_URI'],
                $matches
            )
        ) {
            $baseUri = $matches[1];
        }

        return $baseUri;
    }
}
