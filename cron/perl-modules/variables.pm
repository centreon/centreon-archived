# Program global variables

$varLibCentreon = "/home/msugumaran/centreon/var/";
$lock_file = $varLibCentreon."/archive-monitoring-incidents.lock";



%options = ();
%states = ("OK" => 0, "WARNING" => 1, "CRITICAL" => 2, "UNKNOWN" => 3);
%state_ids = (0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN");

1;
