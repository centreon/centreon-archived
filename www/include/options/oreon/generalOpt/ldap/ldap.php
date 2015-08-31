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

require_once $centreon_path.'www/class/centreonLDAP.class.php';
$tpl = new Smarty();

if (isset($_REQUEST['ar_id']) || isset($_REQUEST['new'])) {
    include $centreon_path.'www/include/options/oreon/generalOpt/ldap/form.php';
} else {
    $ldapAction = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
    if (!is_null($ldapAction) && isset($_REQUEST['select']) && is_array($_REQUEST['select'])) {
        $select = $_REQUEST['select'];
        $ldapConf = new CentreonLdapAdmin($pearDB);
        switch ($ldapAction) {
            case "d": 
                $ldapConf->deleteConfiguration($select);
                break;
            case "ms":
                $ldapConf->setStatus(1, $select);
                break;
            case "mu":
                $ldapConf->setStatus(0, $select);
                break;
            default:
                break;
        }
    }
    include $centreon_path.'www/include/options/oreon/generalOpt/ldap/list.php';
}
?>
