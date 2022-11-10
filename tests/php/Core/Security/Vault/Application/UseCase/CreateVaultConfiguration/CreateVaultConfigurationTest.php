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

namespace Tests\Core\Security\Vault\Application\UseCase\CreateVaultConfiguration;

use Assert\InvalidArgumentException;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Application\Common\UseCase\InvalidArgumentResponse;
use Core\Application\Common\UseCase\{CreatedResponse, ErrorResponse};
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Security\Vault\Application\Repository\{
    ReadVaultConfigurationRepositoryInterface,
    ReadVaultRepositoryInterface,
    WriteVaultConfigurationRepositoryInterface
};
use Core\Security\Vault\Application\UseCase\CreateVaultConfiguration\{
    CreateVaultConfiguration,
    CreateVaultConfigurationRequest,
    NewVaultConfigurationFactory
};
use Core\Security\Vault\Domain\Exceptions\VaultConfigurationException;
use Core\Security\Vault\Domain\Exceptions\VaultException;
use Core\Security\Vault\Domain\Model\{NewVaultConfiguration, Vault, VaultConfiguration};

beforeEach(function (): void {
    $this->readVaultConfigurationRepository = $this->createMock(ReadVaultConfigurationRepositoryInterface::class);
    $this->writeVaultConfigurationRepository = $this->createMock(WriteVaultConfigurationRepositoryInterface::class);
    $this->readVaultRepository = $this->createMock(ReadVaultRepositoryInterface::class);
    $this->presenterFormatter = $this->createMock(PresenterFormatterInterface::class);
    $this->factory = $this->createMock(NewVaultConfigurationFactory::class);
});

it('should present InvalidArgumentResponse when vault configuration already exists', function (): void {
    $vault = new Vault(1, 'myVaultProvider');

    $vaultConfiguration = new VaultConfiguration(
        1,
        'myConf',
        $vault,
        '127.0.0.1',
        8200,
        'myStorage',
        'myRoleId',
        'mySecretId',
        'mySalt'
    );
    $this->readVaultConfigurationRepository
        ->expects($this->once())
        ->method('findByAddressAndPortAndStorage')
        ->willReturn($vaultConfiguration);

    $presenter = new CreateVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateVaultConfiguration(
        $this->readVaultConfigurationRepository,
        $this->writeVaultConfigurationRepository,
        $this->readVaultRepository,
        $this->factory
    );

    $createVaultConfigurationRequest = new CreateVaultConfigurationRequest();

    $useCase($presenter, $createVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(InvalidArgumentResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        VaultConfigurationException::configurationExists()->getMessage()
    );
});

it('should present InvalidArgumentResponse when one parameter is not valid', function (): void {
    $this->readVaultConfigurationRepository
        ->expects($this->once())
        ->method('findByAddressAndPortAndStorage')
        ->willReturn(null);

    $vault = new Vault(1, 'myVaultProvider');
    $this->readVaultRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($vault);

    $presenter = new CreateVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateVaultConfiguration(
        $this->readVaultConfigurationRepository,
        $this->writeVaultConfigurationRepository,
        $this->readVaultRepository,
        $this->factory
    );

    $createVaultConfigurationRequest = new CreateVaultConfigurationRequest();
    $createVaultConfigurationRequest->name = '';
    $createVaultConfigurationRequest->typeId = 1;
    $createVaultConfigurationRequest->address = '127.0.0.1';
    $createVaultConfigurationRequest->port = 8200;
    $createVaultConfigurationRequest->storage = 'myStorage';
    $createVaultConfigurationRequest->roleId = 'myRole';
    $createVaultConfigurationRequest->secretId = 'mySecretId';

    $this->factory
        ->expects($this->once())
        ->method('create')
        ->with($createVaultConfigurationRequest)
        ->willThrowException(
            new InvalidArgumentException(
                AssertionException::notEmpty('NewVaultConfiguration::address')->getMessage(),
                1
            )
        );

    $useCase($presenter, $createVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(InvalidArgumentResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        AssertionException::notEmpty('NewVaultConfiguration::address')->getMessage()
    );
});

it('should present InvalidArgumentResponse when vault provider does not exist', function (): void {
    $this->readVaultConfigurationRepository
        ->expects($this->once())
        ->method('findByAddressAndPortAndStorage')
        ->willReturn(null);

    $this->readVaultRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn(null);

    $presenter = new CreateVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateVaultConfiguration(
        $this->readVaultConfigurationRepository,
        $this->writeVaultConfigurationRepository,
        $this->readVaultRepository,
        $this->factory
    );

    $createVaultConfigurationRequest = new CreateVaultConfigurationRequest();
    $createVaultConfigurationRequest->name = 'myVault';
    $createVaultConfigurationRequest->typeId = 3;
    $createVaultConfigurationRequest->address = '127.0.0.1';
    $createVaultConfigurationRequest->port = 8200;
    $createVaultConfigurationRequest->storage = 'myStorage';
    $createVaultConfigurationRequest->roleId = 'myRole';
    $createVaultConfigurationRequest->secretId = 'mySecretId';

    $useCase($presenter, $createVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(InvalidArgumentResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        VaultException::providerDoesNotExist()->getMessage()
    );
});

it('should present ErrorResponse when an unhandled error occurs', function (): void {
    $this->readVaultConfigurationRepository
        ->expects($this->once())
        ->method('findByAddressAndPortAndStorage')
        ->willThrowException(new \Exception());

    $presenter = new CreateVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateVaultConfiguration(
        $this->readVaultConfigurationRepository,
        $this->writeVaultConfigurationRepository,
        $this->readVaultRepository,
        $this->factory
    );

    $createVaultConfigurationRequest = new CreateVaultConfigurationRequest();

    $useCase($presenter, $createVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(ErrorResponse::class);
    expect($presenter->getResponseStatus()?->getMessage())->toBe(
        VaultConfigurationException::impossibleToCreate()->getMessage()
    );
});

it('should present CreatedResponse when vault configuration is created with success', function (): void {
    $this->readVaultConfigurationRepository
        ->expects($this->once())
        ->method('findByAddressAndPortAndStorage')
        ->willReturn(null);

    $vault = new Vault(1, 'myVaultProvider');
    $this->readVaultRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($vault);

    $presenter = new CreateVaultConfigurationPresenterStub($this->presenterFormatter);
    $useCase = new CreateVaultConfiguration(
        $this->readVaultConfigurationRepository,
        $this->writeVaultConfigurationRepository,
        $this->readVaultRepository,
        $this->factory
    );

    $createVaultConfigurationRequest = new CreateVaultConfigurationRequest();
    $createVaultConfigurationRequest->name = 'myVault';
    $createVaultConfigurationRequest->typeId = 1;
    $createVaultConfigurationRequest->address = '127.0.0.1';
    $createVaultConfigurationRequest->port = 8200;
    $createVaultConfigurationRequest->storage = 'myStorage';
    $createVaultConfigurationRequest->roleId = 'myRoleId';
    $createVaultConfigurationRequest->secretId = 'mySecretId';

    $useCase($presenter, $createVaultConfigurationRequest);

    expect($presenter->getResponseStatus())->toBeInstanceOf(CreatedResponse::class);
});
