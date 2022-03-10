<?php

/*
 * Copyright 2005-2021 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';
require_once _CENTREON_PATH_ . 'www/class/CentreonLDAPAdmin.class.php';
$tpl = new Smarty();

/**
 * used to sanitize key and value in array
 * @param array<mixed> $inputArray
 * @return array<mixed>
 */
function sanitizeInputArray(array $inputArray): array
{
    $sanitizedArray = [];
    foreach ($inputArray as $key => $value) {
        $key = filter_var($key, FILTER_VALIDATE_INT);
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if (false !== $key && false !== $value) {
            $sanitizedArray[$key] = $value;
        }
    }
    return $sanitizedArray;
}

if (isset($_REQUEST['ar_id']) || isset($_REQUEST['new'])) {
    $_REQUEST['ar_id']  = filter_var($_REQUEST['ar_id'] ?? null, FILTER_VALIDATE_INT);
    $_REQUEST['new']    = filter_var($_REQUEST['new'] ?? null, FILTER_VALIDATE_INT);
    include _CENTREON_PATH_ . 'www/include/Administration/parameters/ldap/form.php';
} else {
    $ldapAction = filter_var($_REQUEST['a'] ?? null, FILTER_SANITIZE_STRING);
    if (!is_null($ldapAction) && isset($_REQUEST['select']) && is_array($_REQUEST['select'])) {
        $select = sanitizeInputArray($_REQUEST['select']);
        $ldapConf = new CentreonLdapAdmin($pearDB);
        switch ($ldapAction) {
            case "d":
                purgeOutdatedCSRFTokens();
                if (isCSRFTokenValid()) {
                    purgeCSRFToken();
                    $ldapConf->deleteConfiguration($select);
                } else {
                    unvalidFormMessage();
                }
                break;
            case "ms":
                purgeOutdatedCSRFTokens();
                if (isCSRFTokenValid()) {
                    purgeCSRFToken();
                    $ldapConf->setStatus(1, $select);
                } else {
                    unvalidFormMessage();
                }
                break;
            case "mu":
                purgeOutdatedCSRFTokens();
                if (isCSRFTokenValid()) {
                    purgeCSRFToken();
                    $ldapConf->setStatus(0, $select);
                } else {
                    unvalidFormMessage();
                }
                break;
            default:
                break;
        }
    }
    include _CENTREON_PATH_ . 'www/include/Administration/parameters/ldap/list.php';
}
