<?php 
/*
 * Copyright 2005-2017 Centreon
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
 */

class CentreonFormData
{
    private $attributes;
    private $apiURL;


    public function __construct()
    {
        $this->apiURL = "./api/rest/internal.php";
        $this->setAttributes();
    }

    private function setAttributes();
    {
        $this->attributes = array();
        $this->setObject('timeperiod', true, 'centreonTimeperiod', true);
        $this->setObject('timezone', true, 'centreonGMT', true);
        $this->setObject('command', false, 'centreonCommand', false);
        $this->setObject('notif_command', false, 'centreonCommand', false, 1);
        $this->setObject('check_command', false, 'centreonCommand', false, 2);
        $this->setObject('misc_command', false, 'centreonCommand', false, 3);
        $this->setObject('disco_command', false, 'centreonCommand', false, 4);
        $this->setObject('command', false, 'centreonCommand', false);
        $this->setObject('contact', true, 'centreonContact', true);
        $this->setObject('contactgroup', true, 'centreonContactgroup', true);
        $this->setObject('host', true, 'centreonHost', true);
        $this->setObject('hosttemplates', true, 'centreonHosttemplates', true);
        $this->setObject('hostgroup', true, 'centreonHostgroups', true);
        $this->setObject('hostcategory', true, 'centreonHostcategories', true);
        $this->setObject('service', true, 'centreonService', true);
        $this->setObject('servicetemplate', true, 'centreonServicetemplates', true);
        $this->setObject('meta', true, 'centreonMeta', true);
        $this->setObject('aclgroup', true, 'centreonAclGroup', true);
        $this->setObject('servicecategory', true, 'centreonServicecategories', true);
        $this->setObject('trap', true, 'centreonTraps', true);
        $this->setObject('graphtemplate', true, 'centreonGraphTemplate', false);
    }

    public function getData($object, $defaultDatasetParams)
    {
        if (isset($this->attributes[$object])) {
            if (isset($defaultDatasetParams)) {
                $url = $this->apiURL."?";
                foreach ($defaultDatasetParams as $param => $value) {
                    $url .= $param . "=" . $value . "&";
                }
                return array_merge($this->attributes[$object], array('defaultDatasetRoute', $url));
            } else {
                return $this->attributes[$object];
            }
        }
    }

    private function setObject($object, $multiple, $classObject, $availableDataSetRoute = false, $type = '')
    {
        $this->attributes[$object] = array(
            'datasourceOrigin' => 'ajax',
            'multiple' => $multiple,
            'linkedObject' => $classObject
        );
        if (isset($availableDatasetRoute) && $availableDatasetRoute) {
            $this->attributes[$object]['availableDatasetRoute'] = $this->apiURL.'?object=centreon_configuration_'.$object.'&action=list';
            if ($type != '') {
                $this->attributes[$object]['availableDatasetRoute'] .= "&t=$type";
            }
        }

    }

    private function setTimeperiods()
    {
        $this->attributes['tp'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_timeperiod&action=list',
            'multiple' => false,
            'linkedObject' => 'centreonTimeperiod'
        );
    }

    private function setTimezones()
    {
        $this->attributes['timezone'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_timezone&action=list',
            'multiple' => false,
            'linkedObject' => 'centreonGMT'
        );
    }

    private function setCommands()
    {
        $this->attributes['cmd'] = array(
            'datasourceOrigin' => 'ajax',
            'multiple' => false,
            'linkedObject' => 'centreonCommand'
        );
    }

    private function setContacts()
    {
        $this->attributes['contact'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_contact&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonContact'
        );
    }
    
    private function setContactGroups()
    {
        $this->attributes['cg'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_contactgroup&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonContactgroup'
        );      
    }

    private function setHosts()
    {
        $this->attributes['host'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_host&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonHost'
        );
    }
    
    private function setHostTemplates()
    {
        $this->attributes['htpl'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_hosttemplates&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonHosttemplates'
        );
    }

    private function setHostGroups()
    {
        $this->attributes['hg']  = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_hostgroup&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonHostgroups'
        );
    }

    private function setHostCategories()
    {
        $this->attributes['hc'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_hostcategory&action=list&t=c',
            'multiple' => true,
            'linkedObject' => 'centreonHostcategories'
        );  
    }

    private function setServices()
    {
        $this->attributes['svc'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_service&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonService'
        );        
    }

    private function setServiceTemplates()
    {
        $this->attributes['stpl'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_servicetemplate&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonServicetemplates'
        );
    }

    private function setServiceMetas()
    {
        $this->attributes['meta'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_configuration_meta&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonMeta'
        );
    }
    
    private function setACLGroups()
    {
        $this->attributes['aclgroup'] = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => $this->apiURL.'?object=centreon_administration_aclgroup&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonAclGroup'
        );
    }
    
}
