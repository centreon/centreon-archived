package centreon::plugins::script;

use strict;
use warnings;
use centreon::plugins::options;
use centreon::plugins::output;
use centreon::plugins::misc;
use FindBin;
use Pod::Usage;

my %handlers = ('DIE' => {});

sub new {
    my $class = shift;
    my $self  = {};
    bless $self, $class;

    $self->{options} = undef;
    $self->{plugin} = undef;
    $self->{help} = undef;

    $self->set_signal_handlers;
    return $self;
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{__DIE__} = \&class_handle_DIE;
    $handlers{DIE}->{$self} = sub { $self->handle_DIE($_[0]) };
}

sub class_handle_DIE {
    my ($msg) = @_;

    foreach (keys %{$handlers{DIE}}) {
        &{$handlers{DIE}->{$_}}($msg);
    }
}

sub handle_DIE {
    my ($self, $msg) = @_;

    $self->{output}->add_option_msg(short_msg => $msg);
    $self->{output}->option_exit();
}

sub get_plugin {
    my $self = shift;
    
    ######
    # Need to load global 'Output' and 'Options'
    ######
    $self->{options} = centreon::plugins::options->new();
    $self->{output} = centreon::plugins::output->new(options => $self->{options});
    $self->{options}->set_output(output => $self->{output});

    $self->{options}->add_options(arguments => {
                                                'plugin:s' => { name => 'plugin' }, 
                                                'help' => { name => 'help' },
                                                'version' => { name => 'version' },
                                                'runas:s' => { name => 'runas' },
                                                'environment:s%' => { name => 'environment' },
                                                } );

    $self->{options}->parse_options();

    $self->{plugin} = $self->{options}->get_option(argument => 'plugin' );
    $self->{help} = $self->{options}->get_option(argument => 'help' );
    $self->{version} = $self->{options}->get_option(argument => 'version' );
    $self->{runas} = $self->{options}->get_option(argument => 'runas' );
    $self->{environment} = $self->{options}->get_option(argument => 'environment' );

    $self->{output}->mode(name => $self->{mode});
    $self->{output}->plugin(name => $self->{plugin});
    $self->{output}->check_options(option_results => $self->{options}->get_options());

    $self->{options}->clean();
}

sub display_local_help {
    my $self = shift;

    my $stdout;
    if ($self->{help}) {
        local *STDOUT;
        open STDOUT, '>', \$stdout;
        pod2usage(-exitval => "NOEXIT", -input => $FindBin::Bin . "/" . $FindBin::Script);
    }
    
    $self->{output}->add_option_msg(long_msg => $stdout) if (defined($stdout));
}

sub check_relaunch {
    my $self = shift;
    my $need_restart = 0;
    my $cmd = $FindBin::Bin . "/" . $FindBin::Script;
    my @args = ();
    
    if (defined($self->{environment})) {
        foreach (keys %{$self->{environment}}) {
            if ($_ ne '' && (!defined($ENV{$_}) || $ENV{$_} ne $self->{environment}->{$_})) {
                $ENV{$_} = $self->{environment}->{$_};
                $need_restart = 1;
            }
        }
    }
    
    if (defined($self->{runas}) && $self->{runas} ne '') {
        # Check if it's already me and user exist ;)
        my ($name, $passwd, $uid) = getpwnam($self->{runas});
        if (!defined($uid)) {
            $self->{output}->add_option_msg(short_msg => "Runas user '" . $self->{runas} . "' not exist.");
            $self->{output}->option_exit();
        }
        if ($uid != $>) {
            if ($> == 0) {
                unshift @args, "-s", "/bin/bash", "-l", $self->{runas}, "-c", join(" ", $cmd, "--plugin=" . $self->{plugin}, @ARGV);
                $cmd = "su";
            } else {
                unshift @args, "-S", "-u", $self->{runas}, $cmd, "--plugin=" . $self->{plugin}, @ARGV;
                $cmd = "sudo";
            }
            $need_restart = 1;
        }
    }

    if ($need_restart == 1) {
        if (scalar(@args) <= 0) {
            unshift @args, @ARGV, "--plugin=" . $self->{plugin}
        }

        my ($lerror, $stdout, $exit_code) = centreon::plugins::misc::backtick(
                                                 command => $cmd,
                                                 arguments => [@args],
                                                 timeout => 30,
                                                 wait_exit => 1
                                                 );
        if ($exit_code <= -1000) {
            if ($exit_code == -1000) {
                $self->{output}->output_add(severity => 'UNKNOWN', 
                                            short_msg => $stdout);
            }
            $self->{output}->display();
            $self->{output}->exit();
        }
        print $stdout;
        # We put unknown
        if (!($exit_code >= 0 && $exit_code <= 4)) {
            exit 3;
        }
        exit $exit_code;
    }
}

sub run {
    my $self = shift;

    $self->get_plugin();

    if (defined($self->{help}) && !defined($self->{plugin})) {
        $self->display_local_help();
        $self->{output}->option_exit();
    }
    if (!defined($self->{plugin}) || $self->{plugin} eq '') {
        $self->{output}->add_option_msg(short_msg => "Need to specify '--plugin' option.");
        $self->{output}->option_exit();
    }

    $self->check_relaunch();
    
    centreon::plugins::misc::mymodule_load(output => $self->{output}, module => $self->{plugin}, 
                                           error_msg => "Cannot load module --plugin.");
    my $plugin = $self->{plugin}->new(options => $self->{options}, output => $self->{output});
    $plugin->init(help => $self->{help},
                  version => $self->{version});
    $plugin->run();
}

1;

__END__

=head1 NAME

Class global script.

=cut
