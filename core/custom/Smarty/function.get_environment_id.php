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
 *
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.get_environment_id.php
 * Type:     function
 * Name:     get_environment_id
 * Purpose:  returns The id of environment
 * -------------------------------------------------------------
 * @todo - use cache here, LEFT JOIN may have performance issues !
 */
function smarty_function_get_environment_id($params, $template)
{
    $di = \Centreon\Internal\Di::getDefault();
    $router = $di->get('router');
    $route = $router->getCurrentUri();
    $db = $di->get('db_centreon');

    $arr = array('envid' => 0, 'subid' => 0, 'childid' => 0);

    /* Get environment */
    $stmt = $db->prepare("SELECT m1.parent_id as envid, m1.menu_id as subid, 
            m1.url as lvl1_url, m2.url as lvl2_url, m2.menu_id as childid
        FROM menus m1 LEFT JOIN menus m2 ON m1.menu_id = m2.parent_id
        WHERE m1.parent_id IN (SELECT menu_id FROM menus WHERE parent_id IS NULL) 
        ORDER BY LENGTH(m2.url) DESC, LENGTH(m1.url) DESC");
    $stmt->execute();
    $len = 0;
    while ($row = $stmt->fetch()) {
	$url = is_null($row['lvl1_url']) ? $row['lvl2_url'] : $row['lvl1_url'];
        if (preg_match("/^".preg_quote($url, '/')."/", $route, $matches)) {
            $arr['envid'] = $row['envid'];
            $arr['subid'] = $row['subid'];
            $arr['childid'] = is_null($row['childid']) ? 0 : $row['childid'];
            break;
        }
    }
    return json_encode($arr);
}
