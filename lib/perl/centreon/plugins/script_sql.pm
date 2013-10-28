package centreon::plugins::script_sql;

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;
    # $options{package} = parent package caller
    # $options{options} = options object
    # $options{output} = output object
    $self->{options} = $options{options};
    $self->{output} = $options{output};
    
    $self->{options}->add_options(
                                   arguments => {
                                                'mode:s' => { name => 'mode_name' },
                                                'list-mode' => { name => 'list_mode' },
                                                'sqlmode:s' => { name => 'sqlmode_name', default => 'dbi' },
                                                'list-sqlmode' => { name => 'list_sqlmode' },
                                                }
                                  );
    $self->{version} = '1.0';
    %{$self->{modes}} = ();
    %{$self->{sql_modes}} = ('dbi' => 'centreon::plugins::dbi');
    $self->{default} = undef;
    $self->{sqldefault} = {};
    
    $self->{options}->parse_options();
    $self->{option_results} = $self->{options}->get_options();
    foreach (keys %{$self->{option_results}}) {
         $self->{$_} = $self->{option_results}->{$_};
    }
    $self->{options}->clean();

    $self->{options}->add_help(package => $options{package}, sections => 'PLUGIN DESCRIPTION');
    $self->{options}->add_help(package => __PACKAGE__, sections => 'GLOBAL OPTIONS');

    return $self;
}

sub init {
    my ($self, %options) = @_;
    # $options{version} = string version
    # $options{help} = string help

    if (defined($options{help}) && !defined($self->{mode_name})) {
        $self->{options}->display_help();
        $self->{output}->option_exit();
    }
    if (defined($options{version}) && !defined($self->{mode_name})) {
        $self->version();
    }
    if (defined($self->{list_mode})) {
        $self->list_mode();
    }
    if (defined($self->{list_sqlmode})) {
        $self->list_sqlmode();
    }
    
    if (!defined($self->{mode_name}) || $self->{mode_name} eq '') {
        $self->{output}->add_option_msg(short_msg => "Need to specify '--mode' option.");
        $self->{output}->option_exit();
    }
    $self->is_mode(mode => $self->{mode_name});
    $self->is_sqlmode(sqlmode => $self->{sqlmode_name});

    # Output HELP
    $self->{options}->add_help(package => 'centreon::plugins::output', sections => 'OUTPUT OPTIONS');

    # Load Sql-Mode
    (my $file = $self->{sql_modes}{$self->{sqlmode_name}} . ".pm") =~ s{::}{/}g;
    require $file;
    $self->{sqlmode} = $self->{sql_modes}{$self->{sqlmode_name}}->new(options => $self->{options}, output => $self->{output}, mode => $self->{sqlmode_name});
    
    # Load mode
    ($file = $self->{modes}{$self->{mode_name}} . ".pm") =~ s{::}{/}g;
    require $file;
    $self->{mode} = $self->{modes}{$self->{mode_name}}->new(options => $self->{options}, output => $self->{output}, mode => $self->{mode_name});

    if (defined($options{help})) {
        $self->{options}->add_help(package => $self->{modes}{$self->{mode_name}}, sections => 'MODE');
        $self->{options}->display_help();
        $self->{output}->option_exit();
    }
    if (defined($options{version})) {
        $self->{mode}->version();
        $self->{output}->option_exit(nolabel => 1);
    }
    
    $self->{options}->parse_options();
    $self->{option_results} = $self->{options}->get_options();

    $self->{sqlmode}->check_options(option_results => $self->{option_results}, default => $self->{sqldefault});
    $self->{mode}->check_options(option_results => $self->{option_results}, default => $self->{default});
}

sub run {
    my $self = shift;

    if ($self->{output}->is_disco_format()) {
        $self->{mode}->disco_format();
        $self->{output}->display_disco_format();
        $self->{output}->exit(exit_litteral => 'ok');
    }

    if ($self->{output}->is_disco_show()) {
        $self->{mode}->disco_show(sql => $self->{sqlmode});
        $self->{output}->display_disco_show();
        $self->{output}->exit(exit_litteral => 'ok');
    } else {
        $self->{mode}->run(sql => $self->{sqlmode});
    }
}

sub is_mode {
    my ($self, %options) = @_;
    
    # $options->{mode} = mode
    if (!defined($self->{modes}{$options{mode}})) {
        $self->{output}->add_option_msg(short_msg => "mode '" . $options{mode} . "' doesn't exist (use --list-mode option to show available modes).");
        $self->{output}->option_exit();
    }
}

sub is_sqlmode {
    my ($self, %options) = @_;
    
    # $options->{sqlmode} = mode
    if (!defined($self->{sql_modes}{$options{sqlmode}})) {
        $self->{output}->add_option_msg(short_msg => "mode '" . $options{sqlmode} . "' doesn't exist (use --list-sqlmode option to show available modes).");
        $self->{output}->option_exit();
    }
}


sub version {
    my $self = shift;    
    $self->{output}->add_option_msg(short_msg => "Plugin Version: " . $self->{version});
    $self->{output}->option_exit(nolabel => 1);
}

sub list_mode {
    my $self = shift;
    $self->{options}->display_help();
    
    $self->{output}->add_option_msg(long_msg => "Modes Available:");
    foreach (keys %{$self->{modes}}) {
        $self->{output}->add_option_msg(long_msg => "   " . $_);
    }
    $self->{output}->option_exit(nolabel => 1);
}

sub list_sqlmode {
    my $self = shift;
    $self->{options}->display_help();
    
    $self->{output}->add_option_msg(long_msg => "SQL Modes Available:");
    foreach (keys %{$self->{sql_modes}}) {
        $self->{output}->add_option_msg(long_msg => "   " . $_);
    }
    $self->{output}->option_exit(nolabel => 1);
}

1;

__END__

=head1 NAME

-

=head1 SYNOPSIS

-

=head1 GLOBAL OPTIONS

=over 8

=item B<--mode>

Choose a mode (required).

=item B<--list-mode>

List available modes.

=item B<--sqlmode>

Choose a sql mode (Default: "dbi").

=item B<--list-sqlmode>

List available sql modes.

=back

=head1 DESCRIPTION

B<>.

=cut
