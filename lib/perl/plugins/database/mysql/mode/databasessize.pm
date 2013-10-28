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
                                  "filter:s"                => { name => 'filter', },
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
    
    if (!($self->{sql}->is_version_minimum(version => '5'))) {
        $self->{output}->add_option_msg(short_msg => "MySQL version '" . $self->{sql}->{version} . "' is not supported.");
        $self->{output}->option_exit();
    } 
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => "All databases are ok.");
    foreach my $row (@$result) {
        next if (defined($self->{option_results}->{filter}) && 
                 $$row[0] !~ /$self->{option_results}->{filter}/);
    
        my $exit_code = $self->{perfdata}->threshold_check(value => $$row[1], threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        
        my ($value, $value_unit) = $self->{perfdata}->change_bytes(value => $$row[1]);
        $self->{output}->output_add(long_msg => sprintf("DB '" . $$row[0] . "' size: %.3f%s", $value, $value_unit));
        if (!$self->{output}->is_status(value => $exit_code, compare => 'ok', litteral => 1)) {
            $self->{output}->output_add(severity => $exit_code,
                                        short_msg => sprintf("DB '" . $$row[0] . "' size: %.3f%s", $value, $value_unit));
        }
        $self->{output}->perfdata_add(label => $$row[0] . '_size',
                                      value => $$row[1],
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0);
    }
    
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

=item B<--filter>

Filter database to checks.

=back

=cut
