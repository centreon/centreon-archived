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

/*
 * Basic Settings
 */
$help["timeperiod_name"] = dgettext("help", "Define a short name to identify the time period.");
$help["alias"] = dgettext("help", "Use the alias as a longer name or description to identify the time period.");
$help["weekday"] = dgettext("help", "The weekday directives are comma-delimited lists of time ranges that are \"valid\" times for a particular day of the week. Each time range is in the form of HH:MM-HH:MM, where hours are specified on a 24 hour clock. For example, 00:15-24:00 means 12:15am in the morning for this day until 12:00am midnight (a 23 hour, 45 minute total time range). If you wish to exclude an entire day from the timeperiod, simply do not include it in the timeperiod definition.");
$help["exception"] = dgettext("help", "You can specify several different types of exceptions to the standard rotating weekday schedule. Exceptions can take a number of different forms including single days of a specific or generic month, single weekdays in a month, or single calendar dates. You can also specify a range of days/dates and even specify skip intervals to obtain functionality described by \"every 3 days between these dates\". Weekdays and different types of exceptions all have different levels of precedence, so its important to understand how they can affect each other.");

/*
 * Advanced Settings
 */
$help["include"] = dgettext("help", "This directive is used to specify other timeperiod definitions whose time ranges should be included in this timeperiod.");
$help["exclude"] = dgettext("help", "This directive is used to specify other timeperiod definitions whose time ranges should be excluded from this timeperiod.");

?>

