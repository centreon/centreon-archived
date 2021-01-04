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

class MenuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CentreonDb The database mock
     */
    private $db;

    public function setUp(): void
    {
        $this->db = new CentreonDB();
    }

    /**
     *
     */
    public function testGetGroups()
    {
        $this->db->addResultSet(
            'SELECT topology_name, topology_parent, topology_group FROM topology WHERE topology_show = "1" AND topology_page IS NULL ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_parent' => 2,
                    'topology_group' => 201
                ),
                array(
                    'topology_name' => 'By services',
                    'topology_parent' => 2,
                    'topology_group' => 202
                )
            )
        );
        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getGroups(2),
            array(
                2 =>
                    array(
                        201 => 'By host',
                        202 => 'By services'
                    )
            )
        );
    }

    /**
     *
     */
    public function testGetColor()
    {
        $colorPageId3 = '#E4932C';
        $menu = new Menu($this->db);

        $this->assertEquals(
            $menu->getColor(3),
            $colorPageId3
        );
    }

    /**
     *
     */
    public function testGetMenuLevelOne()
    {
        $result = array(
            "p2" => array(
                "label" => "By host",
                "menu_id" => "By host",
                "url" => "centreon/20101",
                "active" => false,
                "color" => "#85B446",
                "children" => array(),
                'options' => '&o=c',
                'is_react' => 0
            )
        );

        $this->db->addResultSet(
            'SELECT topology_name, topology_parent, topology_group FROM topology WHERE topology_show = "1" AND topology_page IS NULL ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_parent' => '',
                    'topology_group' => 201
                )
            )
        );

        $this->db->addResultSet(
            'SELECT topology_name, topology_page, topology_url, topology_url_opt, topology_group, topology_order, topology_parent, is_react FROM topology WHERE topology_show = "1" AND topology_page IS NOT NULL ORDER BY topology_parent, topology_group, topology_order, topology_page',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_page' => 2,
                    'topology_url' => 'centreon/20101',
                    'topology_url_opt' => '&o=c',
                    'topology_parent' => '',
                    'topology_order' => 1,
                    'topology_group' => 201,
                    'is_react' => 0
                )
            )
        );

        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getMenu(),
            $result
        );
    }


    /**
     *
     */
    public function testGetMenuLevelTwo()
    {
        $result = array(
            "p2" => array(
                "children" => array(
                    '_201' => array(
                        "label" => 'By host',
                        "url" => 'centreon/20101',
                        "active" => false,
                        "children" => array(),
                        'options' => '&o=c',
                        'is_react' => 0
                    )
                )
            )
        );

        $this->db->addResultSet(
            'SELECT topology_name, topology_parent, topology_group FROM topology WHERE topology_show = "1" AND topology_page IS NULL ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_parent' => 2,
                    'topology_group' => 201
                )
            )
        );

        $this->db->addResultSet(
            'SELECT topology_name, topology_page, topology_url, topology_url_opt, topology_group, topology_order, topology_parent, is_react FROM topology WHERE topology_show = "1" AND topology_page IS NOT NULL ORDER BY topology_parent, topology_group, topology_order, topology_page',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_page' => 201,
                    'topology_url' => 'centreon/20101',
                    'topology_url_opt' => '&o=c',
                    'topology_parent' => 2,
                    'topology_order' => 1,
                    'topology_group' => 201,
                    'is_react' => 0
                )
            )
        );

        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getMenu(),
            $result
        );
    }

    /**
     *
     */
    public function testGetMenuLevelThree()
    {
        $result = array(
            "p2" => array(
                "children" => array(
                    '_201' => array(
                        "children" => array(
                            "Main Menu" => array(
                                '_20101' => array(
                                    "label" => "By host",
                                    "url" => "centreon/20101",
                                    "active" => false,
                                    'options' => '&o=c',
                                    'is_react' => 0
                                )
                            )
                        )
                    )
                )
            )
        );

        $this->db->addResultSet(
            'SELECT topology_name, topology_parent, topology_group FROM topology WHERE topology_show = "1" AND topology_page IS NULL ORDER BY topology_group, topology_order',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_parent' => 2,
                    'topology_group' => 201
                )
            )
        );

        $this->db->addResultSet(
            'SELECT topology_name, topology_page, topology_url, topology_url_opt, topology_group, topology_order, topology_parent, is_react FROM topology WHERE topology_show = "1" AND topology_page IS NOT NULL ORDER BY topology_parent, topology_group, topology_order, topology_page',
            array(
                array(
                    'topology_name' => 'By host',
                    'topology_page' => 20101,
                    'topology_url' => 'centreon/20101',
                    'topology_url_opt' => '&o=c',
                    'topology_parent' => 201,
                    'topology_order' => 1,
                    'topology_group' => 201,
                    'is_react' => 0
                )
            )
        );

        $menu = new Menu($this->db);
        $this->assertEquals(
            $menu->getMenu(),
            $result
        );
    }
}
