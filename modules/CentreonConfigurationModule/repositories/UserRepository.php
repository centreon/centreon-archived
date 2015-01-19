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

namespace CentreonConfiguration\Repository;

use CentreonConfiguration\Models\Contact;
use Centreon\Internal\Di;
use Centreon\Internal\Auth\Sso;
use Centreon\Internal\Exception\Authentication\BadCredentialException;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
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
     * @param integer $contactId
     * @param string $object
     * @return string
     */
    public static function getNotificationInfos($contactId, $object)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        if ($object == 'host') {
            $ctp = 'timeperiod_tp_id';
        } elseif ($object == 'service') {
            $ctp = 'timeperiod_tp_id2';
        }
        
        $query = "SELECT tp_name, contact_".$object."_notification_options "
            . "FROM cfg_contacts, cfg_timeperiods "
            . "WHERE contact_id='$contactId' "
            . "AND tp_id = $ctp" ;
        
        $stmt = $dbconn->query($query);
        $resultSet = $stmt->fetch();
        
        if ($resultSet === false) {
            $return = '';
        } else {
            $return = $resultSet['tp_name'].' ('.$resultSet['contact_'.$object.'_notification_options'].')';
        }
        
        return $return;
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
     * @param type $type
     * @return string
     */
    public static function getNotificationCommand($contact_id, $type)
    {
        $di = Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        if ($type != "host" && $type != "service") {
            return "";
        }

        /* Launch Request */
        $query = "SELECT command_name FROM cfg_contacts_".$type."commands_relations, cfg_commands "
            . "WHERE contact_contact_id = $contact_id AND command_command_id = command_id";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        $cmd = "";
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($cmd != "") {
                $cmd .= ",";
            }
            $cmd .= $row["command_name"];
        }
        return $cmd;
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
