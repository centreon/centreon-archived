<?php
$help = array();

/*
 * Basic Settings
 */
$help["timeperiod_name"] = dcgettext("help", "Define a short name to identify the time period.");
$help["alias"] = dcgettext("help", "Use the alias as a longer name or description to identify the time period.");
$help["weekday"] = dcgettext("help", "The weekday directives are comma-delimited lists of time ranges that are \"valid\" times for a particular day of the week. Each time range is in the form of HH:MM-HH:MM, where hours are specified on a 24 hour clock. For example, 00:15-24:00 means 12:15am in the morning for this day until 12:00am midnight (a 23 hour, 45 minute total time range). If you wish to exclude an entire day from the timeperiod, simply do not include it in the timeperiod definition.");
$help["exception"] = dcgettext("help", "You can specify several different types of exceptions to the standard rotating weekday schedule. Exceptions can take a number of different forms including single days of a specific or generic month, single weekdays in a month, or single calendar dates. You can also specify a range of days/dates and even specify skip intervals to obtain functionality described by \"every 3 days between these dates\". Weekdays and different types of exceptions all have different levels of precedence, so its important to understand how they can affect each other.");

/*
 * Advanced Settings
 */
$help["include"] = dcgettext("help", "This directive is used to specify other timeperiod definitions whose time ranges should be included in this timeperiod.");
$help["exclude"] = dcgettext("help", "This directive is used to specify other timeperiod definitions whose time ranges should be excluded from this timeperiod.");

?>

