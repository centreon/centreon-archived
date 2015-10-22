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
 * this program; if not, see <http://www.gnu.org/licenses>.
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */


require_once _CENTREON_PATH_ . "/www/class/centreonBroker.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonConfigurationHostgroup extends CentreonConfigurationObjects
{
    
    /**
     *
     * @var type 
     */
    protected $pearDBMonitoring;

    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $brk = new CentreonBroker($this->pearDB);
        if ($brk->getBroker() == 'broker') {
            $this->pearDBMonitoring = new CentreonDB('centstorage');
        } else {
            $this->pearDBMonitoring = new CentreonDB('ndo');
        }
    }
    
    /**
     * 
     * @param array $args
     * @return array
     */
    public function getList()
    {
        global $centreon;
        
        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHostgroups = '';
        
        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHostgroups .= 'AND hg.hg_id IN (' . $acl->getHostGroupsString('ID') . ') ';
        }
        
        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }
        
        $queryHostgroup = "SELECT DISTINCT hg.hg_name, hg.hg_id "
            . "FROM hostgroup hg "
            . "WHERE hg.hg_name LIKE '%$q%' "
            . $aclHostgroups
            . "ORDER BY hg.hg_name";
        
        $DBRESULT = $this->pearDB->query($queryHostgroup);
        
        $hostgroupList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $hostgroupList[] = array('id' => htmlentities($data['hg_id']), 'text' => $data['hg_name']);
        }
        
        return $hostgroupList;
    }
    
    public function getHostList(){
        global $centreon;
        
        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHostgroups = '';
        $aclHosts = '';
        
        /* Get ACL if user is not admin */
        
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHostgroups .= ' AND hg.hg_id IN (' . $acl->getHostGroupsString('ID') . ') ';
            $aclHosts .= ' AND h.host_id IN (' . $acl->getHostsString('ID', $this->pearDBMonitoring) . ') ';
        }

        
        // Check for select2 'q' argument
        if (false === isset($this->arguments['hgid'])) {
            $hgid = '';
        } else {
            $hgid = $this->arguments['hgid'];
        }
        
        $queryHostgroup = "SELECT DISTINCT h.host_name , h.host_id "
            . "FROM hostgroup hg "
            . "INNER JOIN hostgroup_relation hgr ON hg.hg_id = hgr.hostgroup_hg_id "
            . "INNER JOIN host h ON  h.host_id = hgr.host_host_id "
            . "WHERE hg.hg_id IN (".$hgid.") "
            . $aclHostgroups.$aclHosts;
        
        $DBRESULT = $this->pearDB->query($queryHostgroup);
        $hostList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $hostList[] = array('id' => htmlentities($data['host_id']), 'text' => $data['host_name']);
        }
        
        return $hostList;
    }
    
}