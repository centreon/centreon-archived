<?php

require_once SMARTY_DIR . '../demo/plugins/resource.mysql.php';

class Smarty_Resource_Mysqltest extends Smarty_Resource_Mysql
{
    public function __sleep()
    {
        return array();
    }

    public function __wakeup()
    {
        $this->__construct();
    }
}
