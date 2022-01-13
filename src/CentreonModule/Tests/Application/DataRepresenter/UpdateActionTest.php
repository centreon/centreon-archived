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
use CentreonModule\Application\DataRepresenter\UpdateAction;
use CentreonModule\Application\DataRepresenter\ModuleEntity;

class UpdateActionTest extends TestCase
{
    /**
     * @var Module
     */
    private $entity;

    public function setUp(): void
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

        $this->entity = new Module();
        $this->entity->setId($data['id']);
        $this->entity->setType($data['type']);
        $this->entity->setName($data['name']);
        $this->entity->setAuthor($data['author']);
        $this->entity->setVersionCurrent($data['versionCurrent']);
        $this->entity->setVersion($data['version']);
        $this->entity->setLicense($data['license']);
    }

    public function testJsonSerialize(): void
    {
        $this->entity = $this->entity;
        $message = 'OK';

        $controlResult = [
            'entity' => new ModuleEntity($this->entity),
            'message' => $message,
        ];

        $dataRepresenter = new UpdateAction($this->entity, $message);
        $result = $dataRepresenter->jsonSerialize();

        $this->assertEquals($result, $controlResult);
    }

    /**
     * @covers \CentreonModule\Application\DataRepresenter\UpdateAction::jsonSerialize
     */
    public function testJsonSerializeWithoutEntityAndMessage(): void
    {
        $controlResult = [
            'entity' => null,
            'message' => null,
        ];

        $dataRepresenter = new UpdateAction();
        $result = $dataRepresenter->jsonSerialize();

        $this->assertEquals($result, $controlResult);
    }
}
