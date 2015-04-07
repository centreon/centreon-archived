<?php

/*
 * Copyright 2005-2014 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonBam\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Datatable;
use Centreon\Internal\Di;

/**
 * Description of IndicatorDatatable
 *
 * @author lionel
 */
class IndicatorDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';

    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonBam\Models\Indicator';

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
        'stateSave' => true,
        'paging' => true,
    );
    
    public static $columns = array(
        array (
            'title' => 'Id',
            'name' => 'kpi_id',
            'data' => 'kpi_id',
            'orderable' => true,
            'searchable' => true,
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
            )
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
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
        ),
        array (
            'title' => 'Impact (Warning/Critical/Unknown)',
            'name' => 'impact',
            'data' => 'impact',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'source' => 'other',
        ),
        array (
            'title' => 'Status',
            'name' => 'activate',
            'data' => 'activate',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
            'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-danger">Disabled</span>',
                    '1' => '<span class="label label-success">Enabled</span>',
                )
            ),
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
}
