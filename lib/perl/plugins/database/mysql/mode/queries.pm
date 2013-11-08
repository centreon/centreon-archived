package database::mysql::mode::queries;

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
    $self->{sql}->query(query => q{
        SHOW /*!50000 global */ STATUS WHERE Variable_name IN ('Queries', 'Com_update', 'Com_delete', 'Com_insert', 'Com_truncate', 'Com_select') 
    });
    my $result = $self->{sql}->fetchall_arrayref();
    
    if (!($self->{sql}->is_version_minimum(version => '5.0.76'))) {
        $self->{output}->add_option_msg(short_msg => "MySQL version '" . $self->{sql}->{version} . "' is not supported (need version >= '5.0.76').");
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
    
    foreach my $row (@{$result}) {
        next if ($$row[0] !~ /^(Queries|Com_update|Com_delete|Com_insert|Com_truncate|Com_select)/i);
    
        $new_datas->{$$row[0]} = $$row[1];
        my $old_val = $self->{statefile_cache}->get(name => $$row[0]);
        next if (!defined($old_val) || $$row[1] < $old_val);
        
        my $value = int(($$row[1] - $old_val) / ($new_datas->{last_timestamp} - $old_timestamp));
        if ($$row[0] ne 'Queries') {
            $self->{output}->perfdata_add(label => $$row[0] . '_requests',
                                      value => $value,
                                      min => 0);
            next;
        }
        
        my $exit_code = $self->{perfdata}->threshold_check(value => $value, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(severity => $exit_code,
                                    short_msg => sprintf("Total requests = %d.", $value));
        $self->{output}->perfdata_add(label => 'total_requests',
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

Check average number of queries executed.

=over 8

=item B<--warning>

Threshold warning in bytes.

=item B<--critical>

Threshold critical in bytes.

=back

=cut
