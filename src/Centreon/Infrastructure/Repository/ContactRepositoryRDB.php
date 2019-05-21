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
        $prepare->bindValue(':contact_id', $contactId, DatabaseConnection::PARAM_INT);

        $contact = null;
        if ($prepare->execute()
            && ($result = $prepare->fetch(DatabaseConnection::FETCH_ASSOC))
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
        $prepare = $this->pdo->prepare(
            'SELECT * 
            FROM contact 
            WHERE contact_alias = :username
            LIMIT 1'
        );
        $prepare->bindValue(':username', $name, DatabaseConnection::PARAM_STR);
        if ($prepare->execute()
            && ($result = $prepare->fetch(DatabaseConnection::FETCH_ASSOC))
        ) {
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
            ->setIsAdmin($contact['contact_admin'] === '1')
            ->setToken($contact['contact_autologin_key'])
            ->setEncodedPassword($contact['contact_passwd']);
    }
}
