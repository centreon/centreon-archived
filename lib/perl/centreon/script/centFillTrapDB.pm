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

package centreon::script::centFillTrapDB;

use strict;
use warnings;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centFillTrapDB",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
    );

    bless $self, $class;
    
    $self->{no_description} = 0;
    $self->{no_variables} = 0;
    $self->{no_format_summary} = 0;
    $self->{no_format_desc} = 0;
    $self->{format} = 0;
    $self->{format_desc} = 0;
    $self->{no_desc_wildcard} = 0;
    $self->{no_severity} = 0;
    $self->{severity} = 'Normal';
    
    # Set this to 1 to have the --TYPE string prepended to the --SUMMARY string.
    # Set to 0 to disable
    $self->{prepend_type} = 1;
    $self->{net_snmp_perl} = 0;
    $self->{total_translations} = 0;
    $self->{successful_translations} = 0;
    $self->{failed_translations} = 0;
    
    $self->add_options(
        "f=s" => \$self->{opt_f}, "file=s" => \$self->{opt_f},
        "m=s" => \$self->{opt_m}, "man=s" => \$self->{opt_m}
    );
    
    return $self;
}

sub check_snmptranslate_version {
    my $self = shift;
    $self->{snmptranslate_use_On} = 1;
    
    if (open SNMPTRANSLATE, "snmptranslate -V 2>&1|") {
        my $snmptranslatever = <SNMPTRANSLATE>;
        close SNMPTRANSLATE;

        chomp ($snmptranslatever);

        print "snmptranslate version: " . $snmptranslatever. "\n";

        if ($snmptranslatever =~ /UCD/i || $snmptranslatever =~ /NET-SNMP version: 5.0.1/i) {
            $self->{snmptranslate_use_On} = 0;
            $self->{logger}->writeLogDebug("snmptranslate is either UCD-SNMP, or NET-SNMP v5.0.1, so do not use the -On switch.  Version found: $snmptranslatever");
        }
    }
}

#########################################
## TEST IF OID ALREADY EXISTS IN DATABASE
#
sub existsInDB {
    my $self = shift;
    my ($oid, $name) = @_;
    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `traps_id` FROM `traps` WHERE `traps_oid` = " . $self->{centreon_dbc}->quote($oid) . " AND `traps_name` = " . $self->{centreon_dbc}->quote($name) . " LIMIT 1");
    if ($status == -1) {
        return 0;
    }
    if (defined($sth->fetchrow_array())) {
		return 1;
    }
    return 0;
}

#####################################
## RETURN ENUM FROM STRING FOR STATUS
#
sub getStatus($$) {
    my ($val, $name) = @_;
    if ($val =~ /up/i) {
        return 0;
    } elsif ($val =~ /warning|degraded|minor/i) {
        return 1;
    } elsif ($val =~ /critical|major|failure|error|down/i) {
        return 2;
    }else {
        if ($name =~ /normal|up/i || $name =~ /on$/i) {
            return 0;
        } elsif ($name =~ /warning|degraded|minor/i) {
            return 1;
        } elsif ($name =~ /critical|major|fail|error|down|bad/i | $name =~ /off|low$/i) {
            return 2;
        }
    }
    return 3;
}

