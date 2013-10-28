package database::mysql::mode::databasessize;

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
                                  "warning:s"               => { name => 'warning', },
                                  "critical:s"              => { name => 'critical', },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{warn1} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{critical} . "'.");
       $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{sql} = sqlmode object
    $self->{sql} = $options{sql};

    $self->{sql}->connect();
    $self->{sql}->query(query => 'SELECT table_schema AS NAME, SUM(data_length+index_length) 
            FROM information_schema.tables
            GROUP BY table_schema');
    my $result = $self->{sql}->fetchall_arrayref();
    use Data::Dumper;
    print Data::Dumper::Dumper($result);
    
    
    #    my $exit_code = $self->{perfdata}->threshold_check(value => $milliseconds, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    #    $self->{output}->output_add(severity => $exit_code,
    #                                short_msg => sprintf("Connection established in %.3fs.", $milliseconds / 1000));
    #    $self->{output}->perfdata_add(label => 'connection_time', unit => 'ms',
    #                                  value => $milliseconds,
    #                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
    #                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
    #                                  min => 0);
    #}
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check MySQL databases size.

=over 8

=item B<--warning>

Threshold warning in bytes.

=item B<--critical>

Threshold critical in bytes.

=back

=cut
