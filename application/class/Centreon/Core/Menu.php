<?php
namespace Centreon\Core;

class Menu
{
    private $tree;

    public function __construct()
    {
        $this->setMenu();
        echo "<pre>",print_r($this->getMenu(3), true),"<pre>";
    }

    private function buildTree(array $elements, $parentId = 0) 
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['menu_id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$element['menu_id']] = $element;
            }
        }
        return $branch;
    }

    private function setMenu()
    {
        $db = Di::getDefault()->get('db_centreon');
        $this->tree = array();
        $stmt = $db->prepare("
            SELECT menu_id, name, short_name, parent_id, url, icon_class, icon, menu_order
            FROM menus"
        );
        $stmt->execute();
        $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->tree = $this->buildTree($menus);
    }

    public function getMenu($menuId = null, $tree = null)
    {
        if (is_null($menuId)) {
            return $this->tree;
        }
        if (is_null($tree)) {
            $tree = $this->tree;
        }
        foreach ($tree as $k => $v) {
            if ($k == $menuId) {
                return $v;
            }
            if (isset($v['children'])) {
                return $this->getMenu($menuId, $v['children']);
            }
        }
        return array();
    }
}
