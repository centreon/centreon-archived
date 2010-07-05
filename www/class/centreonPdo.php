<?php

class CentreonPdo extends PDO
{
    public function __construct($dsn, $username = null, $password = null, $options = array())
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('CentreonPdo', array($this)));
    }

    public function disconnect()
    {
        ;
    }
}