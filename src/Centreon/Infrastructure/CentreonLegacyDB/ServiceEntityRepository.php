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

namespace Centreon\Infrastructure\CentreonLegacyDB;

use CentreonDB;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use Centreon\Infrastructure\CentreonLegacyDB\EntityPersister;
use Centreon\Infrastructure\CentreonLegacyDB\Mapping;

/**
 * Compatibility with Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
 */
abstract class ServiceEntityRepository
{
    /**
     * @var \CentreonDB
     */
    protected $db;

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $manager;

    /**
     * @var \Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var \Centreon\Infrastructure\CentreonLegacyDB\EntityPersister
     */
    protected $entityPersister;

    /**
     * Get class name and namespace of the Entity
     *
     * <example>
     * public static function entityClass(): string
     * {
     *      return MyEntity::class;
     * }
     * </example>
     *
     * @return string
     */
    public static function entityClass(): string
    {
        return str_replace(
            '\\Domain\\Repository\\',
            '\\Domain\\Entity\\', // change namespace
            substr(get_called_class(), 0, -10) // remove class name suffix "Repository"
        );
    }

    /**
     * Construct
     *
     * @param \CentreonDB $db
     * @param \Centreon\Infrastructure\Service\CentreonDBManagerService $manager
     */
    public function __construct(CentreonDB $db, CentreonDBManagerService $manager = null)
    {
        $this->db = $db;
        $this->manager = $manager;
        $this->classMetadata = new Mapping\ClassMetadata();

        // load metadata for Entity implemented MetadataInterface
        $this->loadMetadata();
    }

    /**
     * Load ClassMetadata with data from the Entity
     *
     * @return void
     */
    protected function loadMetadata(): void
    {
        if (is_subclass_of(static::entityClass(), Mapping\MetadataInterface::class)) {
            (static::entityClass())::loadMetadata($this->classMetadata);

            // prepare the Entity persister
            $this->entityPersister = new EntityPersister(static::entityClass(), $this->classMetadata);
        }
    }

    public function getEntityPersister(): ?EntityPersister
    {
        return $this->entityPersister;
    }

    /**
     * Get ClassMetadata
     *
     * @return \Centreon\Infrastructure\CentreonLegacyDB\Mapping\ClassMetadata
     */
    public function getClassMetadata(): Mapping\ClassMetadata
    {
        return $this->classMetadata;
    }

    /**
     * This method will update the relation table to clean up old data and add the missing
     *
     * @param array $list
     * @param int $id
     * @param string $tableName
     * @param string $columnA
     * @param string $columnB
     */
    protected function updateRelationData(array $list, int $id, string $tableName, string $columnA, string $columnB)
    {
        $listExists = [];
        $listAdd = [];
        $listRemove = [];

        $rows = (function () use ($id, $tableName, $columnA, $columnB) {
            $sql = "SELECT `{$columnB}` FROM `{$tableName}` WHERE `{$columnA}` = :{$columnA} LIMIT 0, 5000";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            return $rows;
        })();

        // to remove
        foreach ($rows as $row) {
            $pollerId = $row[$columnB];
            if (!in_array($pollerId, $list)) {
                $listRemove[] = $pollerId;
            }

            $listExists[] = $pollerId;
            unset($row, $pollerId);
        }

        // to add
        foreach ($list as $pollerId) {
            if (!in_array($pollerId, $listExists)) {
                $listAdd[] = $pollerId;
            }
            unset($pollerId);
        }

        // removing
        foreach ($listRemove as $pollerId) {
            (function () use ($id, $pollerId, $tableName, $columnA, $columnB) {
                $sql = "DELETE FROM `{$tableName}` WHERE `{$columnA}` = :{$columnA} AND `{$columnB}` = :{$columnB}";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
                $stmt->bindValue(":{$columnB}", $pollerId, \PDO::PARAM_INT);
                $stmt->execute();
            })();
            unset($pollerId);
        }

        // adding
        foreach ($listAdd as $pollerId) {
            (function () use ($id, $pollerId, $tableName, $columnA, $columnB) {
                $sql = "INSERT INTO `{$tableName}` (`{$columnA}`, `{$columnB}`)  VALUES (:{$columnA}, :$columnB)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(":{$columnA}", $id, \PDO::PARAM_INT);
                $stmt->bindValue(":{$columnB}", $pollerId, \PDO::PARAM_INT);
                $stmt->execute();
            })();
            unset($pollerId);
        }
    }
}
