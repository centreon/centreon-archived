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

use Centreon\Application\Controller\AbstractController;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformInformation\PlatformInformation;
use Centreon\Domain\PlatformInformation\PlatformInformationService;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class is design to manage REST API requests concerning the platform's information used in the configuration.
 *
 * @package Centreon\Application\Controller\Configuration
 */
class PlatformInformationController extends AbstractController
{
    public const SERIALIZER_GROUP_MAIN = ['platform_information_main'];
    public const SERIALIZER_GROUP_LIMITED = ['platform_information_limited'];

    /**
     * PlatformInformationController constructor
     * @var PlatformInformationServiceInterface $platformInformationService
     */
    private $platformInformationService;

    public function __construct(PlatformInformationService $platformInformationService)
    {
        $this->platformInformationService = $platformInformationService;
    }

    /**
     * @return View
     * @throws \Exception
     */
    public function getInformation(): View
    {
        $this->denyAccessUnlessGrantedForApiConfiguration();

        if (!$this->getUser()->isAdmin() && !$this->isGranted('ROLE_ADMINISTRATION_PARAMETERS_CENTREON_UI_RW')) {
            $context = (new Context())
                ->setGroups(static::SERIALIZER_GROUP_LIMITED)
                ->enableMaxDepth();
            return $this->view($this->platformInformationService->getInformation())->setContext($context);
        }

        $context = (new Context())
            ->setGroups(static::SERIALIZER_GROUP_MAIN)
            ->enableMaxDepth();
        return $this->view($this->platformInformationService->getInformation())->setContext($context);
    }
}
