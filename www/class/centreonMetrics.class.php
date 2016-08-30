<?php

require_once _CENTREON_PATH_ . 'www/class/centreonInstance.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonService.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonCommand.class.php';

class CentreonMetrics
{
    /**
     *
     * @var type
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
     *
     * @param type $values
     * @return type
     */
    public function getObjectForSelect2($values = array())
    {
        $metrics = array();
        $filters = '';
        if (count($values) > 0) {
            $filters = 'm.metric_id IN (' . join(', ', $values) . ') AND';
        }

        $queryService = "SELECT SQL_CALC_FOUND_ROWS m.metric_id, CONCAT(h.name,' - ', s.description, ' - ',  m.metric_name) AS fullname "
            ."FROM metrics m, hosts h, services s, index_data i "
            ."WHERE "
            . $filters . " "
            . "i.id = m.index_id AND "
            ."h.host_id = i.host_id "
            ."AND   s.service_id = i.service_id "
            ."ORDER BY fullname COLLATE utf8_general_ci";
        $res = $this->dbo->query($queryService);
        if (PEAR::isError($res)) {
            return $metrics;
        }
        while ($row = $res->fetchRow()) {
            $metrics[] = array(
                'id' => $row['metric_id'],
                'text' => $row['fullname']
            );
        }

        return $metrics;
    }
}
