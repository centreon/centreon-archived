package centreon::plugins::script_snmp;

use strict;
use warnings;
use centreon::plugins::snmp;
use centreon::plugins::output;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;
    # $options{options} = options object
    # $options{output} = output object

    $self->{version} = '1.0';
    %{$self->{modes}} = ();
    $self->{default} = undef;

    $self->{options} = $options{options};
    $self->{output} = $options{output};
    $self->{options}->add_help(package => $options{package}, sections => 'PLUGIN DESCRIPTION');

    return $self;
}

sub init {
    my ($self, %options) = @_;
    # $options{mode} = string mode
    # $options{help} = string help

    # Load output
    $self->{options}->add_help(package => 'centreon::plugins::output', sections => 'OUTPUT OPTIONS');

    # SNMP
    $self->{snmp} = centreon::plugins::snmp->new(options => $self->{options}, output => $self->{output});
    
    # Load mode
    (my $file = $self->{modes}{$options{mode}} . ".pm") =~ s{::}{/}g;
    require $file;
    $self->{mode} = $self->{modes}{$options{mode}}->new(options => $self->{options}, output => $self->{output}, mode => $options{mode});

    if (defined($options{help})) {
        $self->{options}->add_help(package => $self->{modes}{$options{mode}}, sections => 'MODE');
        $self->{options}->display_help();
        $self->{output}->option_exit();
    }
    if (defined($options{version})) {
        $self->{mode}->version();
        $self->{output}->option_exit(nolabel => 1);
    }
    
    $self->{options}->parse_options();
    $self->{option_results} = $self->{options}->get_options();

    $self->{snmp}->check_options(option_results => $self->{option_results});
    $self->{mode}->check_options(option_results => $self->{option_results}, default => $self->{default});
}

sub run {
    my $self = shift;

    if ($self->{output}->is_disco_format()) {
        $self->{mode}->disco_format();
        $self->{output}->display_disco_format();
        $self->{output}->exit(exit_litteral => 'ok');
    }

    $self->{snmp}->connect();
    if ($self->{output}->is_disco_show()) {
        $self->{mode}->disco_show(snmp => $self->{snmp});
        $self->{output}->display_disco_show();
        $self->{output}->exit(exit_litteral => 'ok');
    } else {
        $self->{mode}->run(snmp => $self->{snmp});
    }
}

sub is_mode {
    my ($self, %options) = @_;
    
    # $options->{mode} = mode
    if (!defined($self->{modes}{$options{mode}})) {
        $self->{output}->add_option_msg(short_msg => "mode '" . $options{mode} . "' doesn't exist (use --list option to show available modes).");
        $self->{output}->option_exit();
    }
}

sub version {
    my $self = shift;
    $self->{options}->display_help();
    
    $self->{output}->add_option_msg(long_msg => "Plugin Version: " . $self->{version});
    $self->{output}->option_exit(nolabel => 1);
}

sub list {
    my $self = shift;
    $self->{options}->display_help();
    
    $self->{output}->add_option_msg(long_msg => "Modes Available:");
    foreach (keys %{$self->{modes}}) {
        $self->{output}->add_option_msg(long_msg => "   " . $_);
    }
    $self->{output}->option_exit(nolabel => 1);
}

1;

__END__

