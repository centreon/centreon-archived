<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 *
 */
namespace Centreon\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Entity\Topology;
use Centreon\Application\DataRepresenter\Topology\NavigationList;

/**
 * @group Centreon
 * @group DataRepresenter
 */
class NavigationListTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $url = 'http://loca';
        $page = 'my-page';

        $entities = [
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(1);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('1');
                $entity->setTopologyParent(null);
                $entity->setTopologyGroup(null);
                $entity->setTopologyName('menu-A-lvl-1');
                $entity->setIsReact('0');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(2);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('2');
                $entity->setTopologyParent(null);
                $entity->setTopologyGroup(null);
                $entity->setTopologyName('menu-B-lvl-1');
                $entity->setIsReact('1');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(3);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('123');
                $entity->setTopologyParent(1);
                $entity->setTopologyGroup(null);
                $entity->setTopologyName('menu-C-lvl-2');
                $entity->setIsReact('0');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(4);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage(null);
                $entity->setTopologyParent(123);
                $entity->setTopologyGroup(3);
                $entity->setTopologyName('group-label');
                $entity->setIsReact('0');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(5);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('12345');
                $entity->setTopologyParent(123);
                $entity->setTopologyGroup(3);
                $entity->setTopologyName('menu-D-lvl-3');
                $entity->setIsReact('0');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(6);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('12346');
                $entity->setTopologyParent(123);
                $entity->setTopologyGroup(3);
                $entity->setTopologyName('menu-E-lvl-3');
                $entity->setIsReact('0');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(7);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('12347');
                $entity->setTopologyParent(123);
                $entity->setTopologyGroup(null);
                $entity->setTopologyName('menu-F-lvl-3');
                $entity->setIsReact('0');

                return $entity;
            })(),
            (function () use ($url) {
                $entity = new Topology();
                $entity->setTopologyId(87);
                $entity->setTopologyUrl($url);
                $entity->setTopologyPage('30101');
                $entity->setTopologyParent(301);
                $entity->setTopologyGroup(null);
                $entity->setTopologyName('orphan-lvl-3');
                $entity->setIsReact('0');

                return $entity;
            })(),
        ];

        $dataRepresenter = new NavigationList($entities, [
            'default' => [
                'color' => 'red',
            ],
            '1' => [
                'icon' => 'ico01',
                'color' => 'blue',
            ],
            '2' => [
                'icon' => 'ico02',
            ],
            '123' => [
                'icon' => 'ico03',
            ],
        ]);

        $this->assertStringEqualsFile(
            __DIR__ . '/../../../Resources/Fixture/Topology/navigation-list-01.json',
            json_encode($dataRepresenter->jsonSerialize())
        );
    }
}
