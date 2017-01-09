<?php
$help = array();
$help["name"] = dgettext("help", "Define a short name for this category. It will be displayed with this name in the ACL configuration.");
$help["description"] = dgettext("help", "Use this field for a longer description of this category.");
$help["service_template"] = dgettext("help", "Select the service templates this category should be linked to. Every service based on the selected templates will be automatically linked with this category.");
$help["sc_type"] = dgettext("help", "Whether this category is a severity. Severities appear on the monitoring consoles.");
$help["sc_severity_level"] = dgettext("help", "Severity level, must be a number. The items displayed will be sorted in ascending order. Thus the lowest severity is considered than the highest priority.");
$help["sc_severity_icon"] = dgettext("help", "Icon for this severity.");
$help["sc_activate"] = dgettext("help", "Whether or not this category is enabled.");
