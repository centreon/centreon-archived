package database::mysql::mode::myisamkeycachehitrate;

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
                                  "lookback"                => { name => 'lookback', },
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
    
    $self->{sql}->query(query => q{SHOW /*!50000 global */ STATUS WHERE Variable_name IN ('Key_read_requests', 'Key_reads')});
    my $new_datas = {Key_read_requests => undef, Key_reads => undef};
    my $result = $self->{sql}->fetchall_arrayref();
    foreach my $row (@{$result}) {
        $new_datas->{$$row[0]} = $$row[1];
    }
    foreach (keys %$new_datas) {
        if (!defined($new_datas->{$_})) {
            $self->{output}->add_option_msg(short_msg => "Cannot get '$_' variable.");
            $self->{output}->option_exit();
        }
    }

    $self->{statefile_cache}->read(statefile => 'mysql_' . $self->{mode} . '_' . $self->{sql}->get_unique_id4save());
    my $old_timestamp = $self->{statefile_cache}->get(name => 'last_timestamp');
    $new_datas->{last_timestamp} = time();

    my $old_read_request = $self->{statefile_cache}->get(name => 'Key_read_requests');
    my $old_read = $self->{statefile_cache}->get(name => 'Key_reads');
    if (defined($old_read_request) && defined($old_read) &&
        $new_datas->{Key_read_requests} >= $old_read_request &&
        $new_datas->{Key_reads} >= $old_read) {
        
        
        my %prcts = ();
        my $total_read_requests = $new_datas->{Key_read_requests} - $old_read_request;
        my $total_read_disk = $new_datas->{Key_reads} - $old_read;
        $prcts{keycache_hitrate_now} = ($total_read_requests == 0) ? 100 : ($total_read_requests - $total_read_disk) * 100 / $total_read_requests;
        $prcts{keycache_hitrate} = ($new_datas->{Key_read_requests} == 0) ? 100 : ($new_datas->{Key_read_requests} - $new_datas->{Key_reads}) * 100 / $new_datas->{Key_read_requests};
        
        my $exit_code = $self->{perfdata}->threshold_check(value => $prcts{'keycache_hitrate' . ((defined($self->{option_results}->{lookback})) ? '_now' : '' )}, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(severity => $exit_code,
                                    short_msg => sprintf("myisam keycache hitrate at %.2f%%", $prcts{'keycache_hitrate' . ((defined($self->{option_results}->{lookback})) ? '' : '_now')})
                                    );
        $self->{output}->perfdata_add(label => 'keycache_hitrate' . ((defined($self->{option_results}->{lookback})) ? '' : '_now'), unit => '%',
                                      value => sprintf("%.2f", $prcts{'keycache_hitrate' . ((defined($self->{option_results}->{lookback})) ? '_now' : '')}),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                      min => 0);
        $self->{output}->perfdata_add(label => 'keycache_hitrate' . ((defined($self->{option_results}->{lookback})) ? '_now' : ''), unit => '%',
                                      value => sprintf("%.2f", $prcts{'keycache_hitrate' . ((defined($self->{option_results}->{lookback})) ? '_now' : '')}),
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

Check hitrate in the Myisam Key Cache.

=over 8

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=item B<--lookback>

Threshold isn't on the percent calculated from the difference ('keycache_hitrate_now').

=back

=cut
