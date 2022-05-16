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

namespace Core\Infrastructure\Common\Repository;

use Core\Application\Common\Session\Repository\ReadSessionRepositoryInterface;

class SystemReadSessionRepository implements ReadSessionRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findSessionIdsByUserId(int $userId): array
    {
        throw RepositoryException::notImplemented(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function getValueFromSession(string $sessionId, string $key): mixed
    {
        session_id($sessionId);
        session_start();
        $value = $_SESSION[$key];
        session_write_close();
        return $value;
    }
}
