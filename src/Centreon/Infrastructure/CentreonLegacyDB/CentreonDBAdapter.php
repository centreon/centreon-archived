<?php

namespace Centreon\Infrastructure\CentreonLegacyDB;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use CentreonDB;

class CentreonDBAdapter
{
    const BULK_SIZE = 10000;

    /** @var \CentreonDB */
    private $db;

    private $count = 0;
    private $error = false;
    private $errorInfo = '';
    private $query;
    private $result;

    /**
     * Construct
     *
     * @param \CentreonDB $db
     */
    public function __construct(CentreonDB $db)
    {
        $this->db = $db;
    }

    public function getRepository($repository): ServiceEntityRepository
    {
        $interface = ServiceEntityRepository::class;
        $ref = new ReflectionClass($repository);
        $hasInterface = $ref->isSubclassOf($interface);

        if ($hasInterface === false) {
            throw new NotFoundException(sprintf('Repository %s must implement %s', $repository, $interface));
        }

        $repositoryInstance = new $repository($this->db);

        return $repositoryInstance;
    }

    public function getCentreonDBInstance(): CentreonDB
    {
        return $this->db;
    }

    /**
     * @param string $query
     * @param array $params
     *
     * @throws \Exception
     *
     * @return $this
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
     * @param array $fields
     *
     * @throws \Exception
     *
     * @return int Last inserted ID
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
     * Generate value template place holders
     * 
     * @param string $text
     * @param int $count
     * @param string $separator
     * 
     * @return string
     */
    public function placeHolders($text, $count = 0, $separator = ",")
    {
        $result = array();
        if ($count > 0) {
            for ($x = 0; $x < $count; $x++) {
                $result[] = $text;
            }
        }
    
        return implode($separator, $result);
    }

    /**
     * Insert data using load data infile
     * 
     * @param string $file Path and name of file to load
     * @param string $table Table name
     * @param array $fieldsClause Values of subclauses of FIELDS clause
     * @param array $linesClause Values of subclauses of LINES clause
     * @param array $columns Columns name
     *
     * @throws \Exception
     *
     * @return void
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
        $sql = "LOAD DATA INFILE '$file'";
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
     * Insert data using bulk
     * 
     * @param string $table
     * @param array $data
     *
     * @throws \Exception
     *
     * @return int Last inserted ID
     */
    public function insertBulk($table, array $data)
    {
        if (!$table && !$data) {
            throw new \Exception("The argument can't be empty");
        }

        //Define needed vars
        $rowsSQL = array();
        $columnNames = array();
        $toBind = array();
        $params = array();
        $i = 0;

        foreach ($data as $row) {
            $params = array();
            $columnNames = array_keys($row);
            foreach ($row as $columnName => $columnValue) {
                $param = ":" . $columnName . $i;
                $params[] = $param;
                $toBind[$param] = $columnValue; 
            }
            $rowsSQL[] = "(" . implode(", ", $params) . ")";
            $i++;

            if ($i >= BULK_SIZE) {
                //Construct SQL statement
                $sql = "INSERT INTO `$table` (`" . implode("`, `", $columnNames) . "`) VALUES " . implode(", ", $rowsSQL);

                //Prepare PDO statement.
                $stmt = $this->db->prepare($sql);

                //Bind values.
                foreach ($toBind as $param => $val) {
                    $stmt->bindValue($param, $val);
                }

                try {
                    $stmt->execute();

                    //Reset values
                    $i = 0;
                    $rowsSQL = array();
                    $toBind = array();
                } catch (\Exception $e) {
                    throw new \Exception('Query failed. ' . $e->getMessage());
                }
            }
        }

        if ($rowsSQL) {
            //Construct SQL statement
            $rowsSQL[] = "(" . implode(", ", $params) . ")";
            $sql = "INSERT INTO `$table` (`" . implode("`, `", $columnNames) . "`) VALUES " . implode(", ", $rowsSQL);

            //Prepare PDO statement.
            $stmt = $this->db->prepare($sql);

            //Bind values.
            foreach ($toBind as $param => $val) {
                $stmt->bindValue($param, $val);
            }

            try {
                $stmt->execute();
            } catch (\Exception $e) {
                throw new \Exception('Query failed. ' . $e->getMessage());
            }
        }

        return $this->db->lastInsertId();
    }

    /**
     * @param string $table
     * @param array $fields
     * @param int $id
     *
     * @throws \Exception
     *
     * @return bool|int Updated ID
     */
    public function update($table, array $fields, int $id)
    {

        $keys = [];
        $keyValues = [];

        foreach ($fields as $key => $value) {
            array_push($keys, $key.'= :'.$key);
            array_push($keyValues, array($key, $value));
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $keys) ." WHERE id = :id";

        $qq = $this->db->prepare($sql);
        $qq->bindParam(':id', $id);

        foreach ($keyValues as $key => $value) {
            $qq->bindParam(':'.$key, $value);
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
