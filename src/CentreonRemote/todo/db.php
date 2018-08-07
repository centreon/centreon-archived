<?php

class db
{

    /** @var PDO */
    protected $pdo;

    protected $count = 0;
    protected $error = false;
    protected $errorInfo = '';
    protected $query;
    protected $result;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $query
     * @param array $params
     *
     * @return $this
     */
    public function query($query, $params = [])
    {
        $this->error = false;
        $this->errorInfo = '';

        $this->query = $this->pdo->prepare($query);

        if ($this->query){
            if (is_array($params) && !empty($params)){
                $x = 1;

                foreach ($params as $param) {
                    if (is_array($param) && !empty($param)){
                        foreach ($param as $p) {
                            $this->query->bindValue($x, $p);
                            $x++;
                        }
                    } else {
                        $this->query->bindValue($x, $param);
                        $x++;
                    }
                }
            }

            if ($this->query->execute()){
                $this->result = $this->query->fetchAll(PDO::FETCH_OBJ);
                $this->count = $this->query->rowCount();
            } else {
                $this->error = true;
                $this->errorInfo = $this->query->errorInfo();
            }

        }

        return $this;
    }

    /**
     * @param string $table
     * @param array $fields
     *
     * @return bool|int Last inserted ID
     */
    public function insert($table, array $fields)
    {
        $keys = array_keys($fields);
        $values = '?' . str_repeat(", ?", count($fields) - 1);

        $sql = "INSERT INTO {$table} (`". implode('`,`', $keys) ."`) VALUES ({$values})";

        $this->query($sql, $fields);

        return $this->passes() ? $this->pdo->lastInsertId() : false;
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
}
