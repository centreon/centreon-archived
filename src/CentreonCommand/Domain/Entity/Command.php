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

namespace CentreonCommand\Domain\Entity;

/**
 * Command entity
 *
 * @codeCoverageIgnore
 */
class Command
{

    const TABLE = 'command';
    const TYPE_NOTIFICATION = 1;
    const TYPE_CHECK = 2;
    const TYPE_MISC = 3;
    const TYPE_DISCOVERY = 4;

    /**
     * @var int an identification of entity
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Convert type from string to integer
     *
     * @param string $name
     * @return int|null
     */
    public static function getTypeIdFromName(string $name): ?int
    {
        switch ($name) {
            case 'notification':
                return static::TYPE_NOTIFICATION;
                break;
            case 'check':
                return static::TYPE_CHECK;
                break;
            case 'misc':
                return static::TYPE_MISC;
                break;
            case 'discovery':
                return static::TYPE_DISCOVERY;
                break;
        }

        return null;
    }
}
