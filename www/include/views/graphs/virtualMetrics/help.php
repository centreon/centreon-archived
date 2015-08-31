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

$help = array();

/**
 * General Information
 */

$help["tip_metric_name"] = dgettext("help", "Name of virtual metric.");
$help["tip_host_service_data_source"] = dgettext("help", "Host / Service Data Source.");
$help["tip_def_type"] = dgettext("help", "DEF Type.");

/**
 * RPN Function
 */

$help["tip_rpn_function"] = dgettext("help", "RPN (Reverse Polish Notation) Function *.");
$help["tip_metric_unit"] = dgettext("help", "Metric unit.");
$help["tip_warning_threshold"] = dgettext("help", "Warning threshold.");
$help["tip_critical_threshold"] = dgettext("help", "Critical threshold.");

/**
 * Options
 */

$help["tip_hidden_graph_and_legend"] = dgettext("help", "Hides curve and legend.");
$help["tip_comments"] = dgettext("help", "Comments regarding the virtual metric.");