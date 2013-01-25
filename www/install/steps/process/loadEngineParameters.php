<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

session_start();
require_once '../functions.php';

if (!isset($_POST['engine'])) {
    echo 'Could not determine specified engine';
    exit;
}

$lines = getParamLines('../../var/engines', $_POST['engine']);
$html = "";
foreach ($lines as $line) {
    if ($line) {
        if ($line[0] == '#') {
            continue;
        }
        list($key, $label, $required, $paramType, $default) = explode(';', $line);
        $val = $default;
        if (isset($_SESSION[$key])) {
            $val = $_SESSION[$key];
        }
        $star = "";
        if ($required) {
            $star = "<span style='color:#f91e05'> *</span>";
        }
        $html .= "
                    <tr>
                    <td class='formlabel'>".$label.$star."</td>
                    <td class='formvalue'>
                        <input type='text' name='".$key."' value='".$val."' />
                        <label class='field_msg'></label>
                    </td>
                    </tr>";
    }
}
echo $html;
