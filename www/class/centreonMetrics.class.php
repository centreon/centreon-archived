<?php

require_once _CENTREON_PATH_ . 'www/class/centreonInstance.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonService.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonCommand.class.php';

class CentreonMetrics
{
    /**
     *
     * @var \CentreonDB
     */
    protected $db;

    /**
     *
     * @var type
     */
    protected $instanceObj;

    /**
     *
     * @var type
     */
    protected $serviceObj;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->dbo = new CentreonDB('centstorage');
        $this->instanceObj = new CentreonInstance($db);
        $this->serviceObj = new CentreonService($db);
    }

    /**
     * Get metrics information from ids to populat select2
     *
     * @param array $values list of metric ids
     * @return array
     */
    public function getObjectForSelect2($values = [])
    {
        $metrics = [];
        $listValues = '';
        $queryValues = [];
        if (!empty($values)) {
            foreach ($values as $v) {
                $multiValues = explode(',', $v);
                foreach ($multiValues as $item) {
                    $listValues .= ':metric' . $item . ',';
                    $queryValues['metric' . $item] = (int)$item;
                }
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues = '""';
        }


        $queryService = "SELECT SQL_CALC_FOUND_ROWS m.metric_id, CONCAT(i.host_name,' - ', i.service_description,"
            . "' - ', m.metric_name) AS fullname "
            . "FROM metrics m, index_data i "
            . "WHERE m.metric_id IN (" . $listValues . ") "
            . "AND i.id = m.index_id "
            . "ORDER BY fullname COLLATE utf8_general_ci";

        $stmt = $this->dbo->prepare($queryService);
        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $metrics[] = [
                'id' => $row['metric_id'],
                'text' => $row['fullname']
            ];
        }

        return $metrics;
    }
}
