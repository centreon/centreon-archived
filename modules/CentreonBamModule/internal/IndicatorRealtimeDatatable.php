<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 */

namespace CentreonBam\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Datatable;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\Datetime;

/**
 * Description of IndicatorDatatable
 *
 * @author kevin
 */
class IndicatorRealtimeDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';

    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonBam\Models\IndicatorRealtime';

    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'kpi_id', 'name' => 'object_name');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('kpi_id', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    public static $columns = array(
        array (
            'title' => 'Id',
            'name' => 'kpi_id',
            'data' => 'kpi_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Type',
            'name' => 'kpi_type',
            'data' => 'kpi_type',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => 10,
            'class' => 'cell_center',
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '0' => "<i class='fa fa-rss'></i>",
                    '1' => "<i class='fa fa-superscript'></i>",
                    '2' => "<i class='fa fa-suitcase'></i>",
                    '3' => "<i class='fa fa-comment-o'></i>",
                ),
                'extra' => array (
                    //'groupable' => true,
                ),
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Service' => '0',
                    'Metaservice' => '1',
                    'Business Activity' => '2',
                    'Boolean' => '3'
                )
            ),
        ),
        array (
            'title' => 'Indicator',
            'name' => 'object',
            'data' => 'object',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
        ),
        array (
            'title' => 'Impacted BA',
            'name' => 'impacted_ba',
            'data' => 'impacted_ba',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
        ),
        array (
            'title' => 'Current impact',
            'name' => 'last_impact',
            'data' => 'last_impact',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Status',
            'name' => 'current_status',
            'data' => 'current_status',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-success">OK</span>',
                    '1' => '<span class="label label-warning">Warning</span>',
                    '2' => '<span class="label label-danger">Critical</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'OK' => '0',
                    'Warning' => '1',
                    'Critical' => '2'
                )
            ),
        ),
        array (
            'title' => 'Duration',
            'name' => '(unix_timestamp(NOW())-last_state_change) AS duration',
            'data' => 'duration',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '10%',
            'className' => 'cell_center'
        ),
    );

    /**
    *
    * @param type $resultSet
    */
    public static function addAdditionnalDatas(&$resultSet)
    {
        // Get datatabases connections
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        // Add object column
        // Can be service, metaservice or BA
        $sqlKpiService = 'SELECT k.kpi_id, h.host_name, s.service_id, s.service_description
            FROM cfg_hosts h, cfg_services s, cfg_hosts_services_relations hs, cfg_bam_kpi k 
            WHERE s.service_id=k.service_id and hs.host_host_id=h.host_id and hs.service_service_id=s.service_id';
        $stmtKpiService = $dbconn->query($sqlKpiService);
        $resultKpiService = $stmtKpiService->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiMetaservice = 'SELECT k.kpi_id,ms.meta_id
            FROM cfg_meta_services ms,cfg_bam_kpi k
            WHERE ms.meta_id=k.meta_id';
        $stmtKpiMetaservice = $dbconn->query($sqlKpiMetaservice);
        $resultKpiMetaservice = $stmtKpiMetaservice->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBa = 'SELECT k.kpi_id,b.ba_id,b.name
            FROM cfg_bam b,cfg_bam_kpi k
            WHERE b.ba_id=k.id_indicator_ba';
        $stmtKpiBa = $dbconn->query($sqlKpiBa);
        $resultKpiBa = $stmtKpiBa->fetchAll(\PDO::FETCH_ASSOC);

        $sqlKpiBoolean = 'SELECT k.kpi_id,b.boolean_id,b.name
            FROM cfg_bam_boolean b,cfg_bam_kpi k
            WHERE b.boolean_id=k.boolean_id';
        $stmtKpiBoolean = $dbconn->query($sqlKpiBoolean);
        $resultKpiBoolean = $stmtKpiBoolean->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($resultSet as &$kpi) {
            if ($kpi['kpi_type'] == 0) {
                foreach ($resultKpiService as $kpiObject) {
                    if ($kpiObject['kpi_id'] === $kpi['kpi_id']) {
                        $kpi['object_name'] = $kpiObject['host_name'].' '.$kpiObject['service_description'];
                        $kpi['object'] = '<a href="/centreon-bam/indicator/' . $kpiObject['kpi_id'] . '">' . $kpiObject['host_name'].' '.$kpiObject['service_description'] . '</a>';
                    }
                }
            } else if ($kpi['kpi_type'] == 1) {
                foreach ($resultKpiMetaservice as $kpiObject) {
                    if ($kpiObject['kpi_id'] === $kpi['kpi_id']) {
                        $kpi['object_name'] = 'metaservice';
                        $kpi['object'] = 'metaservice';
                    }
                }
            } else if ($kpi['kpi_type'] == 2) {
                foreach ($resultKpiBa as $kpiObject) {
                    if ($kpiObject['kpi_id'] === $kpi['kpi_id']) {
                        $kpi['object_name'] = $kpiObject['name'];
                        $kpi['object'] = '<a href="/centreon-bam/indicator/' . $kpiObject['kpi_id'] . '">' . $kpiObject['name'] . '</a>';
                    }
                }
            } else if ($kpi['kpi_type'] == 3) {
                foreach ($resultKpiBoolean as $kpiObject) {
                    if ($kpiObject['kpi_id'] === $kpi['kpi_id']) {
                        $kpi['object_name'] = $kpiObject['name'];
                        $kpi['object'] = '<a href="/centreon-bam/indicator/' . $kpiObject['kpi_id'] . '">' . $kpiObject['name'] . '</a>';
                    }
                }
            }
        }

        // Add impact column
        $sqlKpiImpact = 'SELECT kpi_id, drop_warning, drop_critical, drop_unknown
                         FROM cfg_bam_kpi';
        $stmtKpiImpact = $dbconn->query($sqlKpiImpact);
        $resultKpiImpact = $stmtKpiImpact->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($resultSet as &$kpi) {
            foreach ($resultKpiImpact as $kpiImpact) {
                if ($kpi['kpi_type'] == 3) {
                    if ($kpiImpact['kpi_id'] === $kpi['kpi_id']) {
                        $kpi['impact'] = $kpiImpact['drop_critical'] . '%';
                    }
                } else {
                    if ($kpiImpact['kpi_id'] === $kpi['kpi_id']) {
                        $kpi['impact'] = $kpiImpact['drop_warning'] . '% / ' . $kpiImpact['drop_critical'] . '% / ' . $kpiImpact['drop_unknown']. '%';
                    }
                }
            }
        }

        // Add impacted BA column
        $sqlKpiImpactedBa = 'SELECT k.kpi_id, k.id_ba, b.name'
            . ' FROM cfg_bam_kpi k, cfg_bam b'
            . ' WHERE k.id_ba=b.ba_id';
        $stmtKpiImpactedBa = $dbconn->query($sqlKpiImpactedBa);
        $resultKpiImpactedBa = $stmtKpiImpactedBa->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($resultSet as &$kpi) {
            $kpi['impacted_ba'] = "";
            foreach ($resultKpiImpactedBa as $kpiImpactedBa) {
                if ($kpiImpactedBa['kpi_id'] === $kpi['kpi_id']) {
                    $kpi['impacted_ba'] = '<a href="/centreon-bam/businessactivity/' . $kpiImpactedBa['id_ba'] . '">' . $kpiImpactedBa['name'] . '</a>';
                }
            }
        }
    }

    /**
     *
     * @param array $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        foreach ($resultSet as &$myIndicatorSet) {
            // Add percent to impact
            $myIndicatorSet['last_impact'] = $myIndicatorSet['last_impact'] . '%';

            // Set human readable duration
            $myIndicatorSet['duration'] = Datetime::humanReadable(
                $myIndicatorSet['duration'],
                Datetime::PRECISION_FORMAT,
                2
            );
        }
    }
}
