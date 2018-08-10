<?php

namespace Centreon\Infrastructure\CentreonLegacyDB;

use Centreon\Infrastructure\Service\Exception\NotFoundException;
use ReflectionClass;
use CentreonDB;

class CentreonDBAdapter
{

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

        if (!$this->query){
            throw new \Exception('Error at preparing the query.');
        }

        if (is_array($params) && !empty($params)){
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
        } catch(\Exception $e) {
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
     * @return bool|int Last inserted ID
     */
    public function insert($table, array $fields)
    {
        $keys = array_keys($fields);
        $values = '?' . str_repeat(", ?", count($fields) - 1);

        $sql = "INSERT INTO {$table} (`". implode('`,`', $keys) ."`) VALUES ({$values})";

        $this->query($sql, $fields);

        return $this->passes() ? $this->db->lastInsertId() : false;
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
