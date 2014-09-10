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

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostTemplateRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_hosts';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Hosttemplate';

    /**
     * 
     * @param int $host_id
     * @return string
     */
    public static function getTemplates($host_id)
    {
        $di = \Centreon\Internal\Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        /* Init Array to return */
        $hostTemplates = "";
        
        /* Get information into the database. */
        $query = "SELECT host_tpl_id, host_name, `order` "
            . "FROM cfg_hosts h, cfg_hosts_templates_relations hr "
            . "WHERE h.host_id = hr.host_tpl_id "
            . "AND hr.host_host_id = '$host_id' "
            . "AND host_activate = '1' "
            . "AND host_register = '0' "
            . "ORDER BY `order` ASC";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($hostTemplates != "") {
                $hostTemplates .= ',';
            }
            $hostTemplates .= $row["host_name"];
        }
        return $hostTemplates;
    }

    /**
     * 
     * @param int $host_id
     * @return string
     */
    public static function getTemplateList($host_id)
    {
        $di = \Centreon\Internal\Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        /* Init Array to return */
        $hostTemplates = array();
        
        /* Get information into the database. */
        $query = "SELECT host_tpl_id, host_name, host_id, `order` "
            . "FROM cfg_hosts h, cfg_hosts_templates_relations hr "
            . "WHERE h.host_id = hr.host_tpl_id "
            . "AND hr.host_host_id = '$host_id' "
            . "AND host_activate = '1' "
            . "AND host_register = '0' "
            . "ORDER BY `order` ASC";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $hostTemplates[] = array(
                                     'id' => $row["host_id"],
                                     'name' => $row["host_name"],
                                     'ico' => 'fa-shield');
        }
        return $hostTemplates;
    }

    /**
     * 
     * @param int $host_id
     * @return array
     */
    public static function getContacts($host_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactList = "";

        $query = "SELECT contact_alias "
            . "FROM cfg_contacts c, cfg_contacts_hosts_relations ch "
            . "WHERE host_host_id = '$host_id' "
            . "AND c.contact_id = ch.contact_id "
            . "ORDER BY contact_alias";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactList != "") {
                $contactList .= ",";
            }
            $contactList .= $row["contact_alias"];
        }
        return $contactList;
    }

    /**
     * 
     * @param int $host_id
     * @return array
     */
    public static function getContactGroups($host_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactgroupList = "";

        $query = "SELECT cg_name "
            . "FROM cfg_contactgroups cg, cfg_contactgroups_hosts_relations cgh "
            . "WHERE host_host_id = '$host_id' "
            . "AND cg.cg_id = cgh.contactgroup_cg_id "
            . "ORDER BY cg_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactgroupList != "") {
                $contactgroupList .= ",";
            }
            $contactgroupList .= $row["cg_name"];
        }
        return $contactgroupList;
    }
}
