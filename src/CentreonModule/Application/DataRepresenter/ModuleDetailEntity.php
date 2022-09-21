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

namespace CentreonModule\Application\DataRepresenter;

use JsonSerializable;
use CentreonModule\Infrastructure\Entity\Module;

class ModuleDetailEntity implements JsonSerializable
{

    /**
     * @var \CentreonModule\Infrastructure\Entity\Module
     */
    private $entity;

    /**
     * Construct
     *
     * @param \CentreonModule\Infrastructure\Entity\Module $entity
     */
    public function __construct(Module $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @OA\Schema(
     *   schema="ModuleDetailEntity",
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="type", type="string", enum={"module","widget"}),
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="label", type="string"),
     *       @OA\Property(property="stability", type="string"),
     *       @OA\Property(property="version", type="object",
     *          @OA\Property(property="current", type="string"),
     *          @OA\Property(property="available", type="string"),
     *          @OA\Property(property="outdated", type="boolean"),
     *          @OA\Property(property="installed", type="boolean")
     *       ),
     *       @OA\Property(property="license", type="string"),
     *       @OA\Property(property="images", type="array", items={"string"}),
     *       @OA\Property(property="last_update", type="string"),
     *       @OA\Property(property="release_note", type="string")
     * )
     *
     * JSON serialization of entity
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize()
    {
        $outdated = $this->entity->isInstalled() && !$this->entity->isUpdated() ?
            true :
            false
        ;

        return [
            'id' => $this->entity->getId(),
            'type' => $this->entity->getType(),
            'title' => $this->entity->getName(),
            'description' => $this->entity->getDescription(),
            'label' => $this->entity->getAuthor(),
            'stability' => $this->entity->getStability(),
            'version' => [
                'current' => $this->entity->getVersionCurrent(),
                'available' => $this->entity->getVersion(),
                'outdated' => $outdated,
                'installed' => $this->entity->isInstalled(),
            ],
            'license' => $this->entity->getLicense(),
            'images' => $this->entity->getImages(),
            'last_update' => $this->entity->getLastUpdate(),
            'release_note' => $this->entity->getReleaseNote(),
        ];
    }
}
