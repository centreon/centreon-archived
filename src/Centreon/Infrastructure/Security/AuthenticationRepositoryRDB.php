<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Infrastructure\Security;

use Centreon\Domain\Entity\AuthenticationToken;
use Centreon\Domain\Entity\Session;
use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Security\Interfaces\AuthenticationRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

/**
 * Database repository for the authentication.
 *
 * @package Centreon\Infrastructure\Security
 */
class AuthenticationRepositoryRDB implements AuthenticationRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $db;

    /**
     * AuthenticationRepository constructor.
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * @throws \Exception
     */
    public function isGoodCredentials(string $username, string $password): bool
    {
        global $dependencyInjector;
        $pearDB = new \CentreonDB(
            $this->db->getCentreonDbName(),
            3,
            true
        );
        $log = new \CentreonUserLog(0, $pearDB);
        $auth = new \CentreonAuth(
            $dependencyInjector,
            $username,
            $password,
            0,
            $pearDB,
            $log,
            1,
            "",
            "API"
        );
        return $auth->passwdOk === 1;
    }

    /**
     * @inheritDoc
     */
    public function addToken(int $contactId, string $token)
    {
        $statement = $this->db->prepare(
            'INSERT INTO ws_token
            SET contact_id = :contact_id,
              token = :token,
              generate_date = NOW()'
        );

        $statement->bindValue(':contact_id', $contactId, DatabaseConnection::PARAM_INT);
        $statement->bindValue(':token', $token, DatabaseConnection::PARAM_STR);
        if ($statement->execute()) {
            return (int) $this->db->lastInsertId();
        } else {
            throw new \Exception('Error during token creation');
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredTokens():int
    {
        $statement = $this->db->query(
            'DELETE FROM ws_token WHERE generate_date < DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        return $statement->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function deleteTokenFromContact(int $contactId, string $token): bool
    {
        $statement = $this->db->prepare(
            'DELETE FROM ws_token WHERE token = :token AND contact_id = :contact_id'
        );
        $statement->bindValue(':token', $token, DatabaseConnection::PARAM_STR);
        $statement->bindValue(':contact_id', $contactId, DatabaseConnection::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() === 1;
    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredSession(): void
    {
        try {
            $this->db->query(
                'DELETE FROM `session`
                WHERE last_reload < 
                    (SELECT UNIX_TIMESTAMP(NOW() - INTERVAL (`value` * 60) SECOND)
                    FROM `options`
                    wHERE `key` = \'session_expire\')
                OR last_reload IS NULL'
            );
        } catch (\PDOException $ex) {
            throw new \Exception("Error during deleting expired session");
        }
    }

    /**
     * @inheritDoc
     */
    public function findSession(string $sessionId): ?Session
    {
        $statement = $this->db->prepare(
            'SELECT * FROM session WHERE session_id = :session_id LIMIT 1'
        );
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        if ($statement->execute()
            && $result = $statement->fetch(\PDO::FETCH_ASSOC)
        ) {
            return EntityCreator::createEntityByArray(
                Session::class,
                $result
            );
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findToken(string $token): ?AuthenticationToken
    {
        $statement = $this->db->prepare(
            'SELECT *, 
                CASE 
                    WHEN generate_date > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                        THEN true
                        ELSE false
                END AS is_valid
            FROM ws_token
            WHERE token = :token
            ORDER BY generate_date ASC'
        );
        $statement->bindValue(':token', $token, \PDO::PARAM_STR);
        if ($statement->execute()) {
            if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $this->createToken($result);
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findTokensByContact(int $contactId): array
    {
        $statement = $this->db->prepare(
            'SELECT *,
                CASE 
                    WHEN generate_date > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                        THEN true
                        ELSE false
                END AS is_valid
            FROM ws_token
            WHERE contact_id = :contact_id
            ORDER BY generate_date ASC'
        );

        $tokens = [];
        $statement->bindValue(':contact_id', $contactId, DatabaseConnection::PARAM_INT);
        if ($statement->execute()) {
            while ($token = $statement->fetch(DatabaseConnection::FETCH_ASSOC)) {
                $tokens[] = $this->createToken($token);
            }
        } else {
            throw new \Exception('Error when recovering the number of tokens');
        }
        return $tokens;
    }

    /**
     * @inheritDoc
     */
    public function deleteTokensByContact(int $contactId): int
    {
        $statement = $this->db->prepare(
            'DELETE FROM ws_token
            WHERE contact_id = :contact_id'
        );
        $statement->bindValue(':contact_id', $contactId, DatabaseConnection::PARAM_INT);
        if ($statement->execute()) {
            return $statement->rowCount();
        } else {
            throw new \Exception('Error when deleting tokens for one contact');
        }
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(string $token): void
    {
        $statement = $this->db->prepare(
            'UPDATE ws_token SET generate_date = NOW() WHERE token = :token'
        );
        $statement->bindValue(':token', $token, DatabaseConnection::PARAM_STR);
        $statement->execute();
    }

    /**
     * (Factory) Create a new instance of the AuthenticationToken class
     *
     * @param array $details Data representing the authentication information
     * @return AuthenticationToken Returns the new instance of the AuthenticationToken class
     * @throws \Exception
     */
    private function createToken(array $details): AuthenticationToken
    {
        return new AuthenticationToken(
            $details['token'],
            (int) $details['contact_id'],
            new \DateTime($details['generate_date']),
            (bool)$details['is_valid']
        );
    }
}
