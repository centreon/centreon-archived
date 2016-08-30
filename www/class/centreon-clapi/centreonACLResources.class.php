<?php
/**
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

class CentreonACLResources
{
    private $DB;

    public function __construct($DB)
    {
        $this->_DB = $DB;
    }

    public function getACLResourceID($name)
    {
        $request = "SELECT acl_group_id FROM acl_groups WHERE acl_group_name LIKE '"
            . htmlentities($name, ENT_QUOTES) . "'";
        $DBRESULT = $this->_DB->query($request);
        $data = $DBRESULT->fetchRow();
        if ($data["acl_group_id"]) {
            return $data["acl_group_id"];
        } else {
            return 0;
        }
    }

    public function addContact($contact_id, $aclid)
    {
        $request = "DELETE FROM acl_group_contacts_relations "
            . "WHERE acl_group_id = '$aclid' AND contact_contact_id = '$contact_id'";
        $DBRESULT = $this->_DB->query($request);

        $request = "INSERT INTO acl_group_contacts_relations "
            . "(acl_group_id, contact_contact_id) "
            . "VALUES ('" . $aclid . "', '" . $contact_id . "')";
        $DBRESULT = $this->_DB->query($request);
        return 0;
    }

    public function delContact($contact_id, $aclid)
    {
        $request = "DELETE FROM acl_group_contacts_relations "
            . "WHERE acl_group_id = '$aclid' AND contact_contact_id = '$contact_id'";
        $DBRESULT = $this->_DB->query($request);
        return 0;
    }

    public function updateACL()
    {
        $request = "UPDATE `acl_resources` SET `changed` = '1'";
        $DBRESULT = $this->_DB->query($request);
        return 0;
    }
}
