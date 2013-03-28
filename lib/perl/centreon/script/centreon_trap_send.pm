package centreon::script::centreon_trap_send;

use strict;
use warnings;
use Net::SNMP;
use centreon::script;

use base qw(centreon::script);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centreon_trap_send",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 1
    );

    bless $self, $class;
    $self->{opt_d} = 'localhost';
    $self->{opt_snmpcommunity} = 'public';
    $self->{opt_snmpversion} = 'snmpv2c';
    $self->{opt_snmpport} = 162;
    $self->{opt_oidtrap} = '.1.3.6.1.4.1.12182.1';
    @{$self->{opt_args}} = ('.1.3.6.1.4.1.12182.2.1:OCTET_STRING:Test "20"', '.1.3.6.1.4.1.12182.2.1:INTEGER:10', '.1.3.6.1.4.1.12182.2.1:COUNTER:100');
    $self->add_options(
        "d=s" => \$self->{opt_d}, "destination=s" => \$self->{opt_d},
        "c=s" => \$self->{opt_snmpcommunity}, "community=s" => \$self->{opt_snmpcommunity},
        "o=s" => \$self->{opt_oidtrap}, "oid=s" => \$self->{opt_oidtrap},
        "snmpport=i" => \$self->{opt_snmpport},
        "snmpversion=s" => \$self->{opt_snmpversion},
        "a=s" => \@{$self->{opt_args}}, "arg=s" => \@{$self->{opt_args}}
    );
    $self->{timestamp} = time();
    return $self;
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    my ($snmp_session, $error) = Net::SNMP->session(-hostname    => $self->{opt_d},
                                                    -community    => $self->{opt_snmpcommunity},
                                                    -port        => $self->{opt_snmpport},
                                                    -version   => $self->{opt_snmpversion});
    if (!defined($snmp_session)) {
        $self->{logger}->writeLogError("UNKNOWN: SNMP Session : $error");
        die("Quit");
    }

    my @oid_value = ();
    foreach (@{$self->{opt_args}}) {
        my ($oid, $type, $value) = split /:/, $_;
        my $result;
        my $ltmp = "\$result = Net::SNMP::$type;";
        eval $ltmp;
        push @oid_value,($oid, $result, $value);
    }
    unshift @oid_value,('1.3.6.1.6.3.1.1.4.1.0', OBJECT_IDENTIFIER, $self->{opt_oidtrap});
    unshift @oid_value,('1.3.6.1.2.1.1.3.0', TIMETICKS, $self->{timestamp});
    $snmp_session->snmpv2_trap(-varbindlist => \@oid_value);
    $snmp_session->close();
}

1;

__END__

=head1 NAME

    sample - Using GetOpt::Long and Pod::Usage

=cut
