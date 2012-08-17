#!/usr/bin/perl
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
# SVN : $URL
# SVN : $Id
#
####################################################################################

sub init_modules {
    ####
    # SNMP Module
    ####
    if ($centrapmanager_net_snmp_perl_enable == 1) {
        eval 'require SNMP;';
        if ($@) {
            logit($@, "EE");
            logit("Could not load the Perl module SNMP!  If net_snmp_perl_enable is", "EE");
            logit("enabled then the SNMP module is required", "EE");
            logit("for system requirements.  Note:  uses the Net-SNMP package's", "EE");
            logit("SNMP module, NOT the CPAN Net::SNMP module!", "EE");
            myDie("die");
        }
        require SNMP;
        if (defined ($centrapmanager_mibs_environment) && $centrapmanager_mibs_environment ne '') {
            $ENV{'MIBS'} = $centrapmanager_mibs_environment;
        }
        &SNMP::initMib();

        if ($centrapmanager_log_debug >= 1) {
            logit("********** Net-SNMP version $SNMP::VERSION Perl module enabled **********", "EE");
            if (defined ($centrapmanager_mibs_environment)) {
                logit("********** MIBS: $centrapmanager_mibs_environment **********", "DD");
            }
        }
    }
    
    
    
    ####
    # Socket Module
    ####
    if ($centrapmanager_dns_enable == 1) {
        eval 'require Socket;';
        if ($@) {
            logit($@, "EE");
            logit("Could not load the Perl module Socket!  If dns_enable", "EE");
            logit("is enabled then the Socket module is required", "EE");
            logit("for system requirements", "EE");
            myDie("die");
        }
        require Socket;
        if ($centrapmanager_log_debug >= 1) {
            logit("********** DNS enabled **********", "DD");
        }
    }
    
    if ($centrapmanager_duplicate_trap_window > 0) {
        eval 'require Digest::MD5;';
        if ($@) {
            logit($@, "EE");
            logit("Could not load the Perl module Digest::MD5!  If centrapmanager_duplicate_trap_window", "EE");
            logit("is set then the Digest::MD5 module is required", "EE");
            logit("for system requirements.", "EE");
            myDie("die");
        }
        require Digest::MD5;
    }
}

sub init_config {
    my $file = $_[0];
    my $type = $_[0];
    
    unless (my $return = do $file) {
        logit("couldn't parse $file: $@", "EE") if $@;
        logit("couldn't do $file: $!", "EE") unless defined $return;
        logit("couldn't run $file", "EE") unless $return;
        if ($type == 1) {
            myDie("finish");
        }
    }
}

sub manage_params_conf {
    if (!defined($centrapmanager_date_format) || $centrapmanager_date_format eq "") {
        $centrapmanager_date_format = "%a %b %e %Y";
    }
    if (!defined($centrapmanager_time_format) || $centrapmanager_time_format eq "") {
        $centrapmanager_time_format = "%H:%M:%S";
    }
}

##############
# CACHE MANAGEMENT
##############

sub get_cache_oids {
    my $sth = $dbh->prepare("SELECT traps_oid FROM traps");
    if (!$sth->execute()) {
        logit("SELECT traps_oid from traps error: " . $sth->errstr, "EE");
        return 1;
    }
    $oids_cache = $sth->fetchall_hashref("traps_oid");
    $last_cache_time = time();
    return 0;
}

sub write_cache_file {
    if (!open(FILECACHE, ">", $centrapmanager_cache_unknown_traps_file)) {
        logit("Can't write $centrapmanager_cache_unknown_traps_file; $!", "EE");
        logit("Go to DB to get info", "EE");
        return 1;
    }
    my $oids_value = join("\n", keys %$oids_cache);
    print FILECACHE $oids_value;
    close FILECACHE;
    logit("Cache file refreshed", "II") if ($centrapmanager_log_debug >= 1);
    return 0;
}

