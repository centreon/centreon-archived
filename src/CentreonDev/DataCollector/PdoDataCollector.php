<?php

namespace CentreonDev\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use CentreonDev\DataCollector\TraceablePDO;
use Centreon\Infrastructure\DatabaseConnection;

class PdoDataCollector extends DataCollector
{
    /**
     * @var DatabaseConnection $pdo
     */
    private $pdo;

    function __construct(DatabaseConnection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $data = array(
            'nb_statements'        => 0,
            'nb_failed_statements' => 0,
            'accumulated_duration' => 0,
            'memory_usage'         => 0,
            'peak_memory_usage'    => 0,
            'statements'           => array()
        );

        $pdodata = $this->collectPDO($this->pdo);
        $data['nb_statements'] += $pdodata['nb_statements'];
        $data['nb_failed_statements'] += $pdodata['nb_failed_statements'];
        $data['accumulated_duration'] += $pdodata['accumulated_duration'];
        $data['memory_usage'] += $pdodata['memory_usage'];
        $data['peak_memory_usage'] = max($data['peak_memory_usage'], $pdodata['peak_memory_usage']);
        $data['statements'] = $pdodata['statements'];

        $data['accumulated_duration_str'] = $data['accumulated_duration'];
        $data['memory_usage_str'] = $data['memory_usage'];
        $data['peak_memory_usage_str'] = $data['peak_memory_usage'];

        $this->data = $data;
        $this->data['method'] = $request->getMethod();
        $this->data['acceptable_content_types'] = $request->getAcceptableContentTypes();
    }

    public function getQueries()
    {
        return $this->data['statements'];
    }

    public function getQueryCount()
    {
        return $this->data['nb_statements'];
    }

    public function getTime()
    {
        return $this->data['accumulated_duration'];
    }

    public function getName(): string
    {
        return 'pdo_data_collector';
    }

    public function reset()
    {
        $this->data = [];
    }

    public function getMethod()
    {
        return $this->data['method'];
    }

    public function getAcceptableContentTypes()
    {
        return $this->data['acceptable_content_types'];
    }

    /**
     * Collects data from a single TraceablePDO instance
     *
     * @param TraceablePDO $pdo
     * @param TimeDataCollector $timeCollector
     * @return array
     */
    protected function collectPDO(DatabaseConnection $pdo, TimeDataCollector $timeCollector = null)
    {
        $stmts = array();
        foreach ($pdo->getExecutedStatements() as $stmt) {
            $stmts[] = array(
                'sql'            => $stmt->getSql(),
                'row_count'      => $stmt->getRowCount(),
                'stmt_id'        => $stmt->getPreparedId(),
                'prepared_stmt'  => $stmt->getSql(),
                'params'         => (object)$stmt->getParameters(),
                'duration'       => $stmt->getDuration(),
                'duration_str'   => $stmt->getDuration(),
                'memory'         => $stmt->getMemoryUsage(),
                'memory_str'     => $stmt->getMemoryUsage(),
                'end_memory'     => $stmt->getEndMemory(),
                'end_memory_str' => $stmt->getEndMemory(),
                'is_success'     => $stmt->isSuccess(),
                'error_code'     => $stmt->getErrorCode(),
                'error_message'  => $stmt->getErrorMessage(),
                'backtrace'      => $stmt->getBacktrace()
            );
            if ($timeCollector !== null) {
                $timeCollector->addMeasure($stmt->getSql(), $stmt->getStartTime(), $stmt->getEndTime());
            }
        }

        return array(
            'nb_statements'            => count($stmts),
            'nb_failed_statements'     => count($pdo->getFailedExecutedStatements()),
            'accumulated_duration'     => $pdo->getAccumulatedStatementsDuration(),
            'accumulated_duration_str' => $pdo->getAccumulatedStatementsDuration(),
            'memory_usage'             => $pdo->getMemoryUsage(),
            'memory_usage_str'         => $pdo->getPeakMemoryUsage(),
            'peak_memory_usage'        => $pdo->getPeakMemoryUsage(),
            'peak_memory_usage_str'    => $pdo->getPeakMemoryUsage(),
            'statements'               => $stmts
        );
    }
}
