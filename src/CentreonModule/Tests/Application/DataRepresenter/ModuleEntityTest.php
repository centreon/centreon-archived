<?php
/*
 * Copyright 2005-2019 Centreon
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
 *
 */

namespace CentreonModule\Tests\Application\DataRepresenter;

use PHPUnit\Framework\TestCase;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Application\DataRepresenter\ModuleEntity;

class ModuleEntityTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $data = [
            'id' => '1',
            'type' => 'module',
            'name' => 'Test Module',
            'author' => 'John Doe',
            'versionCurrent' => '1.0.0',
            'version' => '1.0.1',
            'license' => [
                'required' => true,
                'expiration_date' => '2019-04-21T00:25:55-0700',
            ],
        ];

        $entity = new Module;
        $entity->setId($data['id']);
        $entity->setType($data['type']);
        $entity->setName($data['name']);
        $entity->setAuthor($data['author']);
        $entity->setVersionCurrent($data['versionCurrent']);
        $entity->setVersion($data['version']);
        $entity->setLicense($data['license']);

        $check = function () use ($entity) {
            $outdated = $entity->isInstalled() && !$entity->isUpdated() ?
                true :
                false
            ;

            $controlResult = [
                'id' => $entity->getId(),
                'type' => $entity->getType(),
                'description' => $entity->getName(),
                'label' => $entity->getAuthor(),
                'version' => [
                    'current' => $entity->getVersionCurrent(),
                    'available' => $entity->getVersion(),
                    'outdated' => $outdated,
                    'installed' => $entity->isInstalled(),
                ],
                'license' => $entity->getLicense(),
            ];

            $dataRepresenter = new ModuleEntity($entity);
            $result = $dataRepresenter->jsonSerialize();

            $this->assertEquals($result, $controlResult);
        };

        $check();

        $entity->setInstalled(true);
        $check();
    }
}
