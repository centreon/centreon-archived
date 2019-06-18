<?php

namespace Centreon\Infrastructure\Repository;

use Centreon\Domain\Entity\Contact;
use Centreon\Domain\Repository\Interfaces\ContactRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;

final class ContactRepositoryRDB implements ContactRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $pdo;

    public function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param int $contactId
     * @return Contact|null
     */
    public function findById(int $contactId): ?Contact
    {
        $prepare = $this->pdo->prepare(
            "SELECT * FROM contact WHERE contact_id = :contact_id"
        );
        $prepare->bindValue(':contact_id', $contactId, \PDO::PARAM_INT);

        $contact = null;
        if ($prepare->execute()
            && ($result = $prepare->fetch(\PDO::FETCH_ASSOC))
        ) {
            $contact = $this->createContact($result);
        }

        return $contact;
    }

    /**
     * @param string $name Username
     * @return Contact|null
     */
    public function findByName(string $name): ?Contact
    {
        $statement = $this->pdo->prepare(
            'SELECT * 
            FROM contact 
            WHERE contact_alias = :username
            LIMIT 1'
        );
        $statement->bindValue(':username', $name, \PDO::PARAM_STR);
        if ($statement->execute()
            && ($result = $statement->fetch(\PDO::FETCH_ASSOC))
        ) {
            $contact = $this->createContact($result);
            return $contact;
        } else {
            return null;
        }
    }


    public function findBySession(string $sessionId): ?Contact
    {
        $statement = $this->pdo->prepare(
            'SELECT contact.*
            FROM contact
            INNER JOIN session
              on session.user_id = contact.contact_id
            WHERE session.session_id = :session_id
            LIMIT 1'
        );
        $statement->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        if ($statement->execute()
            && ($result = $statement->fetch(\PDO::FETCH_ASSOC)))
        {
            $contact = $this->createContact($result);
            return $contact;
        } else {
            return null;
        }
    }

    private function createContact(array $contact): Contact
    {
        return (new Contact())
            ->setId((int) $contact['contact_id'])
            ->setName($contact['contact_name'])
            ->setAlias($contact['contact_alias'])
            ->setEmail($contact['contact_email'])
            ->setTemplateId($contact['contact_template_id'])
            ->setIsActive($contact['contact_activate'])
            ->setAdmin($contact['contact_admin'] === '1')
            ->setToken($contact['contact_autologin_key'])
            ->setEncodedPassword($contact['contact_passwd']);
    }
}
