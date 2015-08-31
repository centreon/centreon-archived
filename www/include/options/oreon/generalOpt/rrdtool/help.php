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
 * RRDTool Configuration
 */

$help['tip_directory+rrdtool_binary'] = dgettext('help', 'RRDTOOL binary complete path.');
$help['tip_rrdtool_version'] = dgettext('help', 'RRDTool version.');

/**
 * Title Properties
 */

$help['tip_title_font'] = dgettext('help', 'Font style of titles.');
$help['tip_title_font_size'] = dgettext('help', 'Font size of titles.');

/**
 * Unit Properties
 */
$help['tip_unit_font'] = dgettext('help', 'Font style of units.');
$help['tip_unit_font_size'] = dgettext('help', 'Font size of units.');

/**
 * Axis Properties
 */

$help['tip_axis_font'] = dgettext('help', 'Font style of X-axis and Y-axis.');
$help['tip_axis_font_size'] = dgettext('help', 'Font size of X-axis and Y-axis.');

/**
 * Legend Properties
 */

$help['tip_legend_font'] = dgettext('help', 'Font style of captions.');
$help['tip_legend_font_size'] = dgettext('help', 'Font size of captions.');

/**
 * Watermark Properties
 */

$help['tip_watermark_font'] = dgettext('help', 'Font style of watermarks.');
$help['tip_watermark_font_size'] = dgettext('help', 'Font size of watermarks.');

/**
 * RRDCached Properties
 */
$help['tip_rrdcached_enable'] = dgettext('help', 'Enable the rrdcached for Centreon. This option is valid only with Centreon Broker');
$help['tip_rrdcached_port'] = dgettext('help', 'Port for communicating with rrdcached');
$help['tip_rrdcached_unix_path'] = dgettext('help', 'The absolute path to unix socket for communicating with rrdcached');