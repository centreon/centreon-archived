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

namespace Centreon\Domain\PlatformInformation;

use Centreon\Domain\PlatformInformation\Model\PlatformInformation;
use Centreon\Domain\PlatformInformation\Exception\PlatformInformationException;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationServiceInterface;
use Centreon\Domain\PlatformInformation\Interfaces\PlatformInformationReadRepositoryInterface;

/**
 * Service intended to use rest API on 'information' specific configuration data
 *
 * @package Centreon\Domain\PlatformInformation
 */
class PlatformInformationService implements PlatformInformationServiceInterface
{

    /**
     * @var PlatformInformationReadRepositoryInterface
     */
    private $platformInformationRepository;

    public function __construct(
        PlatformInformationReadRepositoryInterface $platformInformationRepository
    ) {
        $this->platformInformationRepository = $platformInformationRepository;
    }

    /**
     * @inheritDoc
     */
    public function getInformation(): ?PlatformInformation
    {
        $foundPlatformInformation = null;
        try {
            $foundPlatformInformation = $this->platformInformationRepository->findPlatformInformation();
        } catch (\InvalidArgumentException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new PlatformInformationException(
                _("Unable to retrieve platform information's data.")
            );
        }

        return $foundPlatformInformation;
    }
}
