################################################################################
# Copyright 2005-2013 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
# For more information : contact@centreon.com
# Authors : Quentin Garnier <qgarnier@merethis.com>
#
####################################################################################

package centreon::plugins::script_custom;

use strict;
use warnings;
use centreon::plugins::misc;

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
                                                'mode:s'          => { name => 'mode_name' },
                                                'dyn-mode:s'      => { name => 'dynmode_name' },
                                                'list-mode'       => { name => 'list_mode' },
                                                'custommode:s'    => { name => 'custommode_name' },
                                                'list-custommode' => { name => 'list_custommode' },
                                                'multiple'        => { name => 'multiple' },
                                                }
                                  );
    $self->{version} = '1.0';
    %{$self->{modes}} = ();
    %{$self->{custom_modes}} = ();
    $self->{default} = undef;
    $self->{customdefault} = {};
    $self->{custommode_current} = undef;
    $self->{custommode_stored} = [];
    
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

    if (defined($options{help}) && !defined($self->{mode_name}) && !defined($self->{dynmode_name})) {
        $self->{options}->display_help();
        $self->{output}->option_exit();
    }
    if (defined($options{version}) && !defined($self->{mode_name})&& !defined($self->{dynmode_name})) {
        $self->version();
    }
    if (defined($self->{list_mode})) {
        $self->list_mode();
    }
    if (defined($self->{list_custommode})) {
        $self->list_custommode();
    }

    # Output HELP
    $self->{options}->add_help(package => 'centreon::plugins::output', sections => 'OUTPUT OPTIONS');

    if (defined($self->{custommode_name}) && $self->{custommode_name} ne '') {
        $self->is_custommode(custommode => $self->{custommode_name});
        centreon::plugins::misc::mymodule_load(output => $self->{output}, module => $self->{custom_modes}{$self->{custommode_name}}, 
                                               error_msg => "Cannot load module --custommode.");
        $self->{custommode_current} = $self->{custom_modes}{$self->{custommode_name}}->new(options => $self->{options}, output => $self->{output}, mode => $self->{custommode_name});
    } else {
        $self->{output}->add_option_msg(short_msg => "Need to specify '--custommode'.");
        $self->{output}->option_exit();
    }
    
    # Load mode
    if (defined($self->{mode_name}) && $self->{mode_name} ne '') {
        $self->is_mode(mode => $self->{mode_name});
        centreon::plugins::misc::mymodule_load(output => $self->{output}, module => $self->{modes}{$self->{mode_name}}, 
                                               error_msg => "Cannot load module --mode.");
        $self->{mode} = $self->{modes}{$self->{mode_name}}->new(options => $self->{options}, output => $self->{output}, mode => $self->{mode_name});
    } elsif (defined($self->{dynmode_name}) && $self->{dynmode_name} ne '') {
        centreon::plugins::misc::mymodule_load(output => $self->{output}, module => $self->{dynmode_name}, 
                                               error_msg => "Cannot load module --dyn-mode.");
        $self->{mode} = $self->{dynmode_name}->new(options => $self->{options}, output => $self->{output}, mode => $self->{mode_name});
    } else {
        $self->{output}->add_option_msg(short_msg => "Need to specify '--mode' or '--dyn-mode' option.");
        $self->{output}->option_exit();
    }

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

    push @{$self->{custommode_stored}}, $self->{custommode_current};
    $self->{custommode_current}->set_options(option_results => $self->{option_results});
    $self->{custommode_current}->set_defaults(default => $self->{customdefault});

    while ($self->{custommode_current}->check_options()) {
        $self->{custommode_current} = $self->{custom_modes}{$self->{custommode_name}}->new(noptions => 1, options => $self->{options}, output => $self->{output}, mode => $self->{custommode_name});
        $self->{custommode_current}->set_options(option_results => $self->{option_results});
        push @{$self->{custommode_stored}}, $self->{custommode_current};
    }
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
        if (defined($self->{multiple})) {
            $self->{mode}->disco_show(custom => $self->{custommode});
        } else {
            $self->{mode}->disco_show(custom => $self->{custommode_stored}[0]);
        }
        $self->{output}->display_disco_show();
        $self->{output}->exit(exit_litteral => 'ok');
    } else {
        if (defined($self->{multiple})) {
            $self->{mode}->run(custom => $self->{custommode_stored});
        } else {
            $self->{mode}->run(custom => $self->{custommode_stored}[0]);
        }
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

sub is_custommode {
    my ($self, %options) = @_;
    
    # $options->{custommode} = mode
    if (!defined($self->{custom_modes}{$options{custommode}})) {
        $self->{output}->add_option_msg(short_msg => "mode '" . $options{custommode} . "' doesn't exist (use --list-custommode option to show available modes).");
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

sub list_custommode {
    my $self = shift;
    $self->{options}->display_help();
    
    $self->{output}->add_option_msg(long_msg => "Custom Modes Available:");
    foreach (keys %{$self->{custom_modes}}) {
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

Choose a mode.

=item B<--list-mode>

List available modes.

=item B<--version>

Display plugin version.

=item B<--dyn-mode>

Specify a mode with the path (separated by '::').

=item B<--custommode>

Choose a custom mode.

=item B<--list-custommode>

List available custom modes.

=item B<--multiple>

Multiple custom mode objects (some mode needs it).

=back

=head1 DESCRIPTION

B<>.

=cut
