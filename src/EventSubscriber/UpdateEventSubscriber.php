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

namespace EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Repository\ReadVersionRepositoryInterface;

class UpdateEventSubscriber implements EventSubscriberInterface
{
    use LoggerTrait;

    private const MINIMAL_INSTALLED_VERSION = '22.04.0';

    /**
     * @param ReadVersionRepositoryInterface $readVersionRepository
     */
    public function __construct(
        private ReadVersionRepositoryInterface $readVersionRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['validateCentreonWebVersionOrFail', 35],
            ],
        ];
    }

    /**
     * validation centreon web installed version
     *
     * @param RequestEvent $event
     * @throws \Exception
     */
    public function validateCentreonWebVersionOrFail(RequestEvent $event): void
    {
        $this->debug('Checking if route matches updates endpoint');
        if (
            $event->getRequest()->getMethod() === Request::METHOD_PATCH
            && preg_match(
                '#^.*/api/(?:latest|beta|v[0-9]+|v[0-9]+\.[0-9]+)/platform/updates$#',
                $event->getRequest()->getPathInfo(),
            )
        ) {
            $this->debug('Getting Centreon web current version');
            $currentVersion = $this->readVersionRepository->findCurrentVersion();

            if ($currentVersion === null) {
                $errorMessage = sprintf('Required Centreon installed version is %s', self::MINIMAL_INSTALLED_VERSION);
                $this->error($errorMessage);
                throw new \Exception(_($errorMessage));
            }

            $this->debug(
                sprintf(
                    'Comparing installed version %s to required version %s',
                    $currentVersion,
                    self::MINIMAL_INSTALLED_VERSION,
                ),
            );
            if (version_compare($currentVersion, self::MINIMAL_INSTALLED_VERSION, '<')) {
                $errorMessage = sprintf(
                    'Required Centreon installed version is %s (%s installed)',
                    self::MINIMAL_INSTALLED_VERSION,
                    $currentVersion,
                );
                $this->debug($errorMessage);
                throw new \Exception(_($errorMessage));
            }
        }
    }
}
