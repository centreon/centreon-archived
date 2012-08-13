#! /usr/bin/perl -w
################################################################################
# Copyright 2005-2011 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# 
# SVN : $URL$
# SVN : $Id$
#
####################################################################################
#
# Script init
#

use strict;
use DBI;

use vars qw($mysql_database_oreon $mysql_database_ods $mysql_host $mysql_user $mysql_passwd $debug $htmlentities);
use vars qw($cmdFile $etc $TIMEOUT $LOG $NAGIOSUSER @OIDTable);

$NAGIOSUSER = 'nagios';

eval "use HTML::Entities";
if ($@) {
    $htmlentities = 0;
} else {
    $htmlentities = 1;
}

###############################
# Init 

$cmdFile = "/var/lib/centreon/centcore.cmd";
$etc = "/etc/centreon";

# Timeout for write in cmd in seconds
$TIMEOUT = 10;

# Define Log File
$LOG = "/var/log/centreon/centTrapHandler.log";

# Configure Debug status
$debug = 1;

###############################
# require config file
require $etc."/conf.pm";

###############################
## Write into Log File
#
sub logit($$) {
    my ($log, $criticity) = @_;

    my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time());
    open (LOG, ">> ".$LOG) || print "can't write $LOG: $!";
    printf LOG "[%04d-%02d-%02d %02d:%02d:%02d] [$criticity] %s\n", $year+1900, $mon+1, $mday, $hour, $min, $sec, $_[0];
    close LOG or warn $!;
}

###############################
## Execute a command Nagios or Centcore
#
sub send_command {
    eval {
	local $SIG{ALRM} = sub { die "TIMEOUT"; };
	alarm($TIMEOUT);
	system @_;
	alarm(0);
    };
    if ($@) {
	if ($@ =~ "TIMEOUT") {
	    logit("ERROR: Send command timeout", "EE");
	    return 0;
	}
    }
    return 1;
}

###############################
## GET HOSTNAME FROM IP ADDRESS
#
sub get_hostinfos($$$) {
    my @host;

    my $sth = $_[0]->prepare("SELECT host_name FROM host WHERE host_address='$_[1]' OR host_address='$_[2]'");
    $sth->execute();
    while (my $temp = $sth->fetchrow_array()) {
	$host[scalar(@host)] = $temp;
    }
    $sth->finish();
    return @host;
}

###############################
## GET host location
#
sub get_hostlocation($$) {
    my $sth = $_[0]->prepare("SELECT localhost FROM host, `ns_host_relation`, nagios_server WHERE host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id AND host.host_name = '".$_[1]."'");
    $sth->execute();
    if ($sth->rows()){
	my $temp = $sth->fetchrow_array();
	$sth->finish();
    	return $temp;
    } else {
    	return 0;
    }
}

##############################
## Connect to MySQL 
#
sub connectDB() {
    my $dbh = DBI->connect("dbi:mysql:".$mysql_database_oreon.";host=".$mysql_host, $mysql_user, $mysql_passwd) or myDie("Echec de la connexion");
    return $dbh;
}

##################################
## GET nagios server id for a host
#
sub get_hostNagiosServerID($$) {
    my $sth = $_[0]->prepare("SELECT id FROM host, `ns_host_relation`, nagios_server WHERE host.host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = nagios_server.id AND (host.host_name = '".$_[1]."' OR host.host_address = '".$_[1]."')");
    $sth->execute();
    if ($sth->rows()){
	my $temp = $sth->fetchrow_array();
	$sth->finish();
    	return $temp;
    } else {
    	return 0;
    }
}

