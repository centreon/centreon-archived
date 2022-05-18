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

namespace Tests\Core\Infrastructure\Common\Api;

use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Symfony\Component\HttpFoundation\ServerBag;

uses(HttpUrlTrait::class);

beforeEach(function () {
    $this->httpServerBag = new ServerBag([]);
});

it('returns empty base url when there is no current request', function () {
    expect($this->getBaseUrl())->toBe('');
});

it('returns empty base uri when there is no current request', function () {
    expect($this->getBaseUri())->toBe('');
});

it(
    'formats properly base uri',
    function (
        $requestUri,
        $baseUri
    ) {
        $this->httpServerBag->replace(['REQUEST_URI' => $requestUri]);

        expect($this->getBaseUri())->toBe($baseUri);
    }
)->with([
    ['/authentication/providers/configurations/local', ''],
    ['/monitoring/authentication/providers/configurations/local', '/monitoring'],
    ['/monitoring/centreon/authentication/providers/configurations/local', '/monitoring/centreon'],
    ['/administration/authentication/providers/local', ''],
    ['/my-monitoring/api/v22.04/administration/authentication/providers/local', '/my-monitoring'],
    ['/api/latest/monitoring/resources', ''],
    ['/centreon/api/latest/monitoring/resources', '/centreon'],
    ['/monitoring/authentication/logout', '/monitoring'],
]);

it(
    'formats properly base url',
    function (
        $requestParameters,
        $baseUrl
    ) {
        $this->httpServerBag->replace([
            'HTTPS' => $requestParameters[0],
            'SERVER_NAME' => $requestParameters[1],
            'SERVER_PORT' => $requestParameters[2],
            'REQUEST_URI' => $requestParameters[3],
        ]);

        expect($this->getBaseUrl())->toBe($baseUrl);
    }
)->with([
    [
        [
            'off',
            '127.0.0.1',
            '8080',
            '/centreon/api/latest/test',
        ],
        'http://127.0.0.1:8080/centreon'
    ],
    [
        [
            'on',
            'my.monitoring',
            '4443',
            '/api/latest/test',
        ],
        'https://my.monitoring:4443'
    ],
    [
        [
            'on',
            'test',
            '',
            '/api/latest/test',
        ],
        'https://test'
    ],
    [
        [
            'off',
            '192.168.0.1',
            '',
            '/monitoring/centreon/authentication/logout',
        ],
        'http://192.168.0.1/monitoring/centreon'
    ],
]);
