<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonAdministration\Internal;

use Centreon\Internal\Di;
use CentreonAdministration\Models\User as UserModel;
use Centreon\Internal\Exception;
use CentreonAdministration\Repository\UserRepository;

/**
 * Object that represents the logged in user
 */
class User
{
    /**
     *
     * @var int
     */
    private $id;
    /**
     *
     * @var string
     */
    private $name;
    /**
     *
     * @var string
     */
    private $login;
    /**
     *
     * @var string
     */
    private $lang;
    /**
     *
     * @var bool
     */
    private $admin;
    /**
     *
     * @const string
     */
    public static $notInit = 'User not initialized properly';
    /**
     * @var array
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param int $userId
     */
    public function __construct($userId)
    {
        $contactObj = new UserModel();
        $paramArr = array(
            'user_id',
            'firstname',
            'lastname',
            'login',
            'is_admin'
        );
        $params = $contactObj->getParameters($userId, $paramArr);
        if (!is_array($params) || !count($params)) {
            throw new Exception('Unknown user id');
        }
        $this->id = $params['user_id'];
        $this->name = $params['firstname'] . ' ' . $params['lastname'];
        $this->login = $params['login'];
        $this->admin = $params['is_admin'];
        $this->timezone = $this->getTimezone();
    }

    /**
     * id getter
     *
     * @return int
     */
    public function getId()
    {
        if (!isset($this->id)) {
            throw new Exception(self::$notInit);
        }
        return $this->id;
    }

    /**
     * name getter
     *
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            throw new Exception(self::$notInit);
        }
        return $this->name;
    }

    /**
     * login getter
     *
     * @return string
     */
    public function getLogin()
    {
        if (!isset($this->login)) {
            throw new Exception(self::$notInit);
        }
        return $this->login;
    }

    /**
     * lang getter
     *
     * @return string
     */
    public function getLang()
    {
        if (!isset($this->lang)) {
            throw new Exception(self::$notInit);
        }
        return $this->lang;
    }

    /**
     * email getter
     *
     * @return string
     */
    public function getEmail()
    {
        if (!isset($this->email)) {
            throw new Exception(self::$notInit);
        }
        return $this->email;
    }

    /**
     * Returns true if user is admin, false otherwise
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if (!isset($this->admin)) {
            throw new Exception(self::$notInit);
        }
        if ($this->admin) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return string
     */
    public function getHomePage()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $homePage = $router->getPathFor('/centreon-customview');
        return $homePage;
    }
    /**
     * 
     * @return array
     */
    public function getTimezone()
    {
        $repository = '\CentreonAdministration\Repository\UserRepository';
        $repository::setObjectClass('\CentreonAdministration\Models\User');
        $aTimezone = $repository::getRelations("\CentreonAdministration\Models\Relation\User\Timezone", $this->id);
        return $aTimezone;
    }
}
