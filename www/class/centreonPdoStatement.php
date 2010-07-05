<?php

class CentreonPdoStatement extends PDOStatement
{
    public $dbh;

    protected function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function fetchRow()
    {
        return $this->fetch();
    }

    public function numRows()
    {
        return $this->rowCount();
    }

    public function free()
    {
        ;
    }
}