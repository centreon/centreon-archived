package centreon::plugins::script;

use strict;
use warnings;
use centreon::plugins::options;
use centreon::plugins::output;
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
                                                'version' => { name => 'version' } } );

    $self->{options}->parse_options();

    $self->{plugin} = $self->{options}->get_option(argument => 'plugin' );
    $self->{help} = $self->{options}->get_option(argument => 'help' );
    $self->{version} = $self->{options}->get_option(argument => 'version' );

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
    (my $file = $self->{plugin} . ".pm") =~ s{::}{/}g;
    require $file;
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
