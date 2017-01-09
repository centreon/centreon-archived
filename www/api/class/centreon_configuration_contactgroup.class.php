<?php
/*
 * Copyright 2005-2015 Centreon
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
 * this program; if not, see <htcontact://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonContactgroup.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonLDAP.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonConfigurationContactgroup extends CentreonConfigurationObjects
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     *
     * @return array
     */
    public function getList()
    {
        global $centreon;

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $limit = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $offset = $this->arguments['page_limit'];
            $range = $limit . ',' . $offset;
        } else {
            $range = '';
        }

        $filterContactgroup = array();
        $ldapFilter = '';
        if (isset($this->arguments['q'])) {
            $filterContactgroup['cg_name'] = array('LIKE', '%' . $this->arguments['q'] . '%');
            $filterContactgroup['cg_alias'] = array('OR', 'LIKE', '%' . $this->arguments['q'] . '%');
            $ldapFilter = $this->arguments['q'];
        }

        $cg = new CentreonContactgroup($this->pearDB);
        $acl = new CentreonACL($centreon->user->user_id);

        $aclCgs = $acl->getContactGroupAclConf(
            array(
                'fields'  => array('cg_id', 'cg_name', 'cg_type', 'ar_name'),
                'get_row' => null,
                'keys' => array('cg_id'),
                'conditions' => $filterContactgroup,
                'order' => array('cg_name'),
                'pages' => $range,
                'total' => true
            ),
            false
        );
       

        $contactgroupList = array();
        foreach ($aclCgs['items'] as $id => $contactgroup) {
            $sText = $contactgroup['cg_name'];
            if ($contactgroup['cg_type'] == 'ldap') {
                $sText .= " (LDAP : ".$contactgroup['ar_name'].")";
            }
            $id = $contactgroup['cg_id'];
            $contactgroupList[] = array(
                'id' => $id,
                'text' => $sText
            );
        }

        # get Ldap contactgroups
        $ldapCgs = array();
        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $maxItem = $this->arguments['page_limit'] * $this->arguments['page'];
            if ($aclCgs['total'] <= $maxItem) {
                $ldapCgs = $cg->getLdapContactgroups($ldapFilter);
            }
        } else {
            $ldapCgs = $cg->getLdapContactgroups($ldapFilter);
        }
 
        foreach ($ldapCgs as $key => $value) {
            $sTemp = $value;
            if (!$this->unique_key($sTemp, $contactgroupList)) {
                $contactgroupList[] = array(
                    'id' => $key,
                    'text' => $value
                );
            }
        }

        return array(
            'items' => $contactgroupList,
            'total' => $aclCgs['total']
        );
    }
    
    protected function unique_key($val, &$array)
    {

        if (!empty($val) && count($array) > 0) {
            foreach ($array as $key => $value) {
                if ($value['text'] == $val) {
                    return true;
                }
            }
        }
        return false;
    }
}
