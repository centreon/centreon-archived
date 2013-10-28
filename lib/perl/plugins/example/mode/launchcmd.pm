package plugin::example::mode::launchcmd;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::misc;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "cmd:s"     => { name => 'cmd' },
                                  "timeout:s" => { name => 'timeout', default => 30 }
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (!defined($self->{option_results}->{cmd})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify a command.");
       $self->{output}->option_exit(); 
    }
}

sub run {
    my ($self, %options) = @_;

    my ($lerror, $stdout, $exit_code) = centreon::plugins::misc::backtick(
                                                 command => $self->{option_results}->{cmd},
                                                 timeout => $self->{option_results}->{timeout},
                                                 wait_exit => 1
                                                 );
    $stdout =~ s/\r//g;
    if ($exit_code <= -1000) {
        if ($exit_code == -1000) {
            $self->{output}->output_add(severity => 'UNKNOWN', 
                                        short_msg => $stdout);
        }
        $self->{output}->display();
        $self->{output}->exit();
    }
    if ($exit_code != 0) {
        $stdout =~ s/\n/ - /g;
        $self->{output}->output_add(severity => 'UNKNOWN', 
                                    short_msg => "Command error: $stdout");
        $self->{output}->display();
        $self->{output}->exit();
    }
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => 'Command executed with no errors.');
    $self->{output}->output_add(long_msg => $stdout);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Launch a local command. Use --verbose to see the command output.

=over 8

=item B<--cmd>

Command to execute.

=item B<--timeout>

Timeout in seconds for the command (Default: 30).

=back

=cut
