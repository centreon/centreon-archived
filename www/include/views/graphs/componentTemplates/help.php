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

$help["tip_template_name"] = dgettext("help", "Name of curve template.");
$help["tip_host_service_data_source"] = dgettext("help", "It is possible to define a specific service for the data source.");
$help["tip_data_source_name"] = dgettext("help", "Must be the display name of the metric. Refer to the check plugin for more information.");

/**
 * Display Optional Modifier
 */

$help["tip_stack"] = dgettext("help", "Enables graph stacking.");
$help["tip_order"] = dgettext("help", "Display order.");
$help["tip_invert"] = dgettext("help", "Inverted curve (with negative values).");

/**
 * Colors
 */

$help["tip_thickness"] = dgettext("help", "Curve thickness.");
$help["tip_line_color"] = dgettext("help", "Curve line color.");
$help["tip_area_color"] = dgettext("help", "Curve area color.");
$help["tip_transparency"] = dgettext("help", "Curve transparency.");
$help["tip_filling"] = dgettext("help", "Enables area filling.");

/**
 * Legend
 */

$help["tip_legend_name"] = dgettext("help", "Legend Name.");
$help["tip_display_only_the_legend"] = dgettext("help", "Displays the legend only, thus hiding the curve.");
$help["tip_empty_line_after_this_legend"] = dgettext("help", "Number of line breaks after the legend.");
$help["tip_print_max_value"] = dgettext("help", "Prints maximum value.");
$help["tip_print_min_value"] = dgettext("help", "Prints minimum value.");
$help["tip_print_minmax_int"] = dgettext("help", "Rounds the value.");
$help["tip_print_average"] = dgettext("help", "Prints average value.");
$help["tip_print_last_value"] = dgettext("help", "Prints last Value.");
$help["tip_print_total_value"] = dgettext("help", "Print total value.");
$help["tip_comments"] = dgettext("help", "Comments regarding the curve template.");


