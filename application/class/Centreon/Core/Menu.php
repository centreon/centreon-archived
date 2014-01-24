<?php
namespace Centreon\Core;

class Menu
{
    /**
     * @var array
     */
    private $tree;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setMenu();
    }

    /**
     * Takes a set of results and build a tree from it
     *
     * @param array $elements
     * @param int $parentId
     * @return array
     */
    private function buildTree(array $elements, $parentId = 0) 
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['menu_id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = array();
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * Init menu
     *
     */
    private function setMenu()
    {
        $cache = Di::getDefault()->get('cache');
        if ($cache->has('app:menu')) {
            $this->tree = $cache->get('app:menu');
            return null;
        }
        $db = Di::getDefault()->get('db_centreon');
        $this->tree = array();
        $stmt = $db->prepare("
            SELECT menu_id, name, parent_id, url, icon_class, icon, bgcolor, menu_order
            FROM menus"
        );
        $stmt->execute();
        $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->tree = $this->buildTree($menus);
        $cache->set('app:menu', $this->tree);
    }

    /**
     * Get menu, can be recursive if $menuId is set.
     * When $menuId is set, the method will return a 
     * specific branch
     *
     * @param int $menuId
     * @param array $tree
     * @return array
     */
    public function getMenu($menuId = null, $tree = null)
    {
        if (is_null($menuId)) {
            return $this->tree;
        }
        if (is_null($tree)) {
            $tree = $this->tree;
        }
        foreach ($tree as $v) {
            if ($v['menu_id'] == $menuId) {
                return $v;
            }
            if (isset($v['children'])) {
                return $this->getMenu($menuId, $v['children']);
            }
        }
        return array();
    }

    /**
     * Get menu and returns json string
     *
     * @param int $menuId
     * @return string
     */
    public function getMenuJson($menuId = null)
    {
        return json_encode($this->getMenu($menuId));
    }
}
