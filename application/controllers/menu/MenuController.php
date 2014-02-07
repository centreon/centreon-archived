<?php

namespace Controllers\Menu;

class MenuController extends \Centreon\Core\Controller
{
    /**
     * Get menu
     *
     * @method get
     * @route /menu/getmenu/
     */
    public function getmenuAction()
    {
        $params = $this->getParams("get");
        $menu = \Centreon\Core\Di::getDefault()->get('menu');
        $menu_id = null;
        if (isset($params->menu_id)) {
            $menu_id = $params->menu_id;
        }
        $menudata = $menu->getMenu($menu_id);
        $result = array(
            'success' => 1,
            'menu' => $menudata['children']
        );
        echo json_encode($result);
    }
}
