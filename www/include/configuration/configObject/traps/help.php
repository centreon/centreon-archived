<?php

$help = array();

$help["trapname"] = dgettext("help", "Enter the trap name as specified in the MIB file and send by the SNMP master agent.");
$help["oid"] = dgettext("help", "Enter the full numeric object identifier (OID) starting with .1.3.6 (.iso.org.dod).");
$help["vendor"] = dgettext("help", "Choose a vendor from the list. The vendor must have been created beforehand.");
$help["submit_result_enabled"] = dgettext("help", "Switch the submission of trap results to monitoring engine on or off.");
$help["trap_args"] = dgettext("help", "Enter the status message to be submitted to monitoring engine. The original trap message will be placed into the entered string at the position of the variable \$*.");
$help["trap_status"] = dgettext("help", "Choose the service state to be submitted to monitoring engine together with the status message. This simple mode can be used if each trap can be mapped to exactly 1 monitoring engine status.");
$help["trap_advanced"] = dgettext("help", "Enable advanced matching mode for cases where a trap relates to multiple monitoring engine states and the trap message has to be parsed.");
$help["trap_adv_args"] = dgettext("help", "Define one or multiple regular expressions to match against the trap message and map it to the related monitoring engine service state. Use <a href='http://perldoc.perl.org/perlre.html'>perlre</a> for the format and place the expression between two slashes.");
$help["reschedule_enabled"] = dgettext("help", "Choose whether or not the associated service should be actively rechecked after submission of this trap.");
$help["command_enabled"] = dgettext("help", "Choose whether or not a special command should be run by snmptrapd when this trap was received.");
$help["command_args"] = dgettext("help", "Define the command to execute by snmptrapd's trap handler. The command must be located in the PATH of the snmptrapd user.");
$help["comments"] = dgettext("help", "Describe the situation in which this trap will be send. Additionally the format and the parameters of the trap can be described.");

?>

