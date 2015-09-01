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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.get_breadcrumb.php
 * Type:     function
 * Name:     get_breadcrumb
 * Purpose:  returns The list for breadcrumb
 * -------------------------------------------------------------
 */
function smarty_function_get_breadcrumb($params, $template)
{
    $di = \Centreon\Internal\Di::getDefault();
    $router = $di->get('router');
    $route = $router->getCurrentUri();
    $baseUrl = $di->get('config')->get('global', 'base_url');
    $route = str_replace($baseUrl, '/', $route);
    $breadcrumb = array();
    $db = $di->get('db_centreon');
    /* Get in database */
    $queryFindRoute = "SELECT name, parent_id FROM cfg_menus WHERE url = :url";
    $parentId = null;
    $stmt = $db->prepare($queryFindRoute);
    $stmt->bindParam(':url', $route, \PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch();
    $stmt->closeCursor();
    if ($row !== false) {
        $parentId = $row['parent_id'];
	    $breadcrumb[] = array(
		    'name' => $row['name'],
            'url' => null
        );
    } else {
        $route = dirname($route);
        $stmt = $db->prepare($queryFindRoute);
        $stmt->bindParam(':url', $route, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row === false) {
            return '';
        }
        $parentId = $row['parent_id'];
        $breadcrumb[] = array(
            'name' => $row['name'],
            'url' => null
        );
    }
    $queryGetParent = "SELECT name, parent_id, url FROM cfg_menus WHERE menu_id = :menu_id";
    while (false === is_null($parentId)) {
        $stmt = $db->prepare($queryGetParent);
        $stmt->bindParam(':menu_id', $parentId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row === false) {
            $parentId = null;
        }
        $parentId = $row['parent_id'];
        array_unshift($breadcrumb, array(
            'name' => $row['name'],
            'url' => $row['url']
        ));
    }
    $nbLink = count($breadcrumb);
    $string = '';
    for ($i = 0; $i < $nbLink; $i++) {
        $string .= '<li';
        if ($i == $nbLink - 1) {
            $string .= ' class="active"';
        }
        $string .= '>';
        if ($i != $nbLink - 1 && !is_null($breadcrumb[$i]['url'])) {
            $string .= '<a href="' + $breadcrumb[$i]['url'] + '">';
        }
        $string .= $breadcrumb[$i]['name'];
        if ($i != $nbLink - 1 && !is_null($breadcrumb[$i]['url'])) {
            $string .= '</a>';
        }
        $string .= '</li>';
    }
    return $string;
}
