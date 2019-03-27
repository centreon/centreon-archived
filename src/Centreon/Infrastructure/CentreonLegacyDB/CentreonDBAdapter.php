<?php

namespace Centreon\Infrastructure\CentreonLegacyDB;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use Centreon\Infrastructure\Service\CentreonDBManagerService;
use ReflectionClass;
use CentreonDB;

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
            array_push($keys, $key . '= :' . $key);
            array_push($keyValues, array($key, $value));
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
