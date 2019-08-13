<?php

namespace Centreon\Infrastructure;

use PDO;

/**
 * @package Centreon\Infrastructure
 */
class DatabaseConnection extends \PDO
{
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $basename;
    /**
     * @var string
     */
    private $login;
    /**
     * @var string
     */
    private $password;
    /**
     * @var int
     */
    private $port;

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
        $this->host = $host;
        $this->port = $port;
        $this->basename = $basename;
        $this->login = $login;
        $this->password = $password;
        $dsn = "mysql:dbname={$basename};host={$host};port={$port}";
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION

        );
        parent::__construct($dsn, $this->login, $this->password, $options);
    }
}
