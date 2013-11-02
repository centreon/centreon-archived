package example::mode::getvalue;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "oid:s"                   => { name => 'oid' },
                                  "warning:s"               => { name => 'warning' },
                                  "critical:s"              => { name => 'critical' },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (!defined($self->{option_results}->{oid})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify an OID.");
       $self->{output}->option_exit(); 
    }
    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{option_results}->{warning} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{option_results}->{critical} . "'.");
       $self->{output}->option_exit();
    }
    
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();

    my $result = $self->{snmp}->get_leef(oids => [$self->{option_results}->{oid}], nothing_quit => 1);
    my $value = $result->{$self->{option_results}->{oid}};
    
    my $exit = $self->{perfdata}->threshold_check(value => $value, 
                               threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("SNMP Value is %s.", $value));    

    $self->{output}->perfdata_add(label => 'value', unit => undef,
                                  value => $value,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                  min => undef, max => undef);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check an SNMP Integer value.

=over 8

=item B<--oid>

Check OID Integer value.

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=back

=cut
