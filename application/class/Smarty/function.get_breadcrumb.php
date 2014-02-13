<?php
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
    $di = \Centreon\Core\Di::getDefault();
    $router = $di->get('router');
    $route = $router->request()->pathname();
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
