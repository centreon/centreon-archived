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
        FROM cfg_menus m1 LEFT JOIN cfg_menus m2 ON m1.menu_id = m2.parent_id
        WHERE m1.parent_id IN (SELECT menu_id FROM cfg_menus WHERE parent_id IS NULL) 
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
