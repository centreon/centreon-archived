<?php
/**
 * Copyright 2019 Centreon
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
 */

namespace CentreonLegacy\Core\Module;

use PHPUnit\Framework\TestCase;
use Pimple\Psr11\Container;
use VirtualFileSystem\FileSystem;
use Centreon\Test\Mock\DependencyInjector\ServiceContainer;
use CentreonLegacy\Core\Module;
use CentreonLegacy\ServiceProvider;
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonLegacy\Core\Module\Exception;

/**
 * @group CentreonLegacy
 * @group CentreonLegacy\Module
 */
class HealthcheckTest extends TestCase
{
    protected $isModuleFs;

    public function setUp(): void
    {
        // mount VFS
        $this->fs = new FileSystem();

        $this->fs->createDirectory('/tmp');
        $this->fs->createDirectory('/tmp/checklist');
        $this->fs->createFile('/tmp/checklist/requirements.php', '');

        $this->fs->createDirectory('/tmp1');
        $this->fs->createDirectory('/tmp1/checklist');

        $this->container = new ServiceContainer();
        $this->container[ServiceProvider::CONFIGURATION] = $this
            ->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getModulePath',
            ])
            ->getMock();

        $this->container[ServiceProvider::CONFIGURATION]
            ->method('getModulePath')
            ->will(
                $this->returnCallback(function () {
                    return $this->fs->path('/');
                })
            );

        $this->service = $this->getMockBuilder(Module\Healthcheck::class)
            ->setConstructorArgs([
                new Container($this->container),
            ])
            ->onlyMethods([
                'getRequirements',
            ])
            ->getMock();

        $this->setRequirementMockMethodValue();
    }

    public function tearDown(): void
    {
        $this->container->terminate();
        $this->container = null;
    }

    /**
     * Set up method getRequirements
     *
     * @param array $messageV
     * @param array $customActionV
     * @param bool $warningV
     * @param bool $criticalV
     * @param int $licenseExpirationV
     */
    protected function setRequirementMockMethodValue(
        $messageV = null,
        $customActionV = null,
        $warningV = false,
        $criticalV = false,
        $licenseExpirationV = null
    ) {
        $this->service
            ->method('getRequirements')
            ->will($this->returnCallback(function (
                    $checklistDir, &$message, &$customAction, &$warning, &$critical, &$licenseExpiration
                    ) use ($messageV, $customActionV, $warningV, $criticalV, $licenseExpirationV) {
                    $message = $messageV ?: [];
                    $customAction = $customActionV;
                    $warning = $warningV;
                    $critical = $criticalV;
                    $licenseExpiration = $licenseExpirationV;
                }
        ));
    }

    public function testCheckWithDotModuleName()
    {
        try {
            $this->service->check('.');
        } catch (\Exception $ex) {
            $this->assertInstanceOf(Exception\HealthcheckNotFoundException::class, $ex);
        }
    }

    public function testCheckWithMissingModule()
    {
        try {
            $this->service->check('mod');
        } catch (\Exception $ex) {
            $this->assertInstanceOf(Exception\HealthcheckNotFoundException::class, $ex);
        }
    }

    public function testCheckWithWarning()
    {
        $module = 'tmp';

        $this->setRequirementMockMethodValue(null, null, true);

        try {
            $this->service->check($module);
        } catch (\Exception $ex) {
            $this->assertInstanceOf(Exception\HealthcheckWarningException::class, $ex);
        }
    }

    public function testCheckWithCritical()
    {
        $module = 'tmp';
        $valueMessages = [
            [
                'ErrorMessage' => 'err',
                'Solution' => 'none',
            ]
        ];

        $this->setRequirementMockMethodValue($valueMessages, null, false, true);

        try {
            $this->service->check($module);
        } catch (\Exception $ex) {
            $this->assertInstanceOf(Exception\HealthcheckCriticalException::class, $ex);
            $this->assertEquals($valueMessages[0], $this->service->getMessages());
        }
    }

    public function testCheckWithoutRequirementsFile()
    {
        $module = 'tmp1';

        $this->setRequirementMockMethodValue();

        try {
            $this->service->check($module);
        } catch (\Exception $ex) {
            $this->assertInstanceOf(Exception\HealthcheckNotFoundException::class, $ex);
        }
    }

    public function testCheck()
    {
        $module = 'tmp';
        $valueTime = time();
        $valueCustomAction = [
            'action' => 'act',
            'name' => 'nm',
        ];

        $this->setRequirementMockMethodValue(null, $valueCustomAction, false, false, $valueTime);

        $result = $this->service->check($module);
        $this->assertTrue($result);
        $this->assertEquals($valueTime, $this->service->getLicenseExpiration()->getTimestamp());
        $this->assertEquals(
            [
                'customAction' => $valueCustomAction['action'],
                'customActionName' => $valueCustomAction['name'],
            ], $this->service->getCustomAction()
        );
    }

    public function testCheckPrepareResponseWithNotFound()
    {
        $module = 'mod';
        $value = [
            'status' => 'notfound',
        ];

        $result = $this->service->checkPrepareResponse($module);

        $this->assertEquals($result, $value);
    }

    public function testCheckPrepareResponseWithCritical()
    {
        $module = 'tmp';
        $valueMessages = [
            [
                'ErrorMessage' => 'err',
                'Solution' => 'none',
            ]
        ];
        $value = [
            'status' => 'critical',
            'message' => [
                'ErrorMessage' => $valueMessages[0]['ErrorMessage'],
                'Solution' => $valueMessages[0]['Solution'],
            ],
        ];

        $this->setRequirementMockMethodValue($valueMessages, null, false, true);

        $result = $this->service->checkPrepareResponse($module);

        $this->assertEquals($result, $value);
    }

    public function testCheckPrepareResponseWithWarning()
    {
        $module = 'tmp';
        $valueMessages = [
            [
                'ErrorMessage' => 'err',
                'Solution' => 'none',
            ]
        ];
        $value = [
            'status' => 'warning',
            'message' => [
                'ErrorMessage' => $valueMessages[0]['ErrorMessage'],
                'Solution' => $valueMessages[0]['Solution'],
            ],
        ];

        $this->setRequirementMockMethodValue($valueMessages, null, true);

        $result = $this->service->checkPrepareResponse($module);

        $this->assertEquals($value, $result);
    }

    public function testCheckPrepareResponseWithException()
    {
        $module = 'tmp';
        $valueException = 'test exception';
        $value = [
            'status' => 'critical',
            'message' => [
                'ErrorMessage' => $valueException,
                'Solution' => '',
            ],
        ];

        $this->service
            ->method('getRequirements')
            ->will($this->returnCallback(function (
                    $checklistDir, &$message, &$customAction, &$warning, &$critical, &$licenseExpiration
                    ) use ($valueException) {
                    throw new \Exception($valueException);
                }
        ));

        $result = $this->service->checkPrepareResponse($module);

        $this->assertEquals($result, $value);
    }

    public function testCheckPrepareResponse()
    {
        $module = 'tmp';
        $valueTime = time();
        $valueCustomAction = [
            'action' => 'act',
            'name' => 'nm',
        ];
        $value = [
            'status' => 'ok',
            'customAction' => $valueCustomAction['action'],
            'customActionName' => $valueCustomAction['name'],
            'licenseExpiration' => $valueTime,
        ];

        $this->setRequirementMockMethodValue(null, $valueCustomAction, false, false, $valueTime);

        $result = $this->service->checkPrepareResponse($module);

        $this->assertEquals($result, $value);
    }

    public function testReset()
    {
        $value = '';

        $result = $this->service->reset();

        $this->assertEquals($result, $value);
    }
}
