<?php

namespace Centreon\Infrastructure;

use PDO;

/**
 * @package Centreon\Infrastructure
 */
class DatabaseConnection extends \PDO
{

    /**
     * Initialize the PDO connection
     *
     * @param string $host
     * @param string $basename
     * @param string $login
     * @param string $password
     * @param int $port
     */
    public function __construct(string $host, string $basename, string $login, string $password, int $port = 3306)
    {
        $dsn = "mysql:dbname={$basename};host={$host};port={$port}";
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );
        parent::__construct($dsn, $login, $password, $options);
    }
}
