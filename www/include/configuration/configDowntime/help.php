<?php
$help = array();
$help["mc_update"] = dgettext("help", "Choose the update mode for the below field: incremental adds the selected values, replacement overwrites the original values.");

/*
 * Host Configuration
 */
$help["downtime_name"] = dgettext("help", "The name of the recurrent downtime rule.");
$help["downtime_description"] = dgettext("help", "Description of the downtime");
$help["downtime_activate"] = dgettext("help", "Option to enable or disable this downtime");
$help["downtime_period"] = dgettext("help", "This field give the possibility to configure the frequency of this downtime.");

$help["host_relation"] = dgettext("help", "This field give you the possibility to select all hosts implied by this downtime");
$help["hostgroup_relation"] = dgettext("help", "This field give you the possibility to select all hostgroups and all hosts contained into the selected hostgroups implied by this downtime");
$help["svc_relation"] = dgettext("help", "This field give you the possibility to select all services implied by this downtime");
$help["svcgroup_relation"] = dgettext("help", "This field give you the possibility to select all servicegroups and all services contained into the servicegroups implied by this downtime");
