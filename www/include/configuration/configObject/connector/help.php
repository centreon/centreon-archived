<?php
$help = array();

$help["connector_name"] = dgettext("help", "Name which will be used for identifying the connector.");
$help["connector_description"] = dgettext("help", "A short description of the connector.");
$help["command_line"] = dgettext("help", "This will be executed by Centreon Engine, note that this line contains macros that will be replaced before execution. e.g: $USER3$/centreon_connector_perl");
$help["connector_status"] = dgettext("help", "Whether or not the connector is enabled.");
?>