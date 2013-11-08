package database::mysql::mode::slowqueries;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;

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
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);

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
    
    $self->{statefile_cache}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{sql} = sqlmode object
    $self->{sql} = $options{sql};

    $self->{sql}->connect();
    
    if (!($self->{sql}->is_version_minimum(version => '5'))) {
        $self->{output}->add_option_msg(short_msg => "MySQL version '" . $self->{sql}->{version} . "' is not supported (need version >= '5.x').");
        $self->{output}->option_exit();
    }
    
    $self->{sql}->query(query => q{SHOW /*!50000 global */ STATUS LIKE 'Slow_queries'});
    my ($name, $result) = $self->{sql}->fetchrow_array();
    if (!defined($result)) {
        $self->{output}->add_option_msg(short_msg => "Cannot get slow queries.");
        $self->{output}->option_exit();
    }    
    
    
    my $new_datas = {};
    $self->{statefile_cache}->read(statefile => 'mysql_' . $self->{mode} . '_' . $self->{sql}->get_unique_id4save());
    my $old_timestamp = $self->{statefile_cache}->get(name => 'last_timestamp');
    $new_datas->{last_timestamp} = time();
    
    if (defined($old_timestamp) && $new_datas->{last_timestamp} - $old_timestamp == 0) {
        $self->{output}->add_option_msg(short_msg => "Need at least one second between two checks.");
        $self->{output}->option_exit();
    }

    $new_datas->{$name} = $result;
    my $old_val = $self->{statefile_cache}->get(name => $name);
    if (defined($old_val) && $result >= $old_val) {
        my $value = sprintf("%.2f", ($result - $old_val) / ($new_datas->{last_timestamp} - $old_timestamp));
    
        my $exit_code = $self->{perfdata}->threshold_check(value => $value, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(severity => $exit_code,
                                    short_msg => sprintf("%d slow queries in %d seconds (%.2f/sec)", 
                                        ($result - $old_val), ($new_datas->{last_timestamp} - $old_timestamp), $value)
                                    );
        $self->{output}->perfdata_add(label => 'slow_queries_rate',
                                      value => $value,
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0);
    }
    
    $self->{statefile_cache}->write(data => $new_datas); 
    if (!defined($old_timestamp)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
    }

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check average number of queries detected as "slow" (per seconds).

=over 8

=item B<--warning>

Threshold warning in queries per seconds.

=item B<--critical>

Threshold critical in queries per seconds.

=back

=cut