#####################################################################
## GET SERVICES FOR GIVEN HOST (GETTING SERVICES TEMPLATES IN ACCOUNT)
#
sub getServicesIncludeTemplate($$$$) {
    my ($dbh, $sth_st, $host_id, $trap_id) = @_;
    my @service;
    $sth_st->execute();
    
    while (my @temp = $sth_st->fetchrow_array()) {
	my $tr_query = "SELECT `traps_id` FROM `traps_service_relation` WHERE `service_id` = '".$temp[0]."' AND `traps_id` = '".$trap_id."'";
	my $sth_st3 = $dbh->prepare($tr_query);
	$sth_st3->execute();
	my @trap = $sth_st3->fetchrow_array();
	if (defined($trap[0])) {
	    $service[scalar(@service)] = $temp[1];
	} else {
	    if (defined($temp[2])) {
		my $found = 0;
		my $service_template = $temp[2];
		while (!$found) {
		    my $st1_query = "SELECT `service_id`, `service_template_model_stm_id`, `service_description` FROM service s WHERE `service_id` = '".$service_template."'";
		    my $sth_st1 = $dbh->prepare($st1_query);
		    $sth_st1 -> execute();
		    my @st1_result = $sth_st1->fetchrow_array();
		    if (defined($st1_result[0])) {
			my $sth_st2 = $dbh->prepare("SELECT `traps_id` FROM `traps_service_relation` WHERE `service_id` = '".$service_template."' AND `traps_id` = '".$trap_id."'");
			$sth_st2 -> execute();
			my @st2_result = $sth_st2->fetchrow_array();
			if (defined($st2_result[0])) {
			    $found = 1;
			    $service[scalar(@service)] = $temp[1];
			} else {
			    $found = 1;
			    if (defined($st1_result[1]) && $st1_result[1]) {
				$service_template = $st1_result[1];
				$found = 0;
			    }
			}
			$sth_st2->finish;		    
		    }
		    $sth_st1->finish;
		}
	    }
	}
	$sth_st3->finish;
    }
    return (@service);
}



##########################
# GET SERVICE DESCRIPTION
#
sub getServiceInformations($$$)	{

    my $sth = $_[0]->prepare("SELECT `host_id` FROM `host` WHERE `host_name` = '$_[2]'");
    $sth->execute();
    my $host_id = $sth->fetchrow_array();
    if (!defined($host_id)) {
	exit();
    }
    $sth->finish();
    
    $sth = $_[0]->prepare("SELECT `traps_id`, `traps_status`, `traps_submit_result_enable`, `traps_execution_command`, `traps_reschedule_svc_enable`, `traps_execution_command_enable`, `traps_advanced_treatment` FROM `traps` WHERE `traps_oid` = '$_[1]'");
    $sth->execute();
    my ($trap_id, $trap_status, $traps_submit_result_enable, $traps_execution_command, $traps_reschedule_svc_enable, $traps_execution_command_enable, $traps_advanced_treatment) = $sth->fetchrow_array();
    exit if (!defined $trap_id);
    $sth->finish();

    ######################################################
    # getting all "services by host" for given host
    my $st_query = "SELECT s.service_id, service_description, service_template_model_stm_id FROM service s, host_service_relation h";
    $st_query .= " where  s.service_id = h.service_service_id and h.host_host_id='$host_id'";
    my $sth_st = $_[0]->prepare($st_query); 
    my @service = getServicesIncludeTemplate($_[0], $sth_st, $host_id, $trap_id);
    $sth_st->finish;

    ######################################################
    # getting all "services by hostgroup" for given host
    my $query_hostgroup_services = "SELECT s.service_id, service_description, service_template_model_stm_id FROM hostgroup_relation hgr,  service s, host_service_relation hsr";
    $query_hostgroup_services .= " WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id";
    $query_hostgroup_services .= " AND s.service_id = hsr.service_service_id";
    $sth_st = $_[0]->prepare($query_hostgroup_services);
    $sth_st->execute();
    @service = (@service, getServicesIncludeTemplate($_[0], $sth_st, $host_id, $trap_id));
    $sth_st->finish;

    return $trap_id, $trap_status, $traps_submit_result_enable, $traps_execution_command, $traps_reschedule_svc_enable, $traps_execution_command_enable, $traps_advanced_treatment, \@service ;
}

