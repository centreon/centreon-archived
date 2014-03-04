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
    $queryFindRoute = "SELECT name, parent_id FROM menus WHERE url = :url";
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
    $queryGetParent = "SELECT name, parent_id, url FROM menus WHERE menu_id = :menu_id";
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
