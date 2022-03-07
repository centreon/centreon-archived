<?php

namespace Tests\Core\Application\Security\UseCase\LoginOpenIdSession;

use CentreonDB;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Core\Infrastructure\Common\Presenter\JsonPresenter;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Menu\Interfaces\MenuServiceInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Centreon\Domain\Repository\Interfaces\DataStorageEngineInterface;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Security\Domain\Authentication\Interfaces\SessionRepositoryInterface;
use Core\Application\Security\UseCase\LoginOpenIdSession\LoginOpenIdSession;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Core\Application\Security\UseCase\LoginOpenIdSession\LoginOpenIdSessionRequest;
use Core\Infrastructure\Security\Api\LoginOpenIdSession\LoginOpenIdSessionPresenter;
use Core\Application\Security\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;

beforeEach(function () {
    $this->repository = $this->createMock(ReadOpenIdConfigurationRepositoryInterface::class);
    $this->provider = $this->createMock(OpenIdProviderInterface::class);
    $this->session = $this->createMock(SessionInterface::class);
    $this->session
        ->expects($this->any())
        ->method('getId')
        ->willReturn('session_abcd');
    $this->request = $this->createMock(Request::class);
    $this->request
        ->expects($this->any())
        ->method('getSession')
        ->willReturn($this->session);
    $this->requestStack = $this->createMock(RequestStack::class);
    $this->requestStack
        ->expects($this->any())
        ->method('getCurrentRequest')
        ->willReturn($this->request);
    $this->dependencyInjector = $this->createMock(Container::class);
    $this->centreonDB = $this->createMock(CentreonDB::class);
    $this->dependencyInjector['configuration_db'] = $this->centreonDB;
    $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
    $this->menuService = $this->createMock(MenuServiceInterface::class);
    $this->authenticationRepository = $this->createMock(AuthenticationRepositoryInterface::class);
    $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
    $this->dataStorageEngine = $this->createMock(DataStorageEngineInterface::class);
    $this->formatter = $this->createMock(JsonPresenter::class);
    $this->presenter = new LoginOpenIdSessionPresenter($this->formatter);
    $this->contact = $this->createMock(ContactInterface::class);


    $this->validOpenIdConfiguration = new OpenIdConfiguration(
        1,
        true,
        false,
        [],
        [],
        'http://127.0.0.2/',
        '/auth',
        '/token',
        '/introspection',
        '/userinfo',
        '/logout',
        [],
        null,
        'client-id',
        'client-secret',
        'client_secret_post',
        false
    );
});

it('expects to return an error message in presenter when no provider configuration are found', function () {
    $request = new LoginOpenIdSessionRequest();
    $request->authorizationCode = 'abcde-fghij-klmno';
    $request->clientIp = '127.0.0.1';

    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn(null);

    $useCase = new LoginOpenIdSession(
        '/monitoring/ressources',
        $this->repository,
        $this->provider,
        $this->requestStack,
        $this->dependencyInjector,
        $this->authenticationService,
        $this->menuService,
        $this->authenticationRepository,
        $this->sessionRepository,
        $this->dataStorageEngine
    );

    $useCase($request, $this->presenter);
    expect($this->presenter->getData()->error)->toBe('Provider not found');
});

it('expects to execute authenticateOrFail method from OpenIdProvider', function () {
    $request = new LoginOpenIdSessionRequest();
    $request->authorizationCode = 'abcde-fghij-klmno';
    $request->clientIp = '127.0.0.1';

    $this->repository
        ->expects($this->once())
        ->method('findConfiguration')
        ->willReturn($this->validOpenIdConfiguration);

    $this->provider
        ->expects($this->once())
        ->method('authenticateOrFail');

    $useCase = new LoginOpenIdSession(
        '/monitoring/ressources',
        $this->repository,
        $this->provider,
        $this->requestStack,
        $this->dependencyInjector,
        $this->authenticationService,
        $this->menuService,
        $this->authenticationRepository,
        $this->sessionRepository,
        $this->dataStorageEngine
    );
    $useCase($request, $this->presenter);
});

it(
    'expects to return an error message in presenter when the provider can\'t find the user and can\'t create it',
    function () {
        $request = new LoginOpenIdSessionRequest();
        $request->authorizationCode = 'abcde-fghij-klmno';
        $request->clientIp = '127.0.0.1';

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($this->validOpenIdConfiguration);

        $this->provider
            ->expects($this->once())
            ->method('canCreateUser')
            ->willReturn(false);

        $useCase = new LoginOpenIdSession(
            '/monitoring/ressources',
            $this->repository,
            $this->provider,
            $this->requestStack,
            $this->dependencyInjector,
            $this->authenticationService,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );
        $useCase($request, $this->presenter);
        expect($this->presenter->getData()->error)->toBe('User not found');
    }
);

it(
    'expects to return an error message in presenter when the provider ' .
    'wasn\'t be able to return a user after creating it',
    function () {
        $request = new LoginOpenIdSessionRequest();
        $request->authorizationCode = 'abcde-fghij-klmno';
        $request->clientIp = '127.0.0.1';

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($this->validOpenIdConfiguration);

        $this->provider
            ->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $this->provider
            ->expects($this->once())
            ->method('canCreateUser')
            ->willReturn(true);

        $useCase = new LoginOpenIdSession(
            '/monitoring/ressources',
            $this->repository,
            $this->provider,
            $this->requestStack,
            $this->dependencyInjector,
            $this->authenticationService,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $useCase($request, $this->presenter);
        expect($this->presenter->getData()->error)->toBe('User not found');
    }
);

it(
    'expects to find existing token with the current session id',
    function () {
        $request = new LoginOpenIdSessionRequest();
        $request->authorizationCode = 'abcde-fghij-klmno';
        $request->clientIp = '127.0.0.1';

        $this->repository
            ->expects($this->once())
            ->method('findConfiguration')
            ->willReturn($this->validOpenIdConfiguration);

        $this->provider
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($this->contact);

        $useCase = new LoginOpenIdSession(
            '/monitoring/ressources',
            $this->repository,
            $this->provider,
            $this->requestStack,
            $this->dependencyInjector,
            $this->authenticationService,
            $this->menuService,
            $this->authenticationRepository,
            $this->sessionRepository,
            $this->dataStorageEngine
        );

        $useCase($request, $this->presenter);
    }
);
