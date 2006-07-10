#!/usr/bin/perl -w
#
# $Id: trap_link.pl,v 1.0 2006/06/30 12:30:00 Nicolas Cordier for Merethis $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Nicolas Cordier for Merethis
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# OREON makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the OREON web site.
# In no event will OREON be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if OREON has
# been previously advised of the possibility of such damages.

#
## to use the common.pm including usefull functions
#
use DBI;
use lib '/usr/lib/nagios/plugins/traps';
use trap_common;

#
## get informations about the trap for the generation of the result sent to nagios
#
(my $hostname, my $servicename, my @args) = getTrapsInfos(@ARGV);

#
## set state for the resul (OK (0)/WARNING (1)/CRITICAL (2)/UNKNOWN (3))
#
my $state = ($args[0] eq "up" ? 0 : 2);

#
## set the text output of the service check
#
my $plugin = "";
$plugin .= $args[0];

# parse trap to obtain more information
my $i = 0;
while ($args[$i])
{
    submit_res("$args[$i]\n");
    if ("$args[$i]" eq "IF-MIB::ifDescr")
    {
	$plugin .= " on $args[$i + 2]";
    }
    $i++;
}

#
## set the result
#
$res = "[".time."] PROCESS_SERVICE_CHECK_RESULT;".$hostname.";".$servicename.";".$state.";".$plugin."\n";

#
## send the result
#
submit_res($res);
