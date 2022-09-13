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

namespace Core\Security\Authentication\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Authentication\Application\Repository\WriteSessionRepositoryInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WriteSessionRepository implements WriteSessionRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param SessionInterface $session
     */
    public function __construct(private SessionInterface $session)
    {
    }

    /**
     * @inheritDoc
     */
    public function invalidate(): void
    {
        $this->session->invalidate();
    }

    /**
     * Start a session (included the legacy session)
     *
     * @param \Centreon $legacySession
     * @return bool
     */
    public function start(\Centreon $legacySession): bool
    {
        if ($this->session->isStarted()) {
            return true;
        }

        $this->info('[AUTHENTICATE] Starting Centreon Session');
        $this->session->start();
        $this->session->set('centreon', $legacySession);
        $_SESSION['centreon'] = $legacySession;

        $isSessionStarted = $this->session->isStarted();
        if ($isSessionStarted === false) {
            $this->invalidate();
        }

        return $isSessionStarted;
    }
}
