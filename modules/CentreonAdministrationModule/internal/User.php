<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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
        $emails = UserRepository::getEmail($this->id);

        return $emails;
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
        $homePage = $router->getPathFor('/centreon-realtime/service');
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