#######################################
## Replace Macro in output template
#
sub replaceMacros($) {
    my ($allargs) = @_;
    my @macros;
    my $x = 0;

    #my @args = split(/\'\s+\'|\'/, $allargs);
    my @args = split(/\#\#C\#\#/, $allargs);
    foreach (@args) {
	my $tmp = $_;
	logit($tmp, "DD");
	my ($oid, $str) = split(':', $tmp);
	$OIDTable[($x+1)] = $oid;
	logit($str, "DD");
	if ($str !~ m/^$/ && $str ne " ") {
	    $macros[($x+1)] = $str;
	    $macros[($x+1)] =~ s/\=/\-/g;
	    $macros[($x+1)] =~ s/\;/\,/g;
	    $macros[($x+1)] =~ s/\t//g;
	    $macros[($x+1)] =~ s/\#\#C\#//g;
	    if ($debug) {
		logit("\$".($x+1)." => |". $macros[($x+1)]."|", "DD");
	    }
	    $x++;
	}
    }
    return @macros;
} 

######################################
## Force a new check for selected services
#
sub forceCheck($$$$) {
    my ($dbh, $this_host, $this_service, $datetime) = @_;

    my $id = get_hostNagiosServerID($dbh, $this_host);
    if (defined($id) && $id != 0) {
	my $submit = "su -l $NAGIOSUSER -c '/bin/echo \"EXTERNALCMD:$id:[$datetime] SCHEDULE_FORCED_SVC_CHECK;$this_host;$this_service;$datetime\" >> $cmdFile'";
	send_command($submit);
	
	logit("FORCE: Reschedule linked service", "II");
	logit("FORCE: Launched command: $submit", "II");
	
	undef($id);
	undef($submit);
    }
}

#######################################
## Submit result via external command
#
sub submitResult($$$$$$$) {
    my ($dbh, $this_host, $this_service, $datetime, $status, $arguments_line, $cmdFile) = @_;

    # No matching rules
    my $id = get_hostNagiosServerID($dbh, $this_host);
    if (defined($id) && $id != 0) {
	my $submit = "su -l $NAGIOSUSER -c '/bin/echo \"EXTERNALCMD:$id:[$datetime] PROCESS_SERVICE_CHECK_RESULT;$this_host;$this_service;$status;$arguments_line\" >> $cmdFile'";
	send_command($submit);
	
	logit("SUBMIT: Force service status via passive check update", "II");
	logit("SUBMIT: Launched command: $submit", "II");
	
	undef($id);
	undef($submit);
    }
}


sub replaceOID($$) {
    my ($str, $ref_macros) = @_;

    my @macros = @{$ref_macros};

    ##########################
    # REPLACE OID ARGS
    my $x = 1;
    my $oid = "";
    foreach (@macros) {
	if (defined($macros[$x])) {
	    $oid = $OIDTable[$x];
	    if ($debug) {
		logit("REPLACE OID: $str => /\@\{".$oid."\}/".$macros[$x]."/", "DD");
	    }
	    $str =~ s/\@\{$oid\}/$macros[$x]/g;
	    $x++;
	}
    }
    return $str;
}

#######################################
## Check Advanced Matching Rules
#
sub checkMatchingRules($$$$$$$$$) {
    my ($dbh, $trap_id, $this_host, $ip, $hostname, $arguments_line, $datetime, $status, $ref_macros) = @_;

    my @macros = @{$ref_macros};
    
    # Check matching options 
    my $sth = $dbh->prepare("SELECT tmo_regexp, tmo_status, tmo_string FROM traps_matching_properties WHERE trap_id = '".$trap_id."' ORDER BY tmo_order");
    $sth->execute();
    while (my ($regexp, $tmoStatus, $tmoString) = $sth->fetchrow_array()) {
	if ($debug) {
	    logit("[$tmoString][$regexp] => $tmoStatus", "DD");
	}
	my @temp = split(//, $regexp);
	my $i = 0;
	my $len = length($regexp);
	$regexp = "";
	foreach (@temp) {
	    if ($i eq 0 && $_ =~ "/") {
		$regexp = $regexp . "";
	    } elsif ($i eq ($len - 1) && $_ =~ "/") { 
		$regexp = $regexp . "";
	    } else {
		$regexp = $regexp . $_;
	    }
	    $i++;
	}

	##########################
	# REPLACE ARGS
	my $x = 1;
	foreach (@macros) {
	    if (defined($macros[$x])) {
		if ($debug) {
		    logit("REPLACE VAL: $tmoString => /\$".$x."/".$macros[$x]."/", "DD");
		}
		$tmoString =~ s/\$$x/$macros[$x]/g;
		$x++;
	    }
	}

	$tmoString = replaceOID($tmoString, \@macros);

	##########################
	# REPLACE MACROS
	if ($htmlentities == 1) {
	    $tmoString = decode_entities($tmoString);
	} else {
	    $tmoString =~ s/\&quot\;/\"/g;
	    $tmoString =~ s/\&#039\;\&#039\;/"/g;
	}
	$tmoString =~ s/\@HOSTNAME\@/$this_host/g;
	$tmoString =~ s/\@HOSTADDRESS\@/$ip/g;
	$tmoString =~ s/\@HOSTADDRESS2\@/$hostname/g;
	$tmoString =~ s/\@TRAPOUTPUT\@/$arguments_line/g;
	$tmoString =~ s/\@OUTPUT\@/$arguments_line/g;
	$tmoString =~ s/\@TIME\@/$datetime/g;

	# Integrate OID Matching		    
	if (defined($tmoString) && $tmoString =~ m/$regexp/g) {
	    $status = $tmoStatus;
	    logit("Regexp: String:$tmoString => REGEXP:$regexp", "II");
	    logit("Status: $status ($tmoStatus)", "II");
	    last;
	}
    }
    $sth->finish();
    return $status;
}

sub executeCommand($$$$$$$$) {
    my ($traps_execution_command, $this_host, $ip, $hostname, $arguments_line, $datetime, $status, $ref_macros) = @_;
    
    my @macros = @{$ref_macros};

    my $x = 1;
    foreach (@macros) {
	if (defined($macros[$x])) {
	    $traps_execution_command =~ s/\$$x/$macros[$x]/g;
	    $x++;
	}
    }

    $traps_execution_command = replaceOID($traps_execution_command, \@macros);
    
    ##########################
    # REPLACE MACROS
    if ($htmlentities == 1) {
	$traps_execution_command = decode_entities($traps_execution_command);
    } else {
	$traps_execution_command =~ s/\&quot\;/\"/g;
	$traps_execution_command =~ s/\&#039\;\&#039\;/"/g;
	$traps_execution_command =~ s/\&#039\;/'/g;
    }
    $traps_execution_command =~ s/\@HOSTNAME\@/$this_host/g;
    $traps_execution_command =~ s/\@HOSTADDRESS\@/$_[1]/g;
    $traps_execution_command =~ s/\@HOSTADDRESS2\@/$_[2]/g;
    $traps_execution_command =~ s/\@TRAPOUTPUT\@/$arguments_line/g;
    $traps_execution_command =~ s/\@OUTPUT\@/$arguments_line/g;
    $traps_execution_command =~ s/\@STATUS\@/$status/g;
    $traps_execution_command =~ s/\@TIME\@/$datetime/g;

    ##########################
    # SEND COMMAND
    if ($traps_execution_command) {
	logit("EXEC: Launch specific command", "II");
	logit("EXEC: Launched command: $traps_execution_command", "II");

	my $output = `$traps_execution_command`;
	if ($?) {
	    logit("EXEC: Execution error: $!", "EE");
	}
	if ($output) {
	    logit("EXEC: Output : $output", "II");
	}
	undef($output);
    }
}

#######################################
## GET HOSTNAME AND SERVICE DESCRIPTION
#
sub getTrapsInfos($$$$$) {
    my $ip = shift;
    my $hostname = shift;
    my $oid = shift;
    my $arguments_line = shift;
    my $allargs = shift;
    
    my $status;
    my @macros;

    # Connect to MySQL Database
    my $dbh = connectDB();

    my @host = get_hostinfos($dbh, $ip, $hostname);
    foreach (@host) {
	my $this_host = $_;
	my ($trap_id, $status, $traps_submit_result_enable, $traps_execution_command, $traps_reschedule_svc_enable, $traps_execution_command_enable, $traps_advanced_treatment, $ref_servicename) = getServiceInformations($dbh, $oid, $_);
	my @servicename = @{$ref_servicename};
	
	##########################
	# REPLACE ARGS	
	@macros = replaceMacros($allargs);

	foreach (@servicename) {
	    my $this_service = $_;

	    if ($debug) {
		logit("Trap found on service \'$this_service\' for host \'$this_host\'.", "DD");
	    }

	    my $datetime = `date +%s`;
	    chomp($datetime);

            ##########################
	    # REPLACE OID ARGS
	    my $x = 1;
	    my $oid = "";
	    foreach (@macros) {
		if (defined($macros[$x])) {
		    $oid = $OIDTable[$x];
		    if ($debug) {
			logit("REPLACE OID: $traps_execution_command => /\@\{".$oid."\}/".$macros[$x]."/", "DD");
		    }
		    $arguments_line =~ s/\@\{$oid\}/$macros[$x]/g;
		    $x++;
		}
	    }

	    ######################################################################
	    # Advanced matching rules
	    if (defined($traps_advanced_treatment) && $traps_advanced_treatment eq 1) {
		$status = checkMatchingRules($dbh, $trap_id, $this_host, $ip, $hostname, $arguments_line, $datetime, $status, \@macros);
	    }

	    #####################################################################
	    # Submit value to passive service
	    if (defined($traps_submit_result_enable) && $traps_submit_result_enable eq 1) { 
		submitResult($dbh, $this_host, $this_service, $datetime, $status, $arguments_line, $cmdFile);
	    }

	    ######################################################################
	    # Force service execution with external command
	    if (defined($traps_reschedule_svc_enable) && $traps_reschedule_svc_enable eq 1) {
		forceCheck($dbh, $this_host, $this_service, $datetime);
	    }
	    
	    ######################################################################
	    # Execute special command
	    if (defined($traps_execution_command_enable) && $traps_execution_command_enable) {
		executeCommand($traps_execution_command, $this_host, $ip, $hostname, $arguments_line, $datetime, $status, \@macros);
	    }
	}
    }
    $dbh->disconnect();
    exit;
}

####################################
## GenerateError
#
sub myDie($) {
    logit($_[0], "EE");
    exit(1);
}

#########################################################
# PARSE TRAP INFORMATIONS
#

if ($debug) {
    logit("centTrapHandler launched....", "DD");
    logit("PID: $$", "DD");
}

if (scalar(@ARGV)) {
    my ($ip, $hostname, $oid, $arguments, $allArgs) = @ARGV;
    if ($debug) {
	logit("Param: HOSTNAME -> $hostname", "DD");
	logit("Param: IP -> $ip", "DD");
	logit("Param: OID -> $oid", "DD");
	logit("Param: Output -> $arguments", "DD");
	logit("Param: ARGS -> $allArgs", "DD");
    }
    getTrapsInfos($ip, $hostname, $oid, $arguments, $allArgs);
} else {
    print "Error: No parameters received.";
    logit("Error: No parameters received.", "EE");
}

__END__
