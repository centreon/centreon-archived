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
 *  For more information : contact@centreon.com
 */

declare(strict_types=1);

namespace Tests\Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration;

use Centreon\Domain\Repository\RepositoryException;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\NotFoundResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\ACLConditionsException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\InvalidEndpointException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\ACLConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Endpoint;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\WebSSOConfiguration;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\WebSSO\UseCase\FindWebSSOConfiguration\{
    FindWebSSOConfiguration,
    FindWebSSOConfigurationResponse
};

beforeEach(function () {
    $this->custom_relative_url = '/info';
    $this->custom_url = 'https://domain.com/info';
});

it('should return a default ACLConditions instance', function () {
    $aclConditions = new ACLConditions(
        false,
        false,
        '',
        new Endpoint(),
        []
    );

    expect($aclConditions->isEnabled())->toBe(false)
        ->and($aclConditions->onlyFirstRoleIsApplied())->toBe(false)
        ->and($aclConditions->getAttributePath())->toHaveLength(0)
        ->and($aclConditions->getEndpoint())->toBeInstanceOf(Endpoint::class)
        ->and($aclConditions->getRelations())->toBeArray()->toHaveLength(0);
});

it('should throw an exception for missing parameter attribute_path', function () {
    (new ACLConditions(
        true,
        false,
        '',
        new Endpoint(),
        []
    ));
})->throws(
    ACLConditionsException::class,
    ACLConditionsException::missingFields(['attribute_path'])->getMessage()
);

it('it should throw an exception for missing parameter endpoint', function () {
    (new ACLConditions(
        true,
        false,
        '',
        new Endpoint(),
        []
    ));
})->throws(
    ACLConditionsException::class,
    ACLConditionsException::missingFields(['attribute_path'])->getMessage()
);

it(
    'it should throw an exception for missing parameter relations when applyOnlyFirstRole is enabled',
    function () {
        (new ACLConditions(
            true,
            true,
            'info.path.role',
            new Endpoint(),
            []
        ));
    }
)->throws(
    ACLConditionsException::class,
    ACLConditionsException::missingFields(['relations'])->getMessage()
);

it('it should throw an exception for all missing parameters', function () {
    (new ACLConditions(
        true,
        true,
        '',
        new Endpoint(),
        []
    ));
})->throws(
    ACLConditionsException::class,
    ACLConditionsException::missingFields(['attribute_path', 'relations'])->getMessage()
);