sub check_known_trap {
    my $oid2verif = $_[0];
    my $db_mode = 1;

    if ($centrapmanager_cache_unknown_traps_enable == 1) {
        if ($centrapmanager_daemon != 1) {
            use File::stat;

            $db_mode = 0;
            if (-e $centrapmanager_cache_unknown_traps_file) {
                if ((my $result = stat($centrapmanager_cache_unknown_traps_file))) {
                    if ((time() - $result->mtime) > $centrapmanager_cache_unknown_traps_retention) {
                        logit("Try to rewrite cache", "II");
                        !($db_mode = get_cache_oids()) && ($db_mode = write_cache_file());
                    }
                } else {
                    logit("Can't stat file $centrapmanager_cache_unknown_traps_file: $!", "EE");
                    logit("Go to DB to get info", "EE");
                    $db_mode = 1;
                }
            } else {
                !($db_mode = get_cache_oids()) && ($db_mode = write_cache_file());
            }
        } else {
            if (!defined($last_cache_time) || ((time() - $last_cache_time) > $centrapmanager_cache_unknown_traps_retention)) {
                $db_mode = get_cache_oids();
            }
        }
    }

    if ($db_mode == 0) {
        if (defined($oids_cache)) {
            if (defined($oids_cache->{$oid2verif})) {
                return 1;
            } else {
                logit("Unknown trap", "II") if ($centrapmanager_log_debug >= 1);
                return 0;
           }
        } else {
            if (!open FILECACHE, $centrapmanager_cache_unknown_traps_file) {
                logit("Can't read file $centrapmanager_cache_unknown_traps_file: $!", "EE");
                $db_mode = 1;
            } else {
                while (<FILECACHE>) {
                    if (/^$oid2verif$/m) {
                        return 1;
                    }
                }
                close FILECACHE;
                logit("Unknown trap", "II") if ($centrapmanager_log_debug >= 1);
                return 0;
            }
        }
    }

    if ($db_mode == 1) {
        # Read db
        my $sth = $dbh->prepare("SELECT traps_oid FROM traps WHERE traps_oid = " . $dbh->quote($oid2verif));
        if (!$sth->execute()) {
            logit("SELECT traps_oid from traps error: " . $sth->errstr, "EE");
            return 0;
        }
        if ($sth->rows == 0) {
            logit("Unknown trap", "II") if ($centrapmanager_log_debug >= 1);
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
    if (!@filenames) { 
        if (!(chdir($spool_directory))) {
            logit("Unable to enter spool dir $spool_directory:$!", "EE");
            return undef;
        }
        if (!(opendir(DIR, "."))) {
            logit("Unable to open spool dir $spool_directory:$!", "EE");
            return undef;
        }
        if (!(@filenames = readdir(DIR))) {
            logit("Unable to read spool dir $spool_directory:$!", "EE");
            return undef;
        }
        closedir(DIR);
        @filenames = sort (@filenames);
    }
    
    while (($file = shift @filenames)) {
        next if ($file eq ".");
        next if ($file eq "..");
        return $file;
    }
    return undef;
}

sub purge_duplicate_trap {
    if ($centrapmanager_duplicate_trap_window) {
        # Purge traps older than duplicate_trap_window in %duplicate_traps
        my $duplicate_traps_current_time = time();
        foreach my $key (sort keys %duplicate_traps) {
            if ($duplicate_traps{$key} < $duplicate_traps_current_time - $centrapmanager_duplicate_trap_window) {
                # Purge the record
                delete $duplicate_traps{$key};
            }
        }
    }
}

sub readtrap {
    # Flush out @tempvar, @var and @entvar
    my @tempvar = ();
    @var = ();
    @entvar = ();
    @entvarname = ();
    my @rawtrap = ();

    # Statistics
    $g_total_traps_received++;

    if ($centrapmanager_log_debug >= 2) {
        logit("Reading trap.  Current time: " . scalar(localtime()), "DD");
    }

    if ($centrapmanager_daemon == 1) {
        chomp($trap_date_time_epoch = (<$input>));	# Pull time trap was spooled
        push(@rawtrap, $trap_date_time_epoch);
        if ($trap_date_time_epoch eq "") {
            if ($centrapmanager_log_debug >= 1) {
                logit("  Invalid trap file.  Expected a serial time on the first line but got nothing", "DD");
                return 0;
            }
        }
        $trap_date_time_epoch =~ s(`)(')g;	#` Replace any back ticks with regular single quote
    } else {
        $trap_date_time_epoch = time();		# Use current time as time trap was received
    }

    my @localtime_array;
    if ($centrapmanager_daemon == 1 && $centrapmanager_use_trap_time == 1) {
        @localtime_array = localtime($trap_date_time_epoch);

        if ($centrapmanager_date_time_format eq "") {
            $trap_date_time = localtime($trap_date_time_epoch);
        } else {
            $trap_date_time = strftime($centrapmanager_date_time_format, @localtime_array);
        }
    } else {
        @localtime_array = localtime();

        if ($centrapmanager_date_time_format eq "") {
            $trap_date_time = localtime();
        } else {
            $trap_date_time = strftime($centrapmanager_date_time_format, @localtime_array);
        }
    }

    $trap_date = strftime($centrapmanager_date_format, @localtime_array);
    $trap_time = strftime($centrapmanager_time_format, @localtime_array);

    # Pull in passed SNMP info from snmptrapd via STDIN and place in the array @tempvar
    chomp($tempvar[0]=<$input>);	# hostname
    push(@rawtrap, $tempvar[0]);
    $tempvar[0] =~ s(`)(')g;	#` Replace any back ticks with regular single quote 
    if ($tempvar[0] eq "") {
        if ($centrapmanager_log_debug >= 1) {
            logit("  Invalid trap file.  Expected a hostname on line 2 but got nothing", "DD");
            return 0;
        }
    }
        
    chomp($tempvar[1]=<$input>);	# ip address
    push(@rawtrap, $tempvar[1]);
    $tempvar[1] =~ s(`)(')g;	#` Replace any back ticks with regular single quote
    if ($tempvar[1] eq "") {
        if ($centrapmanager_log_debug >= 1) {
            logit("  Invalid trap file.  Expected an IP address on line 3 but got nothing", "DD");
            return 0;
        }
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
    while (defined(my $line = <$input>)) {
        push(@rawtrap, $line);
        $line =~ s(`)(')g;	#` Replace any back ticks with regular single quote

        # Remove escape from quotes if enabled
        if ($centrapmanager_remove_backslash_from_quotes == 1) {
            $line =~ s/\\\"/"/g;
        }

        my $temp1;
        my $temp2;

        ($temp1, $temp2) = split (/ /, $line, 2);

        chomp ($temp1);       # Variable NAME
        chomp ($temp2);       # Variable VALUE
        chomp ($line);

        my $variable_fix;
        #if ($linenum == 1) {
            # Check if line 1 contains 'variable value' or just 'value' 
            if (defined($temp2)) {
                $variable_fix = 0;
            } else {
                $variable_fix = 1;
            }
        #}

        if ($variable_fix == 0 ) {
            # Make sure variable names are numerical
            $temp1 = translate_symbolic_to_oid($temp1);

            # If line begins with a double quote (") but does not END in a double quote then we need to merge
            # the following lines together into one until we find the closing double quote.  Allow for escaped quotes.
            # Net-SNMP sometimes divides long lines into multiple lines..
            if ( ($temp2 =~ /^\"/) && ( ! ($temp2 =~ /[^\\]\"$/)) ) {
                if ($centrapmanager_log_debug >= 2) {
                    logit("  Multi-line value detected - merging onto one line...", "DD");
                }
                chomp $temp2; # Remove the newline character
                while (defined(my $line2 = <$input>)) {
                    chomp $line2;
                    push(@rawtrap, $line2);
                    $temp2.=" ".$line2;
                    # Ends in a non-escaped quote
                    if ($line2 =~ /[^\\]\"$/) {
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

            if ($centrapmanager_log_debug >= 2) {
                logit("Data passed from snmptrapd is incorrect.  UCD-SNMP v4.2.3 is known to cause this", "DD");
            }

            # If line begins with a double quote (") but does not END in a double quote then we need to merge
            # the following lines together into one until we find the closing double quote.  Allow for escaped quotes.
            # Net-SNMP sometimes divides long lines into multiple lines..
            if ( ($line =~ /^\"/) && ( ! ($line =~ /[^\\]\"$/)) ) {
                if ($centrapmanager_log_debug >= 2) {
                    logit("  Multi-line value detected - merging onto one line...", "DD");
                }
                chomp $line;				# Remove the newline character
                while (defined(my $line2 = <$input>)) {
                    chomp $line2;
                    push(@rawtrap, $line2);
                    $line.=" ".$line2;
                    # Ends in a non-escaped quote
                    if ($line2 =~ /[^\\]\"$/) {
                        last;
                    }
                }
            }

            # If the value is blank, set it to (null)
            if ($line eq "") {
                $line = "(null)";
            }

            # Have quotes around it?
            if ($line =~ /^\"/ && $line =~ /$\"/) {
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

    if ($centrapmanager_log_debug >= 2) {
        # Print out raw trap passed from snmptrapd
        logit("Raw trap passed from snmptrapd:", "DD");
        for (my $i=0;$i <= $#rawtrap;$i++) {
            chomp($rawtrap[$i]);
            logit("$rawtrap[$i]", "DD");
        }

        # Print out all items passed from snmptrapd
        logit("Items passed from snmptrapd:", "DD");
        for (my $i=0;$i <= $#tempvar;$i++) {
            logit("value $i: $tempvar[$i]", "DD");
        }
    }

    # Copy what I need to new variables to make it easier to manipulate later

    # Standard variables
    $var[0] = $tempvar[0];		# hostname
    $var[1] = $tempvar[1];		# ip address
    $var[2] = $tempvar[3];		# uptime
    $var[3] = $tempvar[5];		# trapname / OID - assume first value after uptime is
        # the trap OID (value for .1.3.6.1.6.3.1.1.4.1.0)

    $var[4] = "";	 # Clear ip address from trap agent
    $var[5] = "";	 # Clear trap community string
    $var[6] = "";	 # Clear enterprise
    $var[7] = "";	 # Clear securityEngineID
    $var[8] = "";	 # Clear securityName
    $var[9] = "";	 # Clear contextEngineID
    $var[10] = ""; # Clear contextName

    # Make sure trap OID is numerical as event lookups are done using numerical OIDs only
    $var[3] = translate_symbolic_to_oid($var[3]);

    # Cycle through remaining variables searching for for agent IP (.1.3.6.1.6.3.18.1.3.0),
    # community name (.1.3.6.1.6.3.18.1.4.0) and enterpise (.1.3.6.1.6.3.1.1.4.3.0)
    # All others found are regular passed variables
    my $j=0;
    for (my $i=6;$i <= $#tempvar; $i+=2) {
        
        if ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.3.0$/) { # ip address from trap agent
            $var[4] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.4.0$/)	{ # trap community string
            $var[5] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.1.1.4.3.0$/) {	# enterprise
            # $var[6] = $tempvar[$i+1];
            # Make sure enterprise value is numerical
            $var[6] = translate_symbolic_to_oid($tempvar[$i+1]);
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.10.2.1.1.0$/) { # securityEngineID
            $var[7] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.1.1.3$/) { # securityName
            $var[8] = $tempvar[$i+1];
        } elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.1.1.4$/) {	# contextEngineID
            $var[9] = $tempvar[$i+1];
        }
        elsif ($tempvar[$i] =~ /^.1.3.6.1.6.3.18.1.1.1.5$/)	{ # contextName
            $var[10] = $tempvar[$i+1];
        } else { # application specific variables
            $entvarname[$j] = $tempvar[$i];
            $entvar[$j] = $tempvar[$i+1];
            $j++;
        }
    }

    # Only if it's not already resolved
    if ($centrapmanager_dns_enable == 1 && $var[0] =~  /^\d+\.\d+\.\d+\.\d+$/) {
        my $temp = gethostbyaddr(Socket::inet_aton($var[0]),Socket::AF_INET());
        if (defined ($temp)) {
            if ($centrapmanager_log_debug >= 1) {
                logit("Host IP address ($var[0]) resolved to: $temp", "DD");
            }
            $var[0] = $temp;
        } else {
            if ($centrapmanager_log_debug >= 1) {
                logit("Host IP address ($var[0]) could not be resolved by DNS.  Variable \$r / \$R etc will use the IP address", "DD");
            }
        }
    }

    # If the agent IP is blank, copy the IP from the host IP.
    # var[4] would only be blank if it wasn't passed from snmptrapd, which
    # should only happen with ucd-snmp 4.2.3, which you should be using anyway!
    if ($var[4] eq '') {
        $var[4] = $var[1];
        if ($centrapmanager_log_debug >= 1) {
            logit("Agent IP address was blank, so setting to the same as the host IP address of $var[1]", "DD");
        }
    }

    # If the agent IP is the same as the host IP, then just use the host DNS name, no need
    # to look up, as it's obviously the same..
    if ($var[4] eq $var[1]) {
        if ($centrapmanager_log_debug >= 1) {
            logit("Agent IP address ($var[4]) is the same as the host IP, so copying the host name: $var[0]", "DD");
        }
        $agent_dns_name = $var[0];
    } else {
        $agent_dns_name = $var[4];     # Default to IP address
        if ($centrapmanager_dns_enable == 1 && $var[4] ne '') {
            my $temp = gethostbyaddr(Socket::inet_aton($var[4]),Socket::AF_INET());
            if (defined ($temp)) {
                if ($centrapmanager_log_debug >= 1) {
                    logit("Agent IP address ($var[4]) resolved to: $temp", "DD");
                }
                $agent_dns_name = $temp;
            } else {
                if ($centrapmanager_log_debug >= 1) {
                    logit("Agent IP address ($var[4]) could not be resolved by DNS.  Variable \$A etc will use the IP address", "DD");
                }
            }
        }
    }

    if ($centrapmanager_strip_domain) {
        $var[0] = strip_domain_name($var[0], $centrapmanager_strip_domain);
        $agent_dns_name = strip_domain_name($agent_dns_name, $centrapmanager_strip_domain);
    }

    if ($centrapmanager_log_debug >= 1) {
        logit("Trap received from $tempvar[0]: $tempvar[5]", "DD");
    }

    if ($centrapmanager_log_debug >= 2) {
        logit("0:		hostname", "DD");
        logit("1:		ip address", "DD");
        logit("2:		uptime", "DD");
        logit("3:		trapname / OID", "DD");
        logit("4:		ip address from trap agent", "DD");
        logit("5:		trap community string", "DD");
        logit("6:		enterprise", "DD");
        logit("7:		securityEngineID        (not use)", "DD");
        logit("8:		securityName            (not use)", "DD");
        logit("9:		contextEngineID         (not use)", "DD");
        logit("10:		contextName             (not)", "DD");
        logit("0+:		passed variables", "DD");	

        #print out all standard variables
        for (my $i=0;$i <= $#var;$i++) {
            logit("Value $i: $var[$i]", "DD");
        }

        logit("Agent dns name: $agent_dns_name", "DD");

        #print out all enterprise specific variables
        for (my $i=0;$i <= $#entvar;$i++) {
            logit("Ent Value $i (\$" . ($i+1) . "): $entvarname[$i]=$entvar[$i]", "DD");
        }
    }

    # Generate hash of trap and detect duplicates
    if ($centrapmanager_duplicate_trap_window) {
        my $md5 = Digest::MD5->new;
        # All variables except for uptime.
        $md5->add($var[0],$var[1].$var[3].$var[4].$var[5].$var[6].$var[7].$var[8].$var[9].$var[10]."@entvar");
        
        my $trap_digest = $md5->hexdigest;

        if ($centrapmanager_log_debug >= 2) {
            logit("Trap digest: $trap_digest", "DD");
        }

        if ($duplicate_traps{$trap_digest}) {
            # Duplicate trap detected.  Skipping trap...
            return -1;
        }

        $duplicate_traps{$trap_digest} = time();
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
    
    # Check to see if OID passed from snmptrapd is fully numeric.  If not, try to translate
    if (! ($temp =~ /^(\.\d+)+$/))  {
        # Not numeric
        # Try to convert to numerical
        if ($centrapmanager_log_debug >= 2) {
            logit("Symbolic trap variable name detected ($temp).  Will attempt to translate to a numerical OID", "DD");
        }
        if ($centrapmanager_net_snmp_perl_enable == 1) {
            my $temp3 = SNMP::translateObj("$temp",0);
            if (defined ($temp3) ) {
                if ($centrapmanager_log_debug >= 2) {
                    logit("  Translated to $temp3\n", "DD");
                }
                $temp = $temp3;
            } else {
                # Could not translate default to numeric
                if ($centrapmanager_log_debug >= 2) {
                    logit("  Could not translate - will leave as-is", "DD");
                }
            }
        } else {
            if ($centrapmanager_log_debug >= 2) {
                logit("  Could not translate - Net-SNMP Perl module not enabled - will leave as-is", "DD");
            }
        }
    }
  return $temp;
}

# Strip domain name from hostname
sub strip_domain_name {
    my $name = shift;
    my $mode = shift;

    # If mode = 1, strip off all domain names leaving only the host
    if ($mode == 1 && !($name =~ /^\d+\.\d+\.\d+\.\d+$/)) {
        if ($name =~ /\./) { # Contain a . ?
            $name =~ /^([^\.]+?)\./;
            $name = $1;
        }
    } elsif ($mode == 2 && !($name =~ /^\d+\.\d+\.\d+\.\d+$/)) { # If mode = 2, strip off the domains as listed in strip_domain_list in .ini file 
        if (@centrapmanager_strip_domain_list) {
            foreach my $strip_domain_list_temp (@centrapmanager_strip_domain_list) {
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