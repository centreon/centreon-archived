# Program global variables

$varLibCentreon = "/home/msugumaran/centreon/var/";
$lock_file = $varLibCentreon."/archive-monitoring-incidents.lock";



%options = ();

%serviceStates = ("OK" => 0, "WARNING" => 1, "CRITICAL" => 2, "UNKNOWN" => 3);
%hostStates = ("UP" => 0, "DOWN" => 1, "UNREACHABLE" => 2, "PENDING" => 4);

%servicStateIds = (0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");
%hostStateIds = (0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE", 4 => "PENDING");

1;
