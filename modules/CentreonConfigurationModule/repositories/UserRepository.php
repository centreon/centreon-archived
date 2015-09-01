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

namespace CentreonConfiguration\Repository;

use CentreonConfiguration\Models\Contact;
use Centreon\Internal\Di;
use Centreon\Internal\Auth\Sso;
use Centreon\Internal\Exception\Authentication\BadCredentialException;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class UserRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_contacts';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'User';


    /**
     * Create user
     *
     * @param array $givenParameters
     */
    public static function create($givenParameters)
    {
        if (isset($givenParameters['contact_passwd']) && $givenParameters['contact_passwd']) {
            $givenParameters['contact_passwd'] = md5($givenParameters['contact_passwd']);
        }
        parent::create($givenParameters);
    }

    /**
     * Update user
     *
     * @param array $givenParameters
     */
    public static function update($givenParameters)
    {
        /* Do not perform update if password is empty */
        if (isset($givenParameters['contact_passwd']) && $givenParameters['contact_passwd'] == '') {
            unset($givenParameters['contact_passwd']);
        } elseif (isset($givenParameters['contact_passwd'])) { /* Let's md5() the password */
            $givenParameters['contact_passwd'] = md5($givenParameters['contact_passwd']);
        }
        parent::update($givenParameters);
    }

    /**
     * 
     * @param type $name
     * @param type $email
     * @return string
     */
    public static function getUserIcon($name, $email)
    {
        if ($email != "") {
            $name = "<img src='http://www.gravatar.com/avatar/".
                md5($email).
                "?rating=PG&size=16&default=' class='img-circle'>&nbsp;".
                $name;
        } else {
            $name = "<i class='fa fa-user'></i>&nbsp;".$name;
        }
        
        return $name;
    }

    /**
     * 
     * @param type $contact_id
     * @return type
     */
    public static function getContactContactGroup($contact_id)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Launch Request */
        $query = "SELECT cg_name FROM cfg_contactgroups_contacts_relations cgr, cfg_contactgroups cg "
            . "WHERE contact_contact_id = ".$contact_id." AND cgr.contactgroup_cg_id = cg.cg_id";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $cg = "";
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($cg != "") {
                $cg .= ",";
            }
            $cg .= $row["cg_name"];
        }
        return $cg;
    }
    
    /**
     * 
     * @param type $login
     * @param type $password
     */
    public static function getTokenForApi($login, $password)
    {
        $token = "";
        $connectedUser = new Sso($login, $password, 0);
        if (1 === $connectedUser->passwdOk) {
            $token = hash('sha256', $login . $password);
            Contact::update($connectedUser->userInfos['contact_id'], array('contact_autologin_key' => $token));
        } else {
            throw new BadCredentialException('The password or the login is incorrect', 0);
        }
        return $token;
    }
    
    /**
     * 
     * @param type $token
     */
    public static function checkApiToken($token)
    {
        $tokenOk = false;
        $user = Contact::getIdByParameter('contact_autologin_key', array($token));
        if (is_array($user) && (count($user) == 1)) {
            $tokenOk = true;
        }
        return $tokenOk;
    }
}
