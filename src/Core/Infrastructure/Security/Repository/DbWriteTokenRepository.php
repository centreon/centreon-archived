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

namespace Core\Infrastructure\Security\Repository;

use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Application\Security\Repository\WriteTokenRepositoryInterface;
use Centreon\Domain\Log\LoggerTrait;

class DbWriteTokenRepository extends AbstractRepositoryDRB implements WriteTokenRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredSecurityTokens(): void
    {
        $this->deleteExpiredProviderRefreshTokens();
        $this->deleteExpiredProviderTokens();
    }

    /**
     * Delete expired provider refresh tokens.
     */
    private function deleteExpiredProviderRefreshTokens(): void
    {
        $this->debug('Deleting expired refresh tokens');

        $this->db->query(
            $this->translateDbName(
                "DELETE st FROM `:db`.security_token st
                WHERE st.expiration_date < UNIX_TIMESTAMP(NOW())
                AND EXISTS (
                    SELECT 1
                    FROM `:db`.security_authentication_tokens sat
                    WHERE sat.provider_token_refresh_id = st.id
                    LIMIT 1
                )"
            )
        );
    }

    /**
     * Delete provider refresh tokens which are not linked to a refresh token.
     */
    private function deleteExpiredProviderTokens(): void
    {
        $this->debug('Deleting expired tokens which are not linked to a refresh token');

        $this->db->query(
            $this->translateDbName(
                "DELETE st FROM `:db`.security_token st
                WHERE st.expiration_date < UNIX_TIMESTAMP(NOW())
                AND NOT EXISTS (
                    SELECT 1
                    FROM `:db`.security_authentication_tokens sat
                    WHERE sat.provider_token_id = st.id
                    AND sat.provider_token_refresh_id IS NOT NULL
                    LIMIT 1
                )"
            )
        );
    }
}
