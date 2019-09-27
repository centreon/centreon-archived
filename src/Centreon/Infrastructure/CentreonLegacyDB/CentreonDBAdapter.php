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

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use ReflectionClass;
use CentreonDB;

/**
 * Executes commands against Centreon database backend.
 */
class CentreonDBAdapter
{
    /** @var \CentreonDB */
    private $db;

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $manager;
    private $count = 0;
    private $error = false;
    private $errorInfo = '';
    private $query;
    private $result;

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
    }

    public function getRepository($repository): ServiceEntityRepository
    {
        $interface = ServiceEntityRepository::class;
        $ref = new ReflectionClass($repository);
        $hasInterface = $ref->isSubclassOf($interface);

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Repository %s must implement %s', $repository, $interface));
        }

        $repositoryInstance = new $repository($this->db, $this->manager);

        return $repositoryInstance;
    }

    public function getCentreonDBInstance(): CentreonDB
    {
        return $this->db;
    }

    /**
     * @param string $query
     * @param array  $params
     *
     * @return $this
     * @throws \Exception
     *
     */
    public function query($query, $params = [])
    {
        $this->error = false;
        $this->errorInfo = '';

        $this->query = $this->db->prepare($query);

        if (!$this->query) {
            throw new \Exception('Error at preparing the query.');
        }

        if (is_array($params) && !empty($params)) {
            $x = 1;

            foreach ($params as $param) {
                $this->query->bindValue($x, $param);
                $x++;
            }
        }

        try {
            $result = $this->query->execute();
            $isSelect = strpos(strtolower($query), 'select') !== false;

            if ($result && $isSelect) {
                $this->result = $this->query->fetchAll(\PDO::FETCH_OBJ);
                $this->count = $this->query->rowCount();
            } elseif (!$result) {
                $this->error = true;
                $this->errorInfo = $this->query->errorInfo();
            }
        } catch (\Exception $e) {
            throw new \Exception('Query failed. ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * @param string $table
     * @param array  $fields
     *
     * @return int Last inserted ID
     * @throws \Exception
     *
     */
    public function insert($table, array $fields)
    {
        if (!$fields) {
            throw new \Exception("The argument `fields` can't be empty");
        }

        $keys = [];
        $keyVars = [];

        foreach ($fields as $key => $value) {
            $keys[] = "`{$key}`";
            $keyVars[] = ":{$key}";
        }

        $columns = join(',', $keys);
        $values = join(',', $keyVars);

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";

        $stmt = $this->db->prepare($sql);

        foreach ($fields as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception('Query failed. ' . $e->getMessage());
        }

        return $this->db->lastInsertId();
    }

    /**
     * Insert data using load data infile
     *
     * @param string $file         Path and name of file to load
     * @param string $table        Table name
     * @param array  $fieldsClause Values of subclauses of FIELDS clause
     * @param array  $linesClause  Values of subclauses of LINES clause
     * @param array  $columns      Columns name
     *
     * @return void
     * @throws \Exception
     *
     */
    public function loadDataInfile(string $file, string $table, array $fieldsClause, array $linesClause, array $columns)
    {
        // SQL statement format:
        // LOAD DATA
        // INFILE 'file_name'
        // INTO TABLE tbl_name
        // FIELDS TERMINATED BY ',' ENCLOSED BY '\'' ESCAPED BY '\\'
        // LINES TERMINATED BY '\n' STARTING BY ''
        // (`col_name`, `col_name`,...)

        // Construct SQL statement
        $sql = "LOAD DATA LOCAL INFILE '$file'";
        $sql .= " INTO TABLE $table";
        $sql .= " FIELDS TERMINATED BY '" . $fieldsClause["terminated_by"] . "' ENCLOSED BY '"
            . $fieldsClause["enclosed_by"] . "' ESCAPED BY '" . $fieldsClause["escaped_by"] . "'";
        $sql .= " LINES TERMINATED BY '" . $linesClause["terminated_by"] . "' STARTING BY '"
            . $linesClause["starting_by"] . "'";
        $sql .= " (`" . implode("`, `", $columns) . "`)";

        // Prepare PDO statement.
        $stmt = $this->db->prepare($sql);

        // Execute
        try {
            $stmt->execute();
        } catch (\Exception $e) {
            throw new \Exception('Query failed. ' . $e->getMessage());
        }
    }

    /**
     * @param string $table
     * @param array  $fields
     * @param int    $id
     *
     * @return bool|int Updated ID
     * @throws \Exception
     *
     */
    public function update($table, array $fields, int $id)
    {

        $keys = [];
        $keyValues = [];

        foreach ($fields as $key => $value) {
            array_push($keys, $key . '= :' . $key);
            array_push($keyValues, [$key, $value]);
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $keys) . " WHERE id = :id";

        $qq = $this->db->prepare($sql);
        $qq->bindParam(':id', $id);

        foreach ($keyValues as $key => $value) {
            $qq->bindParam(':' . $key, $value);
        }

        try {
            $result = $qq->execute();
        } catch (\Exception $e) {
            throw new \Exception('Query failed. ' . $e->getMessage());
        }

        return $result;
    }


    public function results()
    {
        return $this->result;
    }

    public function count()
    {
        return $this->count;
    }

    public function fails()
    {
        return $this->error;
    }

    public function passes()
    {
        return !$this->error;
    }

    public function errorInfo()
    {
        return $this->errorInfo;
    }

    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollBack()
    {
        $this->db->rollBack();
    }
}
