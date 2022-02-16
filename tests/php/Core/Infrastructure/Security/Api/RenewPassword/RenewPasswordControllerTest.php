<?php

namespace Tests\Infrastructure\Security\Api\RenewPassword;

use Core\Application\Security\UseCase\RenewPassword\RenewPassword;
use Core\Application\Security\UseCase\RenewPassword\RenewPasswordPresenterInterface;
use Core\Infrastructure\Security\Api\Exception\RenewPasswordApiException;
use Core\Infrastructure\Security\Api\RenewPassword\RenewPasswordController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RenewPasswordControllerTest extends TestCase
{
    /**
     * @var RenewPassword&\PHPUnit\Framework\MockObject\MockObject
     */
    private $useCase;

    /**
     * @var Request&\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var RenewPasswordPresenterInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $presenter;

    public function setUp(): void
    {
        $this->useCase = $this->createMock(RenewPassword::class);
        $this->request = $this->createMock(Request::class);
        $this->presenter = $this->createMock(RenewPasswordPresenterInterface::class);
    }

    /**
     * Test that an exception is thrown is the received payload is invalid.
     */
    public function testExceptionIsThrownWithInvalidPayload()
    {
        $controller = new RenewPasswordController();

        $invalidPayload = json_encode([
            'old_password' => 'titi'
        ]);
        $this->request
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($invalidPayload);

        $this->expectException(RenewPasswordApiException::class);
        $controller($this->useCase, $this->request, $this->presenter, 'admin');
    }
}
