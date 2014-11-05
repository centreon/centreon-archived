################################################################################
# Copyright 2005-2013 MERETHIS
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
#
####################################################################################

package centreon::trapd::lib;

use warnings;
use strict;
use POSIX qw(strftime);
use File::stat;

sub init_modules {
    # logger => obj
    # htmlentities => (ref)
    # config => hash
    my %args = @_;

    ####
    # SNMP Module
    ####
    if ($args{config}->{net_snmp_perl_enable} == 1) {
        eval 'require SNMP;';
        if ($@) {
            $args{logger}->writeLogError($@);
            $args{logger}->writeLogError("Could not load the Perl module SNMP!  If net_snmp_perl_enable is");
            $args{logger}->writeLogError("enabled then the SNMP module is required");
            $args{logger}->writeLogError("for system requirements.  Note:  uses the Net-SNMP package's");
            $args{logger}->writeLogError("SNMP module, NOT the CPAN Net::SNMP module!");
            die("Quit");
        }
        if (defined ($args{config}->{mibs_environment}) && $args{config}->{mibs_environment} ne '') {
            $ENV{'MIBS'} = $args{config}->{mibs_environment};
        }
        &SNMP::initMib();

        $args{logger}->writeLogInfo("********** Net-SNMP version $SNMP::VERSION Perl module enabled **********");
        if (defined ($args{config}->{mibs_environment})) {
            $args{logger}->writeLogDebug("********** MIBS: " . $args{config}->{mibs_environment} . " **********");
        }
    }
    
    if ($args{config}->{duplicate_trap_window} > 0) {
        eval 'require Digest::MD5;';
        if ($@) {
            $args{logger}->writeLogError($@);
            $args{logger}->writeLogError("Could not load the Perl module Digest::MD5!  If centrapmanager_duplicate_trap_window");
            $args{logger}->writeLogError("is set then the Digest::MD5 module is required");
            $args{logger}->writeLogError("for system requirements.");
            die("Quit");
        }
    }
    
    eval "require HTML::Entities";
    if ($@) {
        ${$args{htmlentities}} = 0;
    } else {
        ${$args{htmlentities}} = 1;
    }
}

sub manage_params_conf {
    my ($date_format, $time_format) = @_;

    if (!defined($date_format) || $date_format eq "") {
        $date_format = "%a %b %e %Y";
    }
    if (!defined($time_format) || $time_format eq "") {
        $time_format = "%H:%M:%S";
    }
    
    return ($date_format, $time_format);
}

##############
# DB Request
##############