################
## MAIN FUNCTION
#
sub main {
    my $self = shift;
    my $manuf = $self->{opt_m};
    
    if (!open(FILE, $self->{opt_f})) {
		$self->{logger}->writeLogError("Cannot get mib file : $self->{opt_f}");
		exit(1);
    }
    
    # From snmpconvertmib
    # Copyright 2002-2013 Alex Burger
    # alex_b@users.sourceforge.net
    
    $self->check_snmptranslate_version();
    my @mibfile;
    while (<FILE>) {
        chomp;			# remove <cr> at end of line
        s/\015//;			# Remove any DOS carriage returns
        push(@mibfile, $_);		# add to each line to @trapconf array
    }

    my $currentline = 0;
    # A mib file can contain multiple BEGIN definitions.  This finds the first one
    # to make sure we have at least one definition.
    # Determine name of MIB file
    my $mib_name = '';
    while ($currentline <= $#mibfile) {
        my $line = $mibfile[$currentline];

        # Sometimes DEFINITIONS ::= BEGIN will appear on the line following the mib name.
        # Look for DEFINITIONS ::= BEGIN with nothing (white space allowed) around it and a previous line with 
        # only a single word with whitespace around it.
        if ($currentline > 0 && $line =~ /^\s*DEFINITIONS\s*::=\s*BEGIN\s*$/ && $mibfile[$currentline-1] =~ /^\s*(\S+)\s*$/) {
            # We should have found the mib name
            $mib_name = $1;
            $self->{logger}->writeLogInfo("Split line DEFINITIONS ::= BEGIN found ($1).");
            $mib_name =~ s/\s+//g;
            last;
        } elsif ($line =~ /(.*)DEFINITIONS\s*::=\s*BEGIN/) {
            $mib_name = $1;
            $mib_name =~ s/\s+//g;
            last;
        }
        $currentline++;
    }
    $self->{logger}->writeLogInfo("mib name: $mib_name");
    if ($mib_name eq '') {
        $self->{logger}->writeLogError("Could not find DEFINITIONS ::= BEGIN statement in MIB file!");
        exit (1);
    }
    
    while ($currentline <= $#mibfile) {
        my $line = $mibfile[$currentline];

        # Sometimes DEFINITIONS ::= BEGIN will appear on the line following the mib name.
        # Look for DEFINITIONS ::= BEGIN with nothing (white space allowed) around it and a previous line with 
        # only a single word with whitespace around it.
        if ($currentline > 0 && $line =~ /^\s*DEFINITIONS\s*::=\s*BEGIN\s*$/ && $mibfile[$currentline-1] =~ /^\s*(\S+)\s*$/) {
            # We should have found the mib name
            print "\n\nSplit line DEFINITIONS ::= BEGIN found ($1).\n";

            $mib_name = $1;
            $mib_name =~ s/\s+//g;
            print "Processing MIB:         $mib_name\n";

            $currentline++; # Increment to the next line
            next;
        } elsif ($line =~ /(.*)DEFINITIONS\s*::=\s*BEGIN/) {
            $mib_name = $1;
            $mib_name =~ s/\s+//g;
            print "\n\nProcessing MIB:         $mib_name\n";

            $currentline++; # Increment to the next line
            next;
        }

        # TRAP-TYPE (V1) / NOTIFICATION-TYPE (V2)
        #
        # eg: 'mngmtAgentTrap-23003 TRAP-TYPE';
        # eg: 'ciscoSystemClockChanged NOTIFICATION-TYPE';
        if ($line =~ /(.*)\s*TRAP-TYPE.*/ || 
            $line =~ /(.*)\s*(?<!--)NOTIFICATION-TYPE.*/) {
            my $trapname = $1;

            my $trapversion;
            if ( $line =~ /TRAP-TYPE/ ) {
                $trapversion = 'TRAP';
            } else {
                $trapversion = 'NOTIFICATION';
            }

            # Make sure it doesn't start with a --.  If it does, it's a comment line..  Skip it
            if ($line =~/.*--.*TRAP-TYPE/ || $line =~/.*--.*NOTIFICATION-TYPE/) {
                # Comment line

                $currentline++; # Increment to the next line
                $line = $mibfile[$currentline]; # Get next line
                next;
            }
            my $enterprisefound = 0;

            my @variables = ();

            print "#\n";

            # Sometimes the TRAP-TYPE / NOTIFICATION-TYPE will appear on the line following the trap name
            # Look for xxx-TYPE with nothing (white space allowed) around it and a previous line with only a single word
            # with whitespace around it.
            if ( ($currentline > 0 && $line =~ /^\s*TRAP-TYPE\s*$/ && $mibfile[$currentline-1] =~ /^\s*(\S+)\s*$/) ||
            ($currentline > 0 && $line =~ /^\s*NOTIFICATION-TYPE\s*$/ && $mibfile[$currentline-1] =~ /^\s*(\S+)\s*$/) ) {
                # We should have found the trap name
                $trapname = $1;
                print "Split line TRAP-TYPE / NOTIFICATION-TYPE found ($1).\n";
            } elsif ( $line =~ /^\s+TRAP-TYPE.*/ || $line =~ /^\s+NOTIFICATION-TYPE.*/  || $line =~ /^.*,.*NOTIFICATION-TYPE.*/ ) {
                # If the TRAP-TYPE / NOTIFICATION-TYPE line starts with white space, it's probably a import line, so ignore
                print "skipping a TRAP-TYPE / NOTIFICATION-TYPE line - probably an import line.\n";
                $currentline++; # Increment to the next line
                $line = $mibfile[$currentline]; # Get next line
                next;
            }

            # Remove beginning and trailing white space
            $trapname =~ /\s*([A-Za-z0-9_-]+)\s*/;
            $trapname = $1;

            print "Line: $currentline\n";
            if ($trapversion eq 'TRAP') {
                print "TRAP-TYPE: $1\n";		# If trapsummary blank, use trapsummary line for FORMAT and EXEC
            } else {
                print "NOTIFICATION-TYPE: $1\n";	# If trapsummary blank, use trapsummary line for FORMAT and EXEC
            }

            $currentline++; # Increment to the next line
            my $line3 = $mibfile[$currentline];

            my $end_of_definition = 0;

            my $traptype = "";
            my $trapsummary = "";
            my @description = ();
            my $trap_severity = $self->{severity};
            my $enterprise;
            my @arguments;
            my $formatexec;

            while ( ($currentline <= $#mibfile) && !($line3 =~ /\s+END\s+/) && !($line3 =~ /(.*)\s+TRAP-TYPE.*/ )
                    && !($line3 =~ /(.*)\s+NOTIFICATION-TYPE.*/) && ($end_of_definition == 0) ) {
                # Keep going through the file until the next TRAP-TYPE / NOTIFICATION-TYPE or the end of the mib file
                # is reached, or the end of the section (between BEGIN and END)

                # Look for DESCRIPTION and anything after (including newline with /s)
                # and capture that anything in $1

                # If line starts with ENTERPRISE, pull it out
                # Only applies to SNMPv1 TRAPs
                # (SNMPv2 NOTIFICATIONS have the enterprise in the ::= line)

                $traptype = "";
                $trapsummary = "";
                @description = ();
                $trap_severity = $self->{severity};

                if ($line3 =~ /ENTERPRISE\s+(.*)/) {
                    $enterprise = $1;
                    $enterprisefound =1;
                }

                if ( ($line3 =~ /VARIABLES(.*)/s) || ($line3 =~ /OBJECTS(.*)/s) ) {
                    # If there is more text after the word VARIABLES or OBJECTS, assume it's the start of
                    # the variable list
                    my $templine = "";
                    if ($1 ne "") {
                        $templine = $templine . $1;
                        $templine =~ s/--.*//; # Remove any trailing comments
                    }

                    if ($templine =~ /\}/) { # Contains a }, so we're done
                        # DONE!
                    } else {
                        $currentline++; # Increment to the next line
                        my $line4 = $mibfile[$currentline];
                        $line4 =~ s/--.*//; # Remove any trailing comments
                        my $keepdigging = 1;
                        while (($currentline <= $#mibfile) && ($keepdigging == 1)) {
                            $templine = $templine . $line4;
                            if ($line4 =~ /\}/)	{ # Contains a }, so we're done
                                $keepdigging = 0;
                            } else {
                                $currentline++; # Increment to the next line
                                $line4 = $mibfile[$currentline];
                                $line4 =~ s/--.*//; # Remove any trailing comments
                            }
                        }
                    }
                    $templine =~ s/\s//g;	# Remove any white space
                    $templine =~ /\{(.*)\}/; # Remove brackets
                    @variables = split /\,/, $1;
                    print "Variables: @variables\n";
                }

                if ($line3 =~ /DESCRIPTION(.*)/s) {
                    my $temp1 = 0;

                    # Start of DESCRIPTION

                    #print "SDESC\n";

                    # If there is more text after the word DESCRIPTION, assume it's the start of
                    # the description.
                    if ($1 ne "") {
                        # Pull out text and remove beginning and trailing white space
                        if ($1 =~ /\s*(.*)\s*/) {
                            # Remove any quotes
                            $_ = $1;
                            s(\")()g;
                            # "
                            push (@description, "$_\n");
                        }
                    }

                    $currentline++; # Increment to the next line
                    my $line4 = $mibfile[$currentline];

                    # Assume the rest is the description up until a ::= or end of the file
                    while (! ($line4 =~ /::=/)) {
                        # If next line is a --#TYPE, pull out the information and place in $traptype
                        if ($line4 =~ /--#TYPE(.*)/) {
                            # Pull out text and remove beginning and trailing white space and quotes
                            if ($line4 =~ /\s*--#TYPE\s*(.*)\s*/) {
                                # Remove any quotes
                                $_ = $1;
                                s(\")()g;
                                # "

                                #print ("2\n");
                                $traptype = $_;
                                #print "Type: $traptype \n";
                            }

                            # Increment to next line and continue with the loop
                            $currentline++; # Increment to the next line
                            $line4 = $mibfile[$currentline];
                            next;
                        }

                        # If next line is a --#SUMMARY, pull out the information and place in $summary
                        if ($line4 =~ /--#SUMMARY(.*)/) {
                            # Pull out text and remove beginning and trailing white space and quotes
                            if ($line4 =~ /\s*--#SUMMARY\s*(.*)\s*/) {
                                # Remove any quotes
                                $_ = $1;
                                s(\")()g;
                                # "

                                #print ("2\n");
                                $trapsummary .= $_;
                                #print "Summary: $trapsummary \n";
                            }

                            # Increment to next line and continue with the loop
                            $currentline++; # Increment to the next line
                            $line4 = $mibfile[$currentline];
                            next;
                        }
                        
                        # If next line is a --#ARGUMENTS, pull out the information and place in $arguments
                        if ($line4 =~ /--#ARGUMENTS\s*{(.*)}/) {
                            @arguments = split /,/, $1;

                            for(my $i=0;$i <= $#arguments;$i++) {
                                # Most ARGUMENTS lines have %n where n is a number starting 
                                # at 0, but some MIBS have an ARGUMENTS line that have $1, $2,
                                # etc and start at 1.  These need to have the $ removed and 
                                # the number downshifted so the FORMAT will be generated 
                                # properly.
                                if ($arguments[$i] =~ /^\s*\$\d+/) {
                                    $arguments[$i] =~ s/^\s*\$(\d+)/$1/;
                                    $arguments[$i]--;
                                }
                                #print "argument $i: $arguments[$i]\n";
                            }

                            #for(my $i=0;$i <= $#arguments;$i++)
                            #{
                            #print "argument $i: $arguments[$i]\n";
                            #}

                            # Increment to next line and continue with the loop
                            $currentline++; # Increment to the next line
                            $line4 = $mibfile[$currentline];
                            next;
                        }

                        # If next line is a --#SEVERITY, pull out the information and place in $trap_severity
                        if ($line4 =~ /--#SEVERITY\s+(.*)/ && ! ($line4 =~ /--#SEVERITYMAP/)) {
                            # Pull out text and remove beginning and trailing white space and quotes
                            if ($line4 =~ /\s*--#SEVERITY\s+(.*)\s*/) {
                                # Remove any quotes
                                $_ = $1;
                                s(\")()g;
                                # "

                                #print ("2\n");
                                if ($self->{no_severity} == 0) {
                                    $trap_severity = $_;
                                }
                                #print "Severity: $trap_severity \n";
                            }
                            # Increment to next line and continue with the loop
                            $currentline++; # Increment to the next line
                            $line4 = $mibfile[$currentline];
                            next;
                        }
                        # If next line starts with a --#, ignore it and continue with the loop
                        # (we already got the SUMMARY line above)
                        if ($line4 =~ /--#/) {
                            $currentline++; # Increment to the next line
                            $line4 = $mibfile[$currentline];
                            next;
                        }

                        # If we did not find text after the word DESCRIPTION, then the NEXT
                        # line must be the first line of description.

                        # Remove beginning and trailing white space
                        $line4 =~ (/\s*(.*)\s*/);
                        if ($1 ne "") {
                            # Remove any quotes
                            $_ = $1;
                            s(\")()g;
                            # "

                            push (@description, "$_\n");
                            #print "c:$_\n";
                        }

                        $currentline++; # Increment to the next line
                        $line4 = $mibfile[$currentline];
                    }
                    #print "EDESC\n";

                    if ($line4 =~ /::=/) {
                        $end_of_definition = 1;		# Move on to the next one

                        if ($enterprisefound == 0) {
                            # $line4 should now contain ::= line
                            # # Pull out enterprise from { }
                            # # Would only apply to SNMPv2 NOTIFICATIONS
                            # #print "Line4: $line4\n";
                            $line4 =~ /{(.*)\s\d.*/;

                            #print "\$1=$1\n";
                            $enterprisefound =1;

                            # Remove any spaces
                            $_ = $1;
                            s( )()g;
                            $enterprise = $_;
                            print "Enterprise: $enterprise\n";
                        }
                    }
                }
                $currentline++; # Increment to the next line
                $line3 = $mibfile[$currentline];
            }

            # Combine Trap type and summary together to make new summary
            if ($traptype ne "" && $self->{prepend_type} == 1) {
                $trapsummary = $traptype . ": " . $trapsummary;
            }

            my $trap_lookup;
            if ($mib_name eq '') {
                $trap_lookup = $trapname;
            } else {
                $trap_lookup = "$mib_name\:\:$trapname";
            }
            print "Looking up via snmptranslate: $trap_lookup\n";

            my $trapoid;
            if ($self->{snmptranslate_use_On} == 1) {
                $trapoid = `snmptranslate -IR -Ts -On $trap_lookup`;
            } else {
                $trapoid = `snmptranslate -IR -Ts $trap_lookup`;
            }

            chomp $trapoid;
            if ($trapoid ne "") {
                print OUTPUTFILE "#\n#\n#\n";
                print OUTPUTFILE "EVENT $trapname $trapoid \"Status Events\" $trap_severity\n";

                # Loop through trapsummary and replace the %s and %d etc with %1 to %n

                #$j = $#arguments; # j is last element number
                #print "j is $j\n";

                # Change the %s or %d etc into $1 etc (starts at $1)
                $_ = $trapsummary;
                for (my $j=0; $j<= $#arguments; $j++) {
                    my $variable = ($arguments[$j])+1;
                    s(%[a-zA-Z])(\$$variable);
                }

                #print "new summary: $_\n";

                $trapsummary = $_;

                my $descriptionline1 = '';

                # Build description line for FORMAT / EXEC
                if ($self->{format_desc} == 0) { # First line of description
                    $descriptionline1 = $description[0];
                    chomp ($descriptionline1);
                } else {                 # n sentence(s) of description
                    # Build single line copy of description
                    my $description_temp;
                    foreach my $a (@description) {
                        my $b = $a;
                        chomp($b);
                        $description_temp = $description_temp . $b . " ";
                    }
                    chop $description_temp;

                    # Split up based on sentences
                    my @description_temp2 = split /\./, $description_temp;

                    # Remove white space around each sentence and add a trailing .
                    for (my $i=0 ; $i <= $#description_temp2; $i++) {
                        $description_temp2[$i] =~ /\s*(.*)\s*/;
                        $description_temp2[$i] = $1 . ".";
                    }

                    # Build description line based on the number of sentences requested.
                    for (my $i=1 ; $i <= $self->{format_desc}; $i++) {
                        if ($description_temp2[$i-1] ne '') {
                            $descriptionline1 = $descriptionline1 . $description_temp2[$i-1] . " " ;
                        }
                    }
                    chop $descriptionline1;	# Remove last space
                }

                if ($descriptionline1 ne "") {
                    if ($descriptionline1 =~ /%[a-zA-Z]/) {
                        # Sometimes the variables are in the first line of the description
                        # Change the %s or %d etc into $1 etc (starts at $1)
                        # There is no list of variables, so just put them in order starting at 1 and
                        # going up to 20
                        $_ = $descriptionline1;
                        for (my $j=1; $j<= 20; $j++) {
                            s(%[a-zA-Z])(\$$j);
                        }
                        $descriptionline1 = $_;
                        #$descriptionlinehadvariables = 1;
                    } else {
                        if ($self->{no_desc_wildcard} == 0) {
                            $descriptionline1 = "$descriptionline1 \$*";
                        }
                    }
                }

                $formatexec = '';

                if ($self->{format} == 0) {         # --#SUMMARY or description
                    if ($trapsummary ne '' && $self->{no_format_summary} == 0) {
                        $formatexec = $trapsummary;
                    } elsif ($descriptionline1 ne '' && $self->{no_format_desc} == 0) {
                        $formatexec = $descriptionline1;
                    }
                } elsif ($self->{format} == 1) {    # description or --#SUMMARY
                    if ($descriptionline1 ne '' && $self->{no_format_desc} == 0) {
                        $formatexec = $descriptionline1;
                    } elsif ($trapsummary ne '' && $self->{no_format_summary} == 0) {
                        $formatexec = $trapsummary;
                    }
                } elsif ($self->{format} == 2) {    # --#SUMMARY and description
                    if ($trapsummary ne '' && $self->{no_format_summary} == 0) {
                        $formatexec = $trapsummary;
                    }
                    if ($descriptionline1 ne '' && $self->{no_format_desc} == 0) {
                        if ($formatexec =~ /\.$/) { # If it already ends in a .
                            $formatexec = $formatexec . " " . $descriptionline1;
                        } else {
                            $formatexec = $formatexec . ". " . $descriptionline1;
                        }
                    }
                } elsif ($self->{format} == 3) {    # description and --#SUMMARY
                    if ($descriptionline1 ne '' && $self->{no_format_desc} == 0) {
                        $formatexec = $descriptionline1;
                    }
                    if ($trapsummary ne '' && $self->{no_format_summary} == 0) {
                        $formatexec = $formatexec . " " . $trapsummary;
                    }
                } elsif ($self->{format} == 4) {    # -- trap name and variables
                    $formatexec = "$trapname - ";
                    for (my $i=1; $i < $#variables+2; $i++) {
                        $formatexec .= "$variables[$i-1]:\$$i ";
                    }
                }

                if ($formatexec ne '') {
                    print OUTPUTFILE "FORMAT $formatexec\n";
                } else {
                    print OUTPUTFILE "FORMAT \$*\n";
                }

                if ($self->{no_description} == 0) {
                    print OUTPUTFILE "SDESC\n";
                    #print OUTPUTFILE "$descriptionline1\n";
                    for (my $i=0; $i <= $#description; $i++) {
                        print OUTPUTFILE "$description[$i]";
                    }

                    # If net_snmp_perl is enabled, lookup each variable
                    if (@variables && $self->{no_variables} == 0 && $self->{net_snmp_perl} == 1) {
                        print OUTPUTFILE "Variables:\n";
                        for (my $i=0; $i <= $#variables; $i++) {
                            printf OUTPUTFILE "%3d: %s\n",$i+1,$variables[$i];
                            printf OUTPUTFILE "     Syntax=\"" . $SNMP::MIB{$variables[$i]}{type} . "\"\n";
                            if (uc $SNMP::MIB{$variables[$i]}{type} =~ /INTEGER/) {
                                my $b = $SNMP::MIB{$variables[$i]}{enums};
                                my %hash = %$b;
                                my $i = 1;

                                # Create a new copy of the hash swapping the key and the value
                                my %temphash = ();
                                while ((my $key, my $value) = each %hash) {
                                    $temphash{$value} = $key;
                                }
                                # Print out the entries in the hash
                                foreach my $c (sort keys %temphash) {
                                    print OUTPUTFILE "       " . $c . ": $temphash{$c}\n";
                                }
                            }
                            if ($SNMP::MIB{$variables[$i]}{description}) {
                                print OUTPUTFILE "     Descr=\"" . $SNMP::MIB{$variables[$i]}{description} . "\"\n";
                            }
                        }
                    } elsif (@variables ne "" && $self->{no_variables} == 0  && $self->{net_snmp_perl} == 0) {
                        print OUTPUTFILE "Variables:\n";
                        for (my $i=0; $i <= $#variables; $i++) {
                            print OUTPUTFILE "  " . ($i+1) . ": " . $variables[$i] . "\n";
                        }
                    }
                    print OUTPUTFILE "EDESC\n";
                }

                $currentline--;
            }

            print "OID: $trapoid\n";

            $self->{total_translations}++;
            if ($trapoid eq '') {
                $self->{failed_translations}++;
            } else {
                $self->{successful_translations}++;
            }

            #print "\@description is ", $#description,"\n";
            #print "going to next trap / notification\n\n";
        }

        $currentline++; # Increment to the next line
    }
    
    print "\n\nDone\n\n";
    print "Total translations:        $self->{total_translations}\n";
    print "Successful translations:   $self->{successful_translations}\n";
    print "Failed translations:       $self->{failed_translations}\n";
    
    exit(1);
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    my $last_oid = "";
    my $nb_inserted = 0;
    my $nb_updated = 0;

	while (<FILE>) {	
		if ($_ =~ /^EVENT\ ([a-zA-Z0-9\_\-]+)\ ([0-9\.]+)\ (\"[A-Za-z\ \_\-]+\")\ ([a-zA-Z]+)/) {
			my ($name,$oid,$type,$val) = ($1, $2, $3, $4);
		    if ($self->existsInDB($oid, $name)) {
				$self->{logger}->writeLogInfo("Trap oid : $name => $oid already exists in database");
				$last_oid = $oid;
		    } else {
				$val = getStatus($val,$name);
				my ($status, $sth) = $self->{centreon_dbc}->query("INSERT INTO `traps` (`traps_name`, `traps_oid`, `traps_status`, `manufacturer_id`, `traps_submit_result_enable`) VALUES (" . $self->{centreon_dbc}->quote($name) . ", " . $self->{centreon_dbc}->quote($oid) . ", " . $self->{centreon_dbc}->quote($val) . ", " . $self->{centreon_dbc}->quote($manuf) . ", '1')");
				$last_oid = $oid;
                        $nb_inserted++;
		    }
		} elsif ($_ =~/^FORMAT\ (.*)/ && $last_oid ne "") {
		    my ($status, $sth) = $self->{centreon_dbc}->query("UPDATE `traps` set `traps_args` = '$1' WHERE `traps_oid` = " . $self->{centreon_dbc}->quote($last_oid));
                    $nb_updated++;
		} elsif ($_ =~ /^SDESC(.*)/ && $last_oid ne "") {	    
		    my $temp_val = $1;
		    my $desc = "";
		    if (! ($temp_val =~ /\s+/)){
				$temp_val =~ s/\"/\\\"/g;
				$temp_val =~ s/\'/\\\'/g;
				$desc .= $temp_val;
		    }
		    my $found = 0;
		    while (!$found) {
				my $line = <FILE>;
				if ($line =~ /^EDESC/) {
				    $found = 1;
				} else {
					$line =~ s/\"/\\\"/g;
					$line =~ s/\'/\\\'/g;
				 	$desc .= $line;
				}
		    }
		    if ($desc ne "") {
				my ($status, $sth) = $self->{centreon_dbc}->query("UPDATE `traps` SET `traps_comments` = '$desc' WHERE `traps_oid` = " .  $self->{centreon_dbc}->quote($last_oid));
                        $nb_updated++;
		    }
		}
    }
    $self->{logger}->writeLogInfo("$nb_inserted entries inserted, $nb_updated entries updated");
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    if (!defined($self->{opt_f}) || !defined($self->{opt_m})) {
        $self->{logger}->writeLogError("Arguments missing.");
        exit(1);
    }
    $self->{centreon_dbc} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                      host => $self->{centreon_config}->{db_host},
                                                      port => $self->{centreon_config}->{db_port},
                                                      user => $self->{centreon_config}->{db_user},
                                                      password => $self->{centreon_config}->{db_passwd},
                                                      force => 0,
                                                      logger => $self->{logger});
    
    $self->main();
    exit(0);
}

1;
