<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2018 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace Centreon\Domain\Repository;

use Centreon\Domain\Repository\Interfaces\ContactRepositoryInterface;

class ContactRepository implements ContactRepositoryInterface
{
    /**
     * @var \CentreonDB
     */
    private $db;

    /**
     * ContactRepository constructor.
     * @param \CentreonDB $db
     */
    public function __construct(\CentreonDB $db)
    {
        $this->db = $db;
    }

    public function searchContactIdByToken(string $token): ?int
    {
        $stmt = $this->db->prepare(
            "SELECT contact_id 
            FROM ws_token 
            WHERE token = :token"
        );
        $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
        $stmt->execute();

        if (($result = $stmt->fetch()) !== false) {
            return (int)$result['contact_id'];
        }
        return null;
    }
}
