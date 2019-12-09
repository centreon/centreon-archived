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

namespace CentreonUser\Domain\Entity;

use Centreon\Infrastructure\CentreonLegacyDB\Mapping;
use Symfony\Component\Serializer\Annotation as Serializer;
use PDO;

/**
 * Timeperiod entity
 *
 * @codeCoverageIgnore
 */
class Timeperiod implements Mapping\MetadataInterface
{
    const SERIALIZER_GROUP_LIST = 'timeperiod-list';

    const TABLE = 'timeperiod';
    const ENTITY_IDENTIFICATOR_COLUMN = 'tp_id';

    /**
     * @Serializer\Groups({Timeperiod::SERIALIZER_GROUP_LIST})
     * @var int an identification of entity
     */
    private $id;

    /**
     * @Serializer\Groups({Timeperiod::SERIALIZER_GROUP_LIST})
     * @var string
     */
    private $name;

    /**
     * @Serializer\Groups({Timeperiod::SERIALIZER_GROUP_LIST})
     * @var string
     */
    private $alias;

    /**
     * {@inheritdoc}
     */
    public static function loadMetadata(Mapping\ClassMetadata $metadata): void
    {
        $metadata->setTableName(static::TABLE)
            ->add('id', 'tp_id', PDO::PARAM_INT, null, true)
            ->add('name', 'tp_name', PDO::PARAM_STR)
            ->add('alias', 'tp_alias', PDO::PARAM_STR);
    }

    public function setId(int $id = null): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setAlias(string $alias = null): void
    {
        $this->alias = $alias;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
