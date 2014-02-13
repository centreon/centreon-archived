<?php

namespace Centreon\Core;

class Acl
{
    const ADD = 1;
    const DELETE = 2;
    const UPDATE = 4;
    const VIEW = 8;

    private $routes;
    private $isAdmin;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT route, permission 
            FROM acl_routes ar, acl_groups g, acl_group_contacts_relations r
            WHERE ar.acl_group_id = g.acl_group_id
            AND g.acl_group_id = r.acl_group_id
            AND r.contact_contact_id = ?";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($userId));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check whether user is allowed to access route
     *
     * @param string $route
     * @param int $requiredAccess
     * @return bool
     */
    public function routeAllowed($route, $requiredAccess)
    {
        return true; // for dev purpose
        if ($this->isAdmin) {
            return true;
        }
        if (isset($this->routes[$route]) && 
            ($this->routes[$route] & $requiredAccess)) {
            return true;
        }
        return false;
    }

    /**
     * Convert ACL flags
     *
     * @return int
     */
    public static function convertAclFlags($aclFlags)
    {
        $flag = null;
        foreach ($aclFlags as $flag) {
            switch (strtolower($flag)) {
                case "add": 
                    $f = self::ADD;
                    break;
                case "delete":
                    $f = self::DELETE;
                    break;
                case "update":
                    $f = self::UPDATE;
                    break;
                case "view":
                    $f = self::VIEW;
                    break;
            }
            if (is_null($flag)) {
                $flag = $f;
            } else {
                $flag |= $f;
            }
        }
        return $flag;
    }
}
