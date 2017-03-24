<?php

if (isset($pearDB)) {
    $query = 'SELECT cb.config_id, COUNT(cbi.config_group) AS nb '
        . 'FROM cfg_centreonbroker cb '
        . 'LEFT JOIN cfg_centreonbroker_info cbi '
        . 'ON cbi.config_id = cb.config_id '
        . 'AND cbi.config_group = "input" '
        . 'GROUP BY cb.config_id ';
    $res = $pearDB->query($query);
    while ($row = $res->fetchRow()) {
        $daemon = 0;
        if ($row['nb'] > 0) {
            $daemon = 1;
        }
        $query = 'UPDATE cfg_centreonbroker '
            . 'SET daemon = ' . $daemon . ' '
            . 'WHERE config_id = ' . $row['config_id'];
        $pearDB->query($query);
    }
}
