
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
    $self->add_options(
        "f=s" => \$self->{opt_f}, "file" => \$self->{opt_f},
        "m=s" => \$self->{opt_m}, "man=s" => \$self->{opt_m}
    );
    return $self;
}

#########################################
## TEST IF OID ALREADY EXISTS IN DATABASE
#
sub existsInDB {
    my $self = shift;
    my ($oid, $name) = @_;
    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT `traps_id` FROM `traps` WHERE `traps_oid` = " . $self->{centreon_dbc}->query($oid) . " AND `traps_name` = " . self->{centreon_dbc}->quote($name) . " LIMIT 1");
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
		$self->{logger}->writeLogError("Cannot open configuration file : $self->{opt_f}");
		exit(1);
    }
    my $last_oid = "";
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
		    }
		} elsif ($_ =~/^FORMAT\ (.*)/ && $last_oid ne "") {
		    my ($status, $sth) = $self->{centreon_dbc}->query("UPDATE `traps` set `traps_args` = '$1' WHERE `traps_oid` = " . $self->{centreon_dbc}->quote($last_oid));
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
		    }
		}
    }
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