<?php


namespace Centreon\Infrastructure\Repository;

use Centreon\Domain\Entity\AuthenticationToken;
use Centreon\Domain\Entity\Session;
use Centreon\Domain\EntityCreator;
use Centreon\Domain\Repository\Interfaces\AuthenticationRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

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

    public function isGoodCredentials(string $username, string $password): bool
    {
        global $dependencyInjector;
        $pearDB = new \CentreonDB('centreon', 3, true);
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
            return (int)$this->db->lastInsertId();
        } else {
            throw new \Exception('Error during token creation');
        }
    }

    public function deleteExpiredTokens():int
    {
        $statement = $this->db->query(
            'DELETE FROM ws_token
            WHERE generate_date < DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        return $statement->rowCount();
    }

    public function findSession(string $sessionId): ?Session
    {
        $statement = $this->db->prepare(
            'SELECT *
            FROM session
            WHERE session_id = :session_id
            LIMIT 1'
        );
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        if ($statement->execute()
            && $result = $statement->fetch(\PDO::FETCH_ASSOC))
        {
            return EntityCreator::createEntityByArray(
                Session::class,
                $result
            );
        }
        return null;
    }

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

    public function findTokenByContact(int $contactId): array
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

    private function createToken(array $details): AuthenticationToken
    {
        return new AuthenticationToken(
            $details['token'],
            $details['contact_id'],
            new \DateTime($details['generate_date'], new \DateTimeZone('Europe/Paris')),
            (bool)$details['is_valid']
        );
    }
}
