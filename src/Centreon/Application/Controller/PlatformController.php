<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Application\Controller;

use JsonSchema\Validator;
use FOS\RestBundle\View\View;
use Centreon\Domain\Proxy\Proxy;
use JsonSchema\Constraints\Constraint;
use Centreon\Domain\Menu\MenuException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Platform\PlatformException;
use Centreon\Domain\Repository\RepositoryException;
use Centreon\Domain\Exception\EntityNotFoundException;
use Centreon\Domain\RemoteServer\RemoteServerException;
use Centreon\Domain\Proxy\Interfaces\ProxyServiceInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformTopology\PlatformConflictException;
use Centreon\Domain\Platform\Interfaces\PlatformServiceInterface;
use Centreon\Domain\PlatformInformation\PlatformInformationException;
use Centreon\Domain\PlatformTopology\PlatformException as PlatformTopologyException;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;

/**
 * This controller is designed to manage API requests concerning the versions of the different modules, widgets on the
 * Centreon platform.
 *
 * @package Centreon\Application\Controller
 */
class PlatformController extends AbstractController
{
    /**
     * @var PlatformServiceInterface
     */
    private $informationService;

    /**
     * @var PlatformInformationServiceInterface
     */
    private $platformInformationService;

    /**
     * @var ProxyServiceInterface
     */
    private $proxyService;

    public function __construct(
        PlatformServiceInterface $informationService,
        PlatformInformationServiceInterface $platformInformationService,
        ProxyServiceInterface $proxyService
    ) {
        $this->informationService = $informationService;
        $this->platformInformationService = $platformInformationService;
        $this->proxyService = $proxyService;
    }

    /**
     * Validate platform information data according to json schema
     *
     * @param mixed $platformToAdd data sent in json
     * @param array<mixed> $validationSchema
     * @return void
     * @throws PlatformException
     */
    private function validatePlatformInformationSchema($platformToAdd, array $validationSchema): void
    {
        $validator = new Validator();

        $validator->validate(
            $platformToAdd,
            $validationSchema,
            Constraint::CHECK_MODE_VALIDATE_SCHEMA
        );
        if (!$validator->isValid()) {
            $message = '';
            foreach ($validator->getErrors() as $error) {
                $message .= sprintf("[%s] %s\n", $error['property'], $error['message']);
            }
            throw new PlatformInformationException($message);
        }
    }

    /**
     * Retrieves the version of modules, widgets, remote pollers from the Centreon Platform.
     *
     * @return View
     * @throws PlatformException
     */
    public function getVersions(): View
    {
        $webVersion = $this->informationService->getWebVersion();
        $modulesVersion = $this->informationService->getModulesVersion();
        $widgetsVersion = $this->informationService->getWidgetsVersion();

        return $this->view(
            [
                'web' => $this->extractVersion($webVersion),
                'modules' => array_map(
                    function ($version) {
                        return $this->extractVersion($version);
                    },
                    $modulesVersion
                ),
                'widgets' => array_map(
                    function ($version) {
                        return $this->extractVersion($version);
                    },
                    $widgetsVersion
                )
            ]
        );
    }

    /**
     * Extract the major, minor and fix number from the version.
     *
     * @param string $version Version to analyse (ex: 1.2.09)
     * @return array<string, string> (ex: [ 'major' => '1', 'minor' => '2', 'fix' => '09'])
     */
    private function extractVersion(string $version): array
    {
        list($major, $minor, $fix) = explode('.', $version, 3);
        return [
            'version' => $version,
            'major' => $major,
            'minor' => $minor,
            'fix' => !empty($fix) ? $fix : '0'
        ];
    }

    /**
     * Update the platform
     * @param Request $request
     * @return View
     */
    public function updatePlatform(Request $request): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $platformToUpdateProperty = json_decode((string) $request->getContent(), true);

        try {
            if (!is_array($platformToUpdateProperty)) {
                throw new PlatformInformationException(_('Error when decoding sent data'));
            }

            $this->validatePlatformInformationSchema(
                json_decode((string) $request->getContent()),
                json_decode(
                    file_get_contents(
                        $this->getParameter('centreon_path')
                        . 'config/json_validator/latest/Centreon/PlatformInformation/Update.json'
                    ),
                    true
                )
            );

            /**
             * @var PlatformInformation|null $platformInformationUpdate
             */
            $platformInformationUpdate = $this->platformInformationService->getInformation();

            if ($platformInformationUpdate === null) {
                throw new EntityNotFoundException(_("Platform Information not found"));
            }

            foreach ($platformToUpdateProperty as $platformProperty => $platformValue) {
                switch ($platformProperty) {
                    case 'isRemote':
                        $platformInformationUpdate->setIsRemote($platformValue);
                        break;
                    case 'isCentral':
                        $platformInformationUpdate->setIsCentral($platformValue);
                        break;
                    case 'centralServerAddress':
                        $platformInformationUpdate->setCentralServerAddress($platformValue);
                        break;
                    case 'apiUsername':
                        $platformInformationUpdate->setApiUsername($platformValue);
                        break;
                    case 'apiCredentials':
                        $platformInformationUpdate->setApiCredentials($platformValue);
                        break;
                    case 'apiScheme':
                        $platformInformationUpdate->setApiScheme($platformValue);
                        break;
                    case 'apiPort':
                        $platformInformationUpdate->setApiPort($platformValue);
                        break;
                    case 'apiPath':
                        $platformInformationUpdate->setApiPath($platformValue);
                        break;
                    case 'peerValidation':
                        $platformInformationUpdate->setApiPeerValidation($platformValue);
                        break;
                }
            }

            /**
             * Update the Proxy Options
             */
            if (isset($platformToUpdateProperty['proxy'])) {
                $proxyInformations = $platformToUpdateProperty['proxy'];
                $proxy = new Proxy();
                if (isset($proxyInformations['host'])) {
                    $proxy->setUrl($proxyInformations['host']);
                }
                if (isset($proxyInformations['scheme'])) {
                    $proxy->setProtocol($proxyInformations['scheme']);
                }
                if (isset($proxyInformations['port'])) {
                    $proxy->setPort($proxyInformations['port']);
                }
                if (isset($proxyInformations['user'])) {
                    $proxy->setUser($proxyInformations['user']);
                    if (isset($proxyInformations['password'])) {
                        $proxy->setPassword($proxyInformations['password']);
                    }
                }
                $this->proxyService->updateProxy($proxy);
            }

            $this->platformInformationService->updatePlatformInformation($platformInformationUpdate);
        } catch (
            PlatformInformationException
            | EntityNotFoundException
            | RemoteServerException
            | MenuException
            | PlatformTopologyException
            | RepositoryException
            | PlatformConflictException $ex
        ) {
            return $this->view(['message' => $ex->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $ex) {
            return $this->view(
                ['message' => _('Unable to update the platform information')],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
