<?php
/**
 * Copyright 2005-2018 Centreon
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

namespace CentreonLegacy\Core\Menu;

use \Centreon\Test\Mock\CentreonDB;

class MenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CentreonDb The database mock
     */
    private $db;

    public function setUp()
    {
        $this->db = new CentreonDB();
    }

    public function testGetParentIdHasParent()
    {
        /* Test has a parent id */
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => 2
                )
            ),
            array(
                ':page' => 201
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals($menu->getParentId(201), 2);
    }

    public function testGetParentIdHasNotParent()
    {
        /* Test has a parent id */
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => null
                )
            ),
            array(
                ':page' => 201
            )
        );
        $menu = new Menu($this->db);
        $this->assertNull($menu->getParentId(201));
    }

    public function testGetParentsIdLevelThree()
    {
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => 201
                )
            ),
            array(
                ':page' => 20101
            )
        );
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => 2
                )
            ),
            array(
                ':page' => 201
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getParentsId(20101),
            array(
                'levelOne' => 2,
                'levelTwo' => 201
            )
        );
    }

    public function testGetParentsIdLevelTwo()
    {
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => 2
                )
            ),
            array(
                ':page' => 201
            )
        );
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => null
                )
            ),
            array(
                ':page' => 2
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getParentsId(201),
            array(
                'levelOne' => 2,
                'levelTwo' => 201
            )
        );
    }

    public function testGetParentsIdLevelOne()
    {
        $this->db->addResultSet(
            'SELECT topology_parent FROM topology WHERE topology_page = :page',
            array(
                array(
                    'topology_parent' => null
                )
            ),
            array(
                ':page' => 2
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getParentsId(2),
            array(
                'levelOne' => 2,
                'levelTwo' => null
            )
        );
    }

    public function testGetGroupsWithParentNull()
    {
        $this->db->addResultSet(
            'SELECT topology_name, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NULL AND topology_parent = :parent
            ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_group' => 201
                ),
                array(
                    'topology_name' => 'By services',
                    'topology_group' => 202
                )
            ),
            array(
                ':parent' => null
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getGroups(),
            array(
                201 => 'By host',
                202 => 'By services'
            )
        );
    }

    public function testGetGroupsWithParentNotNull()
    {
        $this->db->addResultSet(
            'SELECT topology_name, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NULL AND topology_parent = :parent
            ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_group' => 201
                ),
                array(
                    'topology_name' => 'By services',
                    'topology_group' => 202
                )
            ),
            array(
                ':parent' => 2
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getGroups(2),
            array(
                201 => 'By host',
                202 => 'By services'
            )
        );
    }

    public function testGetMenuChildrenLevelOne()
    {
        $this->db->addResultSet(
            'SELECT topology_name, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NULL AND topology_parent = :parent
            ORDER BY topology_group, topology_order',
            array(),
            array(
                ':parent' => null
            )
        );
        $this->db->addResultSet(
            'SELECT topology_name, topology_page, topology_url, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NOT NULL AND topology_parent IS NULL ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'Home',
                    'topology_page' => 1,
                    'topology_url' => 'main.php?p=1',
                    'topology_group' => null
                ),
                array(
                    'topology_name' => 'Monitoring',
                    'topology_page' => 2,
                    'topology_url' => 'main.php?p=2',
                    'topology_group' => null
                )
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getMenuChildren(),
            array(
                array(
                    'id' => 1,
                    'label' => 'Home',
                    'url' => 'main.php?p=1'
                ),
                array(
                    'id' => 2,
                    'label' => 'Monitoring',
                    'url' => 'main.php?p=2'
                )
            )
        );
    }

    public function testGetMenuChildrenLevelThreeWithGroups()
    {
        $this->db->addResultSet(
            'SELECT topology_name, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NULL AND topology_parent = :parent
            ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_group' => 201
                ),
                array(
                    'topology_name' => 'By services',
                    'topology_group' => 202
                )
            ),
            array(
                ':parent' => 2
            )
        );
        $this->db->addResultSet(
            'SELECT topology_name, topology_page, topology_url, topology_group FROM topology
            WHERE topology_show = "1" AND topology_page IS NOT NULL AND topology_parent = :parent ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'Status',
                    'topology_page' => 20101,
                    'topology_url' => 'main.php?p=20101',
                    'topology_group' => 201
                ),
                array(
                    'topology_name' => 'Status by group',
                    'topology_page' => 20102,
                    'topology_url' => 'main.php?p=20102',
                    'topology_group' => 201
                ),
                array(
                    'topology_name' => 'Status',
                    'topology_page' => 20201,
                    'topology_url' => 'main.php?p=20201',
                    'topology_group' => 202
                )
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getMenuChildren(2),
            array(
                array(
                    'id' => 201,
                    'label' => 'By host',
                    'children' => array(
                        array(
                            'id' => 20101,
                            'label' => 'Status',
                            'url' => 'main.php?p=20101'
                        ),
                        array(
                            'id' => 20102,
                            'label' => 'Status by group',
                            'url' => 'main.php?p=20102'
                        )
                    )
                ),
                array(
                    'id' => 202,
                    'label' => 'By services',
                    'children' => array(
                        array(
                            'id' => 20201,
                            'label' => 'Status',
                            'url' => 'main.php?p=20201'
                        )
                    )
                )
            )
        );
    }
}
