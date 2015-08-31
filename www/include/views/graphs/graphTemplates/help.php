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

$help["tip_template_name"] = dgettext("help", "Name of graph template.");
$help["tip_vertical_label"] = dgettext("help", "Vertical Label (Y-axis).");
$help["tip_width"] = dgettext("help", "Width of grid.");
$help["tip_height"] = dgettext("help", "Height of grid.");
$help["tip_lower_limit"] = dgettext("help", "Lower limit of grid.");
$help["tip_upper_limit"] = dgettext("help", "Upper limit of grid.");
$help["tip_base"] = dgettext("help", "Base value.");

/**
 * Legend
 */

$help["tip_grid_background_color"] = dgettext("help", "Grid background color.");
$help["tip_main_grid_color"] = dgettext("help", "Main grid color.");
$help["tip_secondary_grid_color"] = dgettext("help", "Secondary grid color.");
$help["tip_outline_color"] = dgettext("help", "Outline color.");
$help["tip_background_color"] = dgettext("help", "Background color.");
$help["tip_text_color"] = dgettext("help", "Text color.");
$help["tip_arrow_color"] = dgettext("help", "Arrow color.");
$help["tip_top_color"] = dgettext("help", "Color of the left and top border.");
$help["tip_bottom_color"] = dgettext("help", "Color of the right and bottom border.");
$help["tip_split_components"] = dgettext("help", "Enables component split.");
$help["tip_scale_graph_values"] = dgettext("help", "Enables auto scale of graph.");
$help["tip_default_centreon_graph_template"] = dgettext("help", "Set as default graph template.");
$help["tip_comments"] = dgettext("help", "Comments regarding the graph template.");
