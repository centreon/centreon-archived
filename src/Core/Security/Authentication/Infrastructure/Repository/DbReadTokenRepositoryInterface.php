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
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Security\Authentication\Application\Repository\ReadTokenRepositoryInterface;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\ProviderToken;

class DbReadTokenRepositoryInterface extends AbstractRepositoryDRB implements ReadTokenRepositoryInterface
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
     * {@inheritDoc}
     *
     * @param string $token
     * @return AuthenticationTokens|null
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens
    {
        $statement = $this->db->prepare($this->translateDbName("
            SELECT sat.user_id, sat.provider_configuration_id,
              provider_token.id as pt_id,
              provider_token.token AS provider_token,
              provider_token.creation_date as provider_token_creation_date,
              provider_token.expiration_date as provider_token_expiration_date,
              refresh_token.id as rt_id,
              refresh_token.token AS refresh_token,
              refresh_token.creation_date as refresh_token_creation_date,
              refresh_token.expiration_date as refresh_token_expiration_date
            FROM `:db`.security_authentication_tokens sat
            INNER JOIN `:db`.security_token provider_token ON provider_token.id = sat.provider_token_id
            LEFT JOIN `:db`.security_token refresh_token ON refresh_token.id = sat.provider_token_refresh_id
            WHERE sat.token = :token
        "));
        $statement->bindValue(':token', $token);
        $statement->execute();

        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $expirationDate = $result['provider_token_expiration_date'] !== null
                ? (new \DateTimeImmutable())->setTimestamp((int) $result['provider_token_expiration_date'])
                : null;
            $providerToken = new ProviderToken(
                (int) $result['pt_id'],
                $result['provider_token'],
                (new \DateTimeImmutable())->setTimestamp((int) $result['provider_token_creation_date']),
                $expirationDate
            );

            $providerRefreshToken = null;
            if ($result['refresh_token'] !== null) {
                $expirationDate = $result['refresh_token_expiration_date'] !== null
                    ? (new \DateTimeImmutable())->setTimestamp((int) $result['refresh_token_expiration_date'])
                    : null;
                $providerRefreshToken = new ProviderToken(
                    (int) $result['rt_id'],
                    $result['refresh_token'],
                    (new \DateTimeImmutable())->setTimestamp((int) $result['refresh_token_creation_date']),
                    $expirationDate
                );
            }

            return new AuthenticationTokens(
                (int) $result['user_id'],
                (int) $result['provider_configuration_id'],
                $token,
                $providerToken,
                $providerRefreshToken
            );
        }

        return null;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function hasAuthenticationTokensByToken(string $token): bool
    {
        return $this->findAuthenticationTokensByToken($token) !== null;
    }
}
