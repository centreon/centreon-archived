<?php

namespace CentreonDev\DataCollector;

use Centreon\Infrastructure\DatabaseConnection;
use PDO;
use PDOException;

/**
 * A PDO proxy which traces statements
 */
class TraceablePDO extends DatabaseConnection
{
    protected $pdo;

    protected $executedStatements = array();

    public function __construct(string $host, string $basename, string $login, string $password, int $port = 3306)
    {
        parent::__construct($host, $basename, $login, $password, $port);
        $this->setAttribute(
            PDO::ATTR_STATEMENT_CLASS,
            [TraceablePDOStatement::class, [$this]]
        );
    }

    public function exec(string $statement): int|false
    {
        return $this->profileCall('parent::exec', $statement, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function query($statement, $parameters = null, ...$parametersArgs): \PDOStatement|false
    {
        return $this->profileCall('parent::query', $statement, func_get_args());
    }

    /**
     * Profiles a call to a PDO method
     *
     * @param string $method
     * @param string $sql
     * @param array $args
     * @return mixed The result of the call
     */
    protected function profileCall($method, $sql, array $args)
    {
        $trace = new TracedStatement($sql);
        $trace->start();

        $ex = null;
        try {
            $result = call_user_func_array(array($this, $method), $args);
        } catch (PDOException $e) {
            $ex = $e;
        }

        if ($this->getAttribute(PDO::ATTR_ERRMODE) !== PDO::ERRMODE_EXCEPTION && $result === false) {
            $error = $this->errorInfo();
            $ex = new PDOException($error[2], $error[0]);
        }

        $trace->end($ex);
        $this->addExecutedStatement($trace);

        if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION && $ex !== null) {
            throw $ex;
        }

        return $result;
    }

    /**
     * Adds an executed TracedStatement
     *
     * @param TracedStatement $stmt
     */
    public function addExecutedStatement(TracedStatement $stmt)
    {
        $this->executedStatements[] = $stmt;
    }

    /**
     * Returns the accumulated execution time of statements
     *
     * @return int
     */
    public function getAccumulatedStatementsDuration()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { return $v + $s->getDuration(); });
    }

    /**
     * Returns the peak memory usage while performing statements
     *
     * @return int
     */
    public function getMemoryUsage()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { return $v + $s->getMemoryUsage(); });
    }

    /**
     * Returns the peak memory usage while performing statements
     *
     * @return int
     */
    public function getPeakMemoryUsage()
    {
        return array_reduce($this->executedStatements, function ($v, $s) { $m = $s->getEndMemory(); return $m > $v ? $m : $v; });
    }

    /**
     * Returns the list of executed statements as TracedStatement objects
     *
     * @return array
     */
    public function getExecutedStatements()
    {
        return $this->executedStatements;
    }

    /**
     * Returns the list of failed statements
     *
     * @return array
     */
    public function getFailedExecutedStatements()
    {
        return array_filter($this->executedStatements, function ($s) { return !$s->isSuccess(); });
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this, $name), $args);
    }
}