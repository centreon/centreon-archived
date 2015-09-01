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
 * 
 */

$tpl = $this->getTemplate();

/* Load css */
$tpl->addCss('dataTables.tableTools.min.css')
    ->addCss('dataTables.colVis.min.css')
    ->addCss('dataTables.colReorder.min.css')
    ->addCss('dataTables.bootstrap.css')
    ->addCss('centreon.qtip.css')
    ->addCss('daterangepicker-bs3.css');

/* Load js */
$tpl->addJs('jquery.min.js')
    ->addJs('jquery.dataTables.min.js')
    ->addJs('dataTables.tableTools.min.js')
    ->addJs('dataTables.colVis.min.js')
    ->addJs('dataTables.colReorder.min.js')
    ->addJs('bootstrap-dataTables-paging.js')
    ->addJs('jquery.dataTables.columnFilter.js')
    ->addJs('dataTables.bootstrap.js')
    ->addJs('jquery.select2/select2.min.js')
    ->addJs('jquery.validation/jquery.validate.min.js')
    ->addJs('jquery.validation/additional-methods.min.js')
    ->addJs('jquery.qtip.min.js')
    ->addJs('moment-with-langs.min.js')
    ->addJs('daterangepicker.js');

/* Datatable */
$tpl->assign('moduleName', 'CentreonRealtime');
$tpl->assign('objectName', 'Service');
$tpl->assign('objectListUrl', '/centreon-realtime/service/list');
$tpl->assign('datatableObject', '\CentreonRealtime\Internal\ServiceDatatable');
$tpl->display('file:[ServiceMonitoringWidget]console.tpl');
