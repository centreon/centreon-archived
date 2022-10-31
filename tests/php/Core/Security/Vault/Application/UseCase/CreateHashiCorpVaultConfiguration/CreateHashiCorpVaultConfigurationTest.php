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

namespace Tests\Core\Security\Vault\Application\UseCase\CreateHashiCorpVaultConfiguration;

use Core\Application\Common\UseCase\CreatedResponse;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\Vault\Application\Repository\ReadVaultConfigurationRepositoryInterface;
use Core\Security\Vault\Application\Repository\WriteVaultConfigurationRepositoryInterface;
use Core\Security\Vault\Application\UseCase\CreateHashiCorpVaultConfiguration\CreateHashiCorpVaultConfiguration;
use Core\Security\Vault\Application\UseCase\CreateHashiCorpVaultConfiguration\CreateHashiCorpVaultConfigurationRequest;
use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;
use Core\Security\Vault\Domain\Model\NewVaultConfiguration;
use Core\Security\Vault\Domain\Model\VaultConfiguration;

beforeEach(function (): void {
    $this->readRepository = $this->createMock(ReadVaultConfigurationRepositoryInterface::class);
    $this->writeRepository = $this->createMock(WriteVaultConfigurationRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
});

it('Should present InvalidArgumentResponse when vault configuration already exists', function (): void {
    $vaultConfiguration = new VaultConfiguration(
        1,
        'myConf',
        NewVaultConfiguration::TYPE_HASHICORP,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId'
    );
    $this->readRepository
        ->expects($this->once())
        ->method('findVaultConfigurationByAddressAndPortAndStorage')
        ->willReturn($vaultConfiguration);

    $presenter = new CreateHashiCorpVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateHashiCorpVaultConfiguration($this->readRepository, $this->writeRepository);

    $createHashiCorpVaultConfigurationRequest = new CreateHashiCorpVaultConfigurationRequest();

    $useCase($presenter, $createHashiCorpVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(InvalidArgumentResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        VaultConfigurationException::configurationExists()->getMessage()
    );
});

it('Should present InvalidArgumentResponse when one parameter is not valid', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('findVaultConfigurationByAddressAndPortAndStorage')
        ->willReturn(null);

    $presenter = new CreateHashiCorpVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateHashiCorpVaultConfiguration($this->readRepository, $this->writeRepository);

    $createVaultConfigurationRequest = new CreateHashiCorpVaultConfigurationRequest();
    $createVaultConfigurationRequest->name = 'myVault';
    $createVaultConfigurationRequest->address = '_.@';
    $createVaultConfigurationRequest->port = 8200;
    $createVaultConfigurationRequest->storage = 'myStorage';
    $createVaultConfigurationRequest->roleId = 'myRole';
    $createVaultConfigurationRequest->secretId = 'myStorage';

    $useCase($presenter, $createVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(InvalidArgumentResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        VaultConfigurationException::invalidParameters(['address'])->getMessage()
    );
});

it('Should present ErrorResponse when an unhandled error occurs', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('findVaultConfigurationByAddressAndPortAndStorage')
        ->willThrowException(new \Exception());

    $presenter = new CreateHashiCorpVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateHashiCorpVaultConfiguration($this->readRepository, $this->writeRepository);

    $createHashiCorpVaultConfigurationRequest = new CreateHashiCorpVaultConfigurationRequest();

    $useCase($presenter, $createHashiCorpVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        VaultConfigurationException::impossibleToCreate()->getMessage()
    );
});

it('Should present CreatedResponse when vault configuration is created with success', function (): void {
    $this->readRepository
        ->expects($this->once())
        ->method('findVaultConfigurationByAddressAndPortAndStorage')
        ->willReturn(null);

    $presenter = new CreateHashiCorpVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateHashiCorpVaultConfiguration($this->readRepository, $this->writeRepository);

    $createHashiCorpVaultConfigurationRequest = new CreateHashiCorpVaultConfigurationRequest();
    $createHashiCorpVaultConfigurationRequest->name = 'myVault';
    $createHashiCorpVaultConfigurationRequest->address = '127.0.0.1';
    $createHashiCorpVaultConfigurationRequest->port = 8200;
    $createHashiCorpVaultConfigurationRequest->storage = 'myStorage';
    $createHashiCorpVaultConfigurationRequest->roleId = 'myRoleId';
    $createHashiCorpVaultConfigurationRequest->secretId = 'mySecretId';

    $useCase($presenter, $createHashiCorpVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(CreatedResponse::class);
});
