<?php
/*
 * Copyright 2005-2012 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even 7the implied warranty of MERCHANTABILITY or FITNESS FOR A
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

/**
 * Get toolbar action list
 *
 * @param string $domName
 * @return array
 */
function getActionList($domName)
{
    $tab = array(
        'onchange' => "javascript: " .
            "if (this.form.elements['$domName'].selectedIndex == 1 && confirm('"
            . _("Do you confirm the deletion ?") . "')) {" .
            " 	setA(this.form.elements['$domName'].value); submit();} " .
            "else if (this.form.elements['$domName'].selectedIndex == 2) {" .
            " 	setA(this.form.elements['$domName'].value); submit();} " .
            "else if (this.form.elements['$domName'].selectedIndex == 3) {" .
            " 	setA(this.form.elements['$domName'].value); submit();} " .
            "this.form.elements['$domName'].selectedIndex = 0"
    );
    return $tab;
}
