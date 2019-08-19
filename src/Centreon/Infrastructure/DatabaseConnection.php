<?php

namespace Centreon\Infrastructure;

/**
 * Allow to implement PDO
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

    public function __construct(string $host, string $basename, string $login, string $password, int $port = 3306)
    {
        $this->host = $host;
        $this->port = $port;
        $this->basename = $basename;
        $this->login = $login;
        $this->password = $password;
        $dsn = "mysql:dbname={$basename};host={$host};port={$port}";
        parent::__construct($dsn, $this->login, $this->password);
    }
}
