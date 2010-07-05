<?php

class CentreonPdo extends PDO
{
    public function __construct($dsn, $username = null, $password = null, $options = array())
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('CentreonPdoStatement', array($this)));
    }

    public function disconnect()
    {
        ;
    }

    /**
     * returns error info
     *
     * @return string
     */
    public function toString()
    {
        $errString = "";
        $errTab = $this->errorInfo();
        if (count($errTab)) {
            $errString = implode(";", $errTab);
        }
        return $errString;
    }
}