# We get All datas for a TRAP
sub get_oids {
    my ($cdb, $oid) = @_;
    my $ref_result;
    
    my ($dstatus, $sth) = $cdb->query("SELECT name, traps_log, traps_execution_command, traps_reschedule_svc_enable, traps_id, traps_args,
                                        traps_oid, traps_name, traps_advanced_treatment, traps_advanced_treatment_default, traps_execution_command_enable, traps_submit_result_enable, traps_status,
                                        traps_timeout, traps_exec_interval, traps_exec_interval_type,
                                        traps_routing_mode, traps_routing_value, traps_routing_filter_services,
                                        traps_exec_method, traps_downtime, traps_output_transform,
                                        service_categories.level, service_categories.sc_name, service_categories.sc_id
                                        FROM traps
                                        LEFT JOIN traps_vendor ON (traps_vendor.id = traps.manufacturer_id)
                                        LEFT JOIN service_categories ON (service_categories.sc_id = traps.severity_id)
                                        WHERE traps_oid = " . $cdb->quote($oid));
    return -1 if ($dstatus == -1);
    $ref_result = $sth->fetchall_hashref('traps_id');
    
    foreach (keys %$ref_result) {
        # Get Matching Status Rules
        if (defined($ref_result->{$_}->{traps_advanced_treatment}) && $ref_result->{$_}->{traps_advanced_treatment} == 1) {
            ($dstatus, $sth) = $cdb->query("SELECT * FROM traps_matching_properties
                                            LEFT JOIN service_categories ON (service_categories.sc_id = traps_matching_properties.severity_id)
                                            WHERE trap_id = " . $_ . " ORDER BY tmo_order ASC");
            return -1 if ($dstatus == -1);
            $ref_result->{$_}->{traps_matching_properties} = [];
            while (my $row = $sth->fetchrow_hashref()) {
                push @{$ref_result->{$_}->{traps_matching_properties}}, $row;
            }
        }
        
        # Get Trap PREEXEC Commands
        ($dstatus, $sth) = $cdb->query("SELECT * FROM traps_preexec WHERE trap_id = " . $_ . " ORDER BY tpe_order ASC");
        return -1 if ($dstatus == -1);
        $ref_result->{$_}->{traps_preexec} = [];
        while (my $row = $sth->fetchrow_hashref()) {
            push @{$ref_result->{$_}->{traps_preexec}}, $row;
        }
        
        # Get Associated Host
        # TODO
    }
    return (0, $ref_result);
}

sub get_hosts {
    # logger => obj
    # cbd => obj
    # trap_info => ref
    # agent_dns_name => value
    # ip_address => value
    my %args = @_;
    my ($dstatus, $sth);
    my $ref_result;
    my $request;
    
    if ($args{trap_info}->{traps_routing_mode} == 1 
        && defined($args{trap_info}->{traps_routing_value}) && $args{trap_info}->{traps_routing_value} ne '') {
        my $search_str = $args{centreontrapd}->substitute_string($args{trap_info}->{traps_routing_value});
        $search_str = $args{centreontrapd}->substitute_centreon_functions($search_str);
        $request = "SELECT host_id, host_name FROM host WHERE host_address = " . $args{cdb}->quote($search_str);
    } else {
        # Default Mode
        $request = "SELECT host_id, host_name FROM host WHERE host_address = " . $args{cdb}->quote($args{agent_dns_name}) .  " OR host_address=" . $args{cdb}->quote($args{ip_address});  
    }
    
    ($dstatus, $sth) = $args{cdb}->query($request);
    return -1 if ($dstatus == -1);
    $ref_result = $sth->fetchall_hashref('host_id');

    if ($args{logger}->is_debug()) {
        if (scalar(keys %$ref_result) == 0) {
            $args{logger}->writeLogDebug("Cant find a host. Request: " . $request);
        }
    }
    
    # Get server_id
    foreach (keys %$ref_result) {
        ($dstatus, $sth) = $args{cdb}->query("SELECT ns_host_relation.nagios_server_id, nagios_server.ns_ip_address FROM ns_host_relation, nagios_server WHERE 
                                            ns_host_relation.host_host_id = " . $ref_result->{$_}->{host_id} . " AND ns_host_relation.nagios_server_id = nagios_server.id LIMIT 1");
        return -1 if ($dstatus == -1);
        my $data = $sth->fetchrow_hashref();
        $ref_result->{$_}->{nagios_server_id} = $data->{nagios_server_id};
        $ref_result->{$_}->{ns_ip_address} = $data->{ns_ip_address};
    }
    
    return (0, $ref_result);
}

sub get_services {
    my ($cdb, $trap_id, $host_id, $result) = @_;
    my $services_do = {};
    
    ### Get service List for the Host
    my ($dstatus, $sth) = $cdb->query("SELECT s.service_id, s.service_description FROM host h, host_service_relation hsr, service s WHERE 
                                         h.host_id = " . $host_id . " AND h.host_activate = '1' AND h.host_id = hsr.host_host_id AND hsr.service_service_id = s.service_id AND s.service_activate = '1'
                                     UNION ALL SELECT s.service_id, s.service_description FROM 
                                   host h, host_service_relation hsr, hostgroup_relation hgr, service s WHERE h.host_id = " . $host_id . " AND h.host_activate = '1' AND 
                                   h.host_id = hgr.host_host_id AND hgr.hostgroup_hg_id = hsr.hostgroup_hg_id AND hsr.service_service_id = s.service_id AND s.service_activate = '1'");
    return -1 if ($dstatus == -1);
    $result = $sth->fetchall_hashref('service_id');
    foreach my $service_id (keys %$result) {
        # Search Template trap_id
        my %loop_stop = ();
        my @stack = ($service_id);
        
        while ((my $lservice_id = shift(@stack))) {
            if (defined($loop_stop{$lservice_id})) {
                # Already done
                last;
            }
            $loop_stop{$lservice_id} = 1;
            
            ($dstatus, $sth) = $cdb->query("SELECT traps_id FROM traps_service_relation WHERE service_id = '" . $lservice_id . "' AND traps_id = '" . $trap_id . "' LIMIT 1");
            return -1 if ($dstatus == -1);
            my $data = $sth->fetchrow_hashref();
            if (defined($data)) {
                $services_do->{$service_id} = $result->{$service_id};
                last;
            }
            
            ($dstatus, $sth) = $cdb->query("SELECT service_template_model_stm_id FROM service WHERE service_id = " . $lservice_id . " LIMIT 1");
            return -1 if ($dstatus == -1);
            $data = $sth->fetchrow_hashref();
            if (defined($data) && defined($data->{service_template_model_stm_id})) {
                unshift @stack, $data->{service_template_model_stm_id};
            }
        }
    }
    
    return (0, $services_do);
}

sub check_downtimes {
    my ($csdb, $downtime, $trap_time, $host_id, $ref_services, $logger) = @_;
    my $ref_result;
    
    # Only one request is $downtime == 2
    if ($downtime == 2) {
        my ($dstatus, $sth) = $csdb->query("SELECT DISTINCT IFNULL(service_id, 'host') as service_id FROM downtimes WHERE host_id = $host_id AND start_time <= $trap_time AND end_time >= $trap_time");
        return -1 if ($dstatus == -1);
        $ref_result = $sth->fetchall_hashref('service_id');
    }
    
    # Check if host is in downtime - if yes: return 1
    if ($downtime == 1) {
        # Real-Time
        my ($dstatus, $sth) = $csdb->query("SELECT host_id FROM hosts WHERE host_id = $host_id AND scheduled_downtime_depth = 1 LIMIT 1");
        return -1 if ($dstatus == -1);
        my $data = $sth->fetchrow_hashref();
        if (defined($data)) {
            # Go out. Downtime on host.
            $logger->writeLogInfo("Skipping trap: host '$host_id' in downtime");
            return 1;
        }
    } else {
        # Check it
        if (defined($ref_result->{host})) {
            $logger->writeLogInfo("Skipping trap: host '$host_id' in downtime");
            return 1;
        }
    }
    
    if (scalar(keys %{$ref_services}) == 0) {
        return 0;
    }
    
    if ($downtime == 1) {
        # Check some services only
        my ($dstatus, $sth) = $csdb->query("SELECT service_id FROM services WHERE service_id IN (" . join(',', keys %{$ref_services}) . ") AND scheduled_downtime_depth = 1");
        return -1 if ($dstatus == -1);
        $ref_result = $sth->fetchall_hashref('service_id');
    }
    
    # Parse services
    foreach my $service_id (keys %{$ref_services}) {
        if (defined($ref_result->{$service_id})) {
            $logger->writeLogInfo("Skipping trap: host '$host_id' and service $service_id in downtime");
            delete $ref_services->{$service_id};
        }
    }
    
    return 0;
}

sub set_macro {
    my ($macros, $name, $value) = @_;
    
    if (!defined($macros->{$name})) {
        $macros->{$name} = $value;
    }
}

sub get_macros_host {
    my ($cdb, $host_id) = @_;
    my ($dstatus, $sth, $value);
    my %macros;
    my %loop_stop = ();
    my @stack = ($host_id);
    
    while ((my $lhost_id = shift(@stack))) {
        if (defined($loop_stop{$lhost_id})) {
            # Already done the host
            next;
        }
        $loop_stop{$lhost_id} = 1;
    
        ($dstatus, $sth) = $cdb->query("SELECT host_snmp_community, host_snmp_version FROM host WHERE host_id = " . $lhost_id . " LIMIT 1");
        return -1 if ($dstatus == -1);
        $value = $sth->fetchrow_hashref();
        if (defined($value->{host_snmp_community}) && $value->{host_snmp_community} ne "") {
            set_macro(\%macros, '$_HOSTSNMPCOMMUNITY$', $value->{host_snmp_community});
        }
        if (defined($value->{host_snmp_version}) && $value->{host_snmp_version} ne "") {
            set_macro(\%macros, '$_HOSTSNMPVERSION$', $value->{host_snmp_version});
        }
    
        ($dstatus, $sth) = $cdb->query("SELECT host_macro_name, host_macro_value FROM on_demand_macro_host WHERE host_host_id = " . $lhost_id);
        return -1 if ($dstatus == -1);
        while ($value = $sth->fetchrow_hashref()) {
            set_macro(\%macros, $value->{host_macro_name}, $value->{host_macro_value});
        }
    
        ($dstatus, $sth) = $cdb->query("SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = " . $lhost_id . " ORDER BY `order` DESC");
        return -1 if ($dstatus == -1);
        while ($value = $sth->fetchrow_hashref()) {
            unshift @stack, $value->{host_tpl_id};
        }
    }
        
    return (0, \%macros);
}

##############
# Protocol with logdb
##############

sub send_logdb {
    my %args = @_;
    my $pipe = $args{pipe};
    my $num_args = $#{$args{entvar}} + 1;
    
    # Need atomic write (Limit to 4096 with Linux)
    $args{output_message} =~ s/\n/\\n/g;
    my $value = $args{id} . ":0:$num_args:" . 
                $args{trap_time} . "," .
                $args{cdb}->quote($args{timeout}) . "," .
                $args{cdb}->quote($args{host_name}) . "," .  
                $args{cdb}->quote($args{ip_address}) . "," .
                $args{cdb}->quote($args{agent_host_name}) . "," .
                $args{cdb}->quote($args{agent_ip_address}) . "," .
                $args{cdb}->quote($args{trap_oid}) . "," .
                $args{cdb}->quote($args{trap_name}) . "," .
                $args{cdb}->quote($args{vendor}) . "," .
                $args{cdb}->quote($args{status}) . "," .
                $args{cdb}->quote($args{severity_id}) . "," .
                $args{cdb}->quote($args{severity_name}) . ",";
    # We truncate if it
    $value .= substr($args{cdb}->quote($args{output_message}), 0, 4096 - length($value) - 1);
    print $pipe $value . "\n";

    for (my $i=0; $i <= $#{$args{entvar}}; $i++) {
        my $value = ${$args{entvar}}[$i];
        $value =~ s/\n/\\n/g;
        print $pipe $args{id} . ":1:$i:" . 
                    $i . "," .
                    $args{cdb}->quote(${$args{entvarname}}[$i]) . "," .
                    $args{cdb}->quote($value) . "," .
                    $args{trap_time} . "\n";
    }
}

##############
# CACHE MANAGEMENT
##############

sub get_cache_oids {
    # cdb => connection db
    # last_cache_time => ref
    # oids_cache => ref
    my %args = @_;

    my ($status, $sth) = $args{cdb}->query("SELECT traps_oid FROM traps");
    return -1 if ($status == -1);
    ${$args{oids_cache}} = $sth->fetchall_hashref("traps_oid");
    ${$args{last_cache_time}} = time();
    return 0;
}

sub check_known_trap {
    # logger => obj
    # config => hash
    # oid2verif => val
    # cdb => db connection
    # last_cache_time => ref
    # oids_cache => ref
    my %args = @_;
    my $oid2verif = $args{oid2verif};

    if ($args{config}->{cache_unknown_traps_enable} == 1) {
        if (!defined(${$args{last_cache_time}}) || ((time() - ${$args{last_cache_time}}) > $args{config}->{cache_unknown_traps_retention})) {
            if (get_cache_oids(cdb => $args{cdb}, oids_cache => $args{oids_cache}, last_cache_time => $args{last_cache_time}) == -1) {
                $args{logger}->writeLogError("Cant load cache trap oids.");
                return 0;
            }
        }
        if (defined(${$args{oids_cache}}->{$oid2verif})) {
            return 1;
        } else {
            $args{logger}->writeLogInfo("Unknown trap");
            return 0;
        }
    } else {
        # Read db
        my ($status, $sth) = $args{cdb}->query("SELECT traps_oid FROM traps WHERE traps_oid = " . $args{cdb}->quote($oid2verif));
        return 0 if ($status == -1);
        if (!$sth->fetchrow_hashref()) {
            $args{logger}->writeLogInfo("Unknown trap");
            return 0;
        }
    }

    return 1;
}



###
# Code from SNMPTT Modified
# Copyright 2002-2009 Alex Burger
# alex_b@users.sourceforge.net
###

sub get_trap {
    # logger => obj
    # config => hash
    # filenames => ref array
    my %args = @_;

    if (!@{$args{filenames}}) { 
        if (!(chdir($args{config}->{spool_directory}))) {
            $args{logger}->writeLogError("Unable to enter spool dir " . $args{config}->{spool_directory} . ":$!");
            return undef;
        }
        if (!(opendir(DIR, "."))) {
            $args{logger}->writeLogError("Unable to open spool dir " . $args{config}->{spool_directory} . ":$!");
            return undef;
        }
        if (!(@{$args{filenames}} = readdir(DIR))) {
            $args{logger}->writeLogError("Unable to read spool dir " . $args{config}->{spool_directory} . ":$!");
            return undef;
        }
        closedir(DIR);
        @{$args{filenames}} = sort (@{$args{filenames}});
    }
    
    while ((my $file = shift @{$args{filenames}})) {
        next if ($file eq ".");
        next if ($file eq "..");
        next if (! -f $args{config}->{spool_directory} . '/' . $file);
        return $file;
    }
    return undef;
}

sub purge_duplicate_trap {
    # config => hash
    # duplicate_traps => ref hash 
    my %args = @_;

    if ($args{config}->{duplicate_trap_window}) {
        # Purge traps older than duplicate_trap_window in %duplicate_traps
        my $duplicate_traps_current_time = time();
        foreach my $key (sort keys %{$args{duplicate_traps}}) {
            if ($args{duplicate_traps}->{$key} < $duplicate_traps_current_time - $args{config}->{duplicate_trap_window}) {
                # Purge the record
                delete $args{duplicate_traps}->{$key};
            }
        }
    }
}

sub readtrap {
    # logger => obj
    # config => hash
    # handle => str
    # agent_dns => ref
    # trap_date => ref
    # trap_time => ref
    # trap_date_time => ref
    # trap_date_time_epoch => ref
    # duplicate_traps => ref hash
    # digest_trap => ref,
    # var => ref array
    # entvar => ref array
    # entvarname => ref array
    
    my %args = @_;
    my $input = $args{handle};

    # Flush out @tempvar, @var and @entvar
    my @tempvar = ();
    @{$args{var}} = ();
    @{$args{entvar}} = ();
    @{$args{entvarname}} = ();
    my @rawtrap = ();

    $args{logger}->writeLogDebug("Reading trap.  Current time: " . scalar(localtime()));

    chomp(${$args{trap_date_time_epoch}} = (<$input>));	# Pull time trap was spooled
    push(@rawtrap, ${$args{trap_date_time_epoch}});
    if (${$args{trap_date_time_epoch}} eq "") {
        if ($args{logger}->is_debug()) {
            $args{logger}->writeLogDebug("  Invalid trap file.  Expected a serial time on the first line but got nothing");
            return 0;
        }
    }
    ${$args{trap_date_time_epoch}} =~ s(`)(')g;	#` Replace any back ticks with regular single quote

    my @localtime_array;
    if ($args{config}->{use_trap_time} == 1) {
        @localtime_array = localtime(${$args{trap_date_time_epoch}});

        if ($args{config}->{date_time_format} eq "") {
            ${$args{trap_date_time}} = localtime(${$args{trap_date_time_epoch}});
        } else {
            ${$args{trap_date_time}} = strftime($args{config}->{date_time_format}, @localtime_array);
        }
    } else {
        @localtime_array = localtime();

        if ($args{config}->{date_time_format} eq "") {
            ${$args{trap_date_time}} = localtime();
        } else {
            ${$args{trap_date_time}} = strftime($args{config}->{date_time_format}, @localtime_array);
        }
    }

    ${$args{trap_date}} = strftime($args{config}->{date_format}, @localtime_array);
    ${$args{trap_time}} = strftime($args{config}->{time_format}, @localtime_array);

    # Pull in passed SNMP info from snmptrapd via STDIN and place in the array @tempvar
    chomp($tempvar[0]=<$input>);	# hostname
    push(@rawtrap, $tempvar[0]);
    $tempvar[0] =~ s(`)(')g;	#` Replace any back ticks with regular single quote 
    if ($tempvar[0] eq "") {
        if ($args{logger}->is_debug()) {
            $args{logger}->writeLogDebug("  Invalid trap file.  Expected a hostname on line 2 but got nothing");
            return 0;
        }
    }
        
    chomp($tempvar[1]=<$input>);	# ip address
    push(@rawtrap, $tempvar[1]);
    $tempvar[1] =~ s(`)(')g;	#` Replace any back ticks with regular single quote
    if ($tempvar[1] eq "") {
        if ($args{logger}->is_debug()) {
            $args{logger}->writeLogDebug("  Invalid trap file.  Expected an IP address on line 3 but got nothing");
            return 0;
        }
    }

    # With DNS resolution disabled in snmptrapd, some systems pass the hostname as:
    # UDP: [x.x.x.x]:161->[y.y.y.y]
    # If this is detected, use x.x.x.x as the hostname.
    if ($tempvar[0] =~ /\[(\d+\.\d+\.\d+\.\d+)\].*?->\[(\d+\.\d+\.\d+\.\d+)\]/) {
        $tempvar[0] = $1;
    }
    
    # Some systems pass the IP address as udp:ipaddress:portnumber.  This will pull
    # out just the IP address
    $tempvar[1] =~ /(\d+\.\d+\.\d+\.\d+)/;
    $tempvar[1] = $1;

    # Net-SNMP 5.4 has a bug which gives <UNKNOWN> for the hostname
    if ($tempvar[0] =~ /<UNKNOWN>/) {
        $tempvar[0] = $tempvar[1];
    }

    #Process varbinds
    #Separate everything out, keeping both the variable name and the value
    my $linenum = 1;
    my $variable_fix;
    while (defined(my $line = <$input>)) {
        push(@rawtrap, $line);
        $line =~ s(`)(')g;	#` Replace any back ticks with regular single quote

        # Remove escape from quotes if enabled
        if ($args{config}->{remove_backslash_from_quotes} == 1) {
            $line =~ s/\\\"/"/g;
        }

        my $temp1;
        my $temp2;

        ($temp1, $temp2) = split (/ /, $line, 2);

        chomp ($temp1);       # Variable NAME
        chomp ($temp2);       # Variable VALUE
        chomp ($line);

        if ($linenum == 1) {
            # Check if line 1 contains 'variable value' or just 'value' 
            if (defined($temp2)) {
                $variable_fix = 0;
            } else {
                $variable_fix = 1;
            }
        }

        if ($variable_fix == 0) {
            # Make sure variable names are numerical
            $temp1 = translate_symbolic_to_oid($temp1, $args{logger}, $args{config});

            # If line begins with a double quote (") but does not END in a double quote then we need to merge
            # the following lines together into one until we find the closing double quote.  Allow for escaped quotes.
            # Net-SNMP sometimes divides long lines into multiple lines..
            if ( ($temp2 =~ /^\"/) && ( ! ($temp2 =~ /[^\\]\"$/)) ) {
                $args{logger}->writeLogDebug("  Multi-line value detected - merging onto one line...");
                $temp2 =~ s/[\r\n]//g;			# Remove the newline character
                while (defined(my $line2 = <$input>)) {
                    chomp $line2;
                    push(@rawtrap, $line2);
                    $temp2 .= " " . $line2;
                    # Check if line ends in a non-escaped quote
                    if (($line2 =~ /\"$/) && ($line2 !~ /\\\"$/)) {
                        last;
                    }
                }
            }

            # If the value is blank, set it to (null)
            if ($temp2 eq "") {
                $temp2 = "(null)";
            }

            # Have quotes around it?
            if ($temp2 =~ /^\"/ && $temp2 =~ /\"$/) {
                $temp2 = substr($temp2,1,(length($temp2)-2)); # Remove quotes
                push(@tempvar, $temp1);
                push(@tempvar, $temp2);
            } else {
                push(@tempvar, $temp1);
                push(@tempvar, $temp2);
            }
        } else {
            # Should have been variable value, but only value found.  Workaround
            # 
            # Normally it is expected that a line contains a variable name
            # followed by a space followed by the value (except for the 
            # first line which is the hostname and the second which is the
            # IP address).  If there is no variable name on the line (only
            # one string), then add a variable string called 'variable' so 
            # it is handled correctly in the next section.
            # This happens with ucd-snmp v4.2.3 but not v4.2.1 or v4.2.5.
            # This appears to be a bug in ucd-snmp v4.2.3.  This works around
            # the problem by using 'variable' for the variable name, although 
            # v4.2.3 should NOT be used with SNMPTT as it prevents SNMP V2 traps 
            # from being handled correctly.

            $args{logger}->writeLogDebug("Data passed from snmptrapd is incorrect.  UCD-SNMP v4.2.3 is known to cause this");

            # If line begins with a double quote (") but does not END in a double quote then we need to merge
            # the following lines together into one until we find the closing double quote.  Allow for escaped quotes.
            # Net-SNMP sometimes divides long lines into multiple lines..
            if ( ($line =~ /^\"/) && ( ! ($line =~ /[^\\]\"$/)) ) {
                $args{logger}->writeLogDebug("  Multi-line value detected - merging onto one line...");
                $temp2 =~ s/[\r\n]//g;			# Remove newline characters
                while (defined(my $line2 = <$input>)) {
                    chomp $line2;
                    push(@rawtrap, $line2);
                    $temp2 .= " " . $line2;
                    if (($line2 =~ /\"$/) && ($line2 !~ /\\\"$/)) { # Ends in a non-escaped quote or it's a single line with a quote.
                        last;
                    }
                }
            }

            # If the value is blank, set it to (null)
            if ($line eq "") {
                $line = "(null)";
            }

            # Have quotes around it?
            if ($line =~ /^\"/ && $line =~ /\"$/) {
                $line = substr($line,1,(length($line)-2));		# Remove quotes
                push(@tempvar, "variable");
                push(@tempvar, $line);
            } else {
                push(@tempvar, "variable");
                push(@tempvar, $line);
            }
        }

        $linenum++;
    }

    if ($args{logger}->is_debug()) {
        # Print out raw trap passed from snmptrapd
        $args{logger}->writeLogDebug("Raw trap passed from snmptrapd:");
        for (my $i=0;$i <= $#rawtrap;$i++) {
            chomp($rawtrap[$i]);
            $args{logger}->writeLogDebug("$rawtrap[$i]");
        }

        # Print out all items passed from snmptrapd
        $args{logger}->writeLogDebug("Items passed from snmptrapd:");
        for (my $i=0;$i <= $#tempvar;$i++) {
            $args{logger}->writeLogDebug("value $i: $tempvar[$i]");
        }
    }

    # Copy what I need to new variables to make it easier to manipulate later

    # Standard variables
    ${$args{var}}[0] = $tempvar[0];		# hostname
    ${$args{var}}[1] = $tempvar[1];		# ip address
    ${$args{var}}[2] = $tempvar[3];		# uptime
    ${$args{var}}[3] = $tempvar[5];		# trapname / OID - assume first value after uptime is
        # the trap OID (value for .1.3.6.1.6.3.1.1.4.1.0)

    ${$args{var}}[4] = "";	 # Clear ip address from trap agent
    ${$args{var}}[5] = "";	 # Clear trap community string
    ${$args{var}}[6] = "";	 # Clear enterprise
    ${$args{var}}[7] = "";	 # Clear securityEngineID
    ${$args{var}}[8] = "";	 # Clear securityName
    ${$args{var}}[9] = "";	 # Clear contextEngineID
    ${$args{var}}[10] = ""; # Clear contextName

    # Make sure trap OID is numerical as event lookups are done using numerical OIDs only
    ${$args{var}}[3] = translate_symbolic_to_oid(${$args{var}}[3], $args{logger}, $args{config});

    # Cycle through remaining variables searching for for agent IP (.1.3.6.1.6.3.18.1.3.0),
    # community name (.1.3.6.1.6.3.18.1.4.0) and enterpise (.1.3.6.1.6.3.1.1.4.3.0)
    # All others found are regular passed variables
    my $j=0;
    for (my $i=6;$i <= $#tempvar; $i+=2) {
        
        if ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.3.0$/) { # ip address from trap agent
            ${$args{var}}[4] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.4.0$/)	{ # trap community string
            ${$args{var}}[5] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.1.1.4.3.0$/) {	# enterprise
            # ${$args{var}}[6] = $tempvar[$i+1];
            # Make sure enterprise value is numerical
            ${$args{var}}[6] = translate_symbolic_to_oid($tempvar[$i+1], $args{logger}, $args{config});
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.10.2.1.1.0$/) { # securityEngineID
            ${$args{var}}[7] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.1.1.3$/) { # securityName
            ${$args{var}}[8] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.1.1.4$/) {	# contextEngineID
            ${$args{var}}[9] = $tempvar[$i+1];
        }
        elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.1.1.5$/)	{ # contextName
            ${$args{var}}[10] = $tempvar[$i+1];
        } else { # application specific variables
            ${$args{entvarname}}[$j] = $tempvar[$i];
            ${$args{entvar}}[$j] = $tempvar[$i+1];
            $j++;
        }
    }

    # Only if it's not already resolved
    if ($args{config}->{dns_enable} == 1 && ${$args{var}}[0] =~  /^\d+\.\d+\.\d+\.\d+$/) {
        my $temp = gethostbyaddr(Socket::inet_aton(${$args{var}}[0]),Socket::AF_INET());
        if (defined ($temp)) {
            $args{logger}->writeLogDebug("Host IP address (" . ${$args{var}}[0] . ") resolved to: $temp");
            ${$args{var}}[0] = $temp;
        } else {
            $args{logger}->writeLogDebug("Host IP address (" . ${$args{var}}[0] . ") could not be resolved by DNS. Variable \$r / \$R etc will use the IP address");
        }
    }

    # If the agent IP is blank, copy the IP from the host IP.
    # var[4] would only be blank if it wasn't passed from snmptrapd, which
    # should only happen with ucd-snmp 4.2.3, which you should be using anyway!
    if (${$args{var}}[4] eq '') {
        ${$args{var}}[4] = ${$args{var}}[1];
        $args{logger}->writeLogDebug("Agent IP address was blank, so setting to the same as the host IP address of " . ${$args{var}}[1]);
    }

    # If the agent IP is the same as the host IP, then just use the host DNS name, no need
    # to look up, as it's obviously the same..
    if (${$args{var}}[4] eq ${$args{var}}[1]) {
        $args{logger}->writeLogDebug("Agent IP address (" . ${$args{var}}[4] . ") is the same as the host IP, so copying the host name: " . ${$args{var}}[0]);
        ${$args{agent_dns_name}} = ${$args{var}}[0];
    } else {
        ${$args{agent_dns_name}} = ${$args{var}}[4];     # Default to IP address
        if ($args{config}->{dns_enable} == 1 && ${$args{var}}[4] ne '') {
            my $temp = gethostbyaddr(Socket::inet_aton(${$args{var}}[4]),Socket::AF_INET());
            if (defined ($temp)) {
                $args{logger}->writeLogDebug("Agent IP address (" . ${$args{var}}[4] . ") resolved to: $temp");
                ${$args{agent_dns_name}} = $temp;
            } else {
                $args{logger}->writeLogDebug("Agent IP address (" . ${$args{var}}[4] . ") could not be resolved by DNS.  Variable \$A etc will use the IP address");
            }
        }
    }

    if ($args{config}->{strip_domain}) {
        ${$args{var}}[0] = strip_domain_name(${$args{var}}[0], $args{config}->{strip_domain}, $args{config});
        ${$args{agent_dns_name}} = strip_domain_name(${$args{agent_dns_name}}, $args{config}->{strip_domain}, $args{config});
    }

    $args{logger}->writeLogDebug("Trap received from $tempvar[0]: $tempvar[5]");

   if ($args{logger}->is_debug()) {
        $args{logger}->writeLogDebug("0:		hostname");
        $args{logger}->writeLogDebug("1:		ip address");
        $args{logger}->writeLogDebug("2:		uptime");
        $args{logger}->writeLogDebug("3:		trapname / OID");
        $args{logger}->writeLogDebug("4:		ip address from trap agent");
        $args{logger}->writeLogDebug("5:		trap community string");
        $args{logger}->writeLogDebug("6:		enterprise");
        $args{logger}->writeLogDebug("7:		securityEngineID        (not use)");
        $args{logger}->writeLogDebug("8:		securityName            (not use)");
        $args{logger}->writeLogDebug("9:		contextEngineID         (not use)");
        $args{logger}->writeLogDebug("10:		contextName             (not)");
        $args{logger}->writeLogDebug("0+:		passed variables");	

        #print out all standard variables
        for (my $i=0;$i <= $#{$args{var}};$i++) {
            $args{logger}->writeLogDebug("Value $i: " . ${$args{var}}[$i]);
        }

        $args{logger}->writeLogDebug("Agent dns name: " . ${$args{agent_dns_name}});

        #print out all enterprise specific variables
        for (my $i=0;$i <= $#{$args{entvar}};$i++) {
            $args{logger}->writeLogDebug("Ent Value $i (\$" . ($i+1) . "): " . ${$args{entvarname}}[$i] . "=" . ${$args{entvar}}[$i]);
        }
    }

    # Generate hash of trap and detect duplicates
    if ($args{config}->{duplicate_trap_window}) {
        my $md5 = Digest::MD5->new;
        # All variables except for uptime.
        $md5->add(${$args{var}}[0],${$args{var}}[1].${$args{var}}[3].${$args{var}}[4].${$args{var}}[5].${$args{var}}[6].${$args{var}}[7].${$args{var}}[8].${$args{var}}[9].${$args{var}}[10]."@{$args{entvar}}");
        
        my $trap_digest = $md5->hexdigest;
        ${$args{digest_trap}} = $trap_digest;

        $args{logger}->writeLogDebug("Trap digest: $trap_digest");

        if ($args{duplicate_traps}->{$trap_digest}) {
            # Duplicate trap detected.  Skipping trap...
            return -1;
        }

        $args{duplicate_traps}->{$trap_digest} = time();
    }

    return 1;

    # Variables of trap received by SNMPTRAPD:
    #
    # $var[0]   hostname
    # $var[1]   ip address
    # $var[2]   uptime
    # $var[3]   trapname / OID
    # $var[4]   ip address from trap agent
    # $var[5]   trap community string
    # $var[6]   enterprise
    # $var[7]   securityEngineID                (snmptthandler-embedded required)
    # $var[8]   securityName                    (snmptthandler-embedded required)
    # $var[9]   contextEngineID                 (snmptthandler-embedded required)
    # $var[10]  contextName                     (snmptthandler-embedded required)
    #
    # $entvarname[0]  passed variable name 1
    # $entvarname[1]  passed variable name 2
    #
    # $entvar[0]    passed variable 1
    # $entvar[1]    passed variable 2
    # .
    # .
    # etc..
    #
    ##############################################################################
}

# Used when reading received traps to symbolic names in variable names and
# values to numerical
sub translate_symbolic_to_oid
{
    my $temp = shift;
    my $logger = shift;
    my $config = shift;
    
    # Check to see if OID passed from snmptrapd is fully numeric.  If not, try to translate
    if (! ($temp =~ /^(\.\d+)+$/))  {
        # Not numeric
        # Try to convert to numerical
        $logger->writeLogDebug("Symbolic trap variable name detected ($temp).  Will attempt to translate to a numerical OID");
        if ($config->{net_snmp_perl_enable} == 1) {
            my $temp3 = SNMP::translateObj("$temp",0);
            if (defined ($temp3) ) {
                $logger->writeLogDebug("  Translated to $temp3");
                $temp = $temp3;
            } else {
                # Could not translate default to numeric
                $logger->writeLogDebug("  Could not translate - will leave as-is");
            }
        } else {
            $logger->writeLogDebug("  Could not translate - Net-SNMP Perl module not enabled - will leave as-is");
        }
    }
  return $temp;
}

# Strip domain name from hostname
sub strip_domain_name {
    my $name = shift;
    my $mode = shift;
    my $config = shift;

    # If mode = 1, strip off all domain names leaving only the host
    if ($mode == 1 && !($name =~ /^\d+\.\d+\.\d+\.\d+$/)) {
        if ($name =~ /\./) { # Contain a . ?
            $name =~ /^([^\.]+?)\./;
            $name = $1;
        }
    } elsif ($mode == 2 && !($name =~ /^\d+\.\d+\.\d+\.\d+$/)) { # If mode = 2, strip off the domains as listed in strip_domain_list in .ini file 
        if (@{$config->{strip_domain_list}}) {
            foreach my $strip_domain_list_temp (@{$config->{strip_domain_list}}) {
                if ($strip_domain_list_temp =~ /^\..*/) { # If domain from list starts with a '.' then remove it first
                    ($strip_domain_list_temp) = $strip_domain_list_temp =~ /^\.(.*)/;
                }

                if ($name =~ /^.+\.$strip_domain_list_temp/) { # host is something . domain name?
                    $name =~ /(.*)\.$strip_domain_list_temp/;	# strip the domain name
                    $name = $1;
                    last;  # Only process once 
                }
            }
        }
    }
    return $name;
}

1;
