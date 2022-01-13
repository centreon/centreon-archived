<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\Platform\PlatformException;
use Centreon\Domain\PlatformInformation\UseCase\V20\UpdatePartiallyPlatformInformation;
use Centreon\Domain\Platform\Interfaces\PlatformServiceInterface;
use Centreon\Domain\PlatformInformation\Model\PlatformInformationDtoValidator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    public function __construct(
        PlatformServiceInterface $informationService
    ) {
        $this->informationService = $informationService;
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
     * @param UpdatePartiallyPlatformInformation $updatePartiallyPlatformInformation
     * @return View
     * @throws \Throwable
     */
    public function updatePlatform(
        Request $request,
        UpdatePartiallyPlatformInformation $updatePartiallyPlatformInformation
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        $updatePartiallyPlatformInformation->addValidators(
            [
                new PlatformInformationDtoValidator(
                    $this->getParameter('centreon_path')
                    . 'config/json_validator/latest/Centreon/PlatformInformation/Update.json'
                )
            ]
        );

        $request = json_decode((string) $request->getContent(), true);
        if (!is_array($request)) {
            throw new BadRequestHttpException(_('Error when decoding sent data'));
        }

        $updatePartiallyPlatformInformation->execute($request);
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
