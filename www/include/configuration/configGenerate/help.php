<?php
$help = array();
$help['host']     = dgettext("help", "Select the poller you would like to interact with.");
$help['gen']      = dgettext("help", "Generates configuration files and stores them in centreon/filesGeneration directory.");
$help['debug']    = dgettext("help", "Runs the scheduler debug mode.");
$help['move']     = dgettext("help", "Copies the generated files into the scheduler's configuration folder.");
$help['restart']  = dgettext("help", "Restart the scheduler: Restart, Reload or External Command.");
$help['postcmd']  = dgettext("help", "Run the commands that are defined in the poller configuration page (Configuration > Centreon > Poller > Post-Restart command).");
