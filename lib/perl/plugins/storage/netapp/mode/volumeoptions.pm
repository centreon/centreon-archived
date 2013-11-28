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

package storage::netapp::mode::volumeoptions;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my $oid_volName = '.1.3.6.1.4.1.789.1.5.8.1.2';
my $oid_volOptions = '.1.3.6.1.4.1.789.1.5.8.1.7';

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                {
                                  "warn"            => { name => 'warn' },
                                  "crit"            => { name => 'crit' },
                                  "name:s"          => { name => 'name' },
                                  "regexp"          => { name => 'use_regexp' },
                                  "option:s"        => { name => 'option' },
                                });
    $self->{volume_id_selected} = [];
    $self->{status} = 'OK';
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
    
    if (defined($self->{option_results}->{warn})) {
        $self->{status} = 'WARNING';
    }
    if (defined($self->{option_results}->{crit})) {
        $self->{status} = 'CRITICAL';
    }
}

sub manage_selection {
    my ($self, %options) = @_;

    $self->{result_names} = $self->{snmp}->get_table(oid => $oid_volName, nothing_quit => 1);
    foreach my $oid ($self->{snmp}->oid_lex_sort(keys %{$self->{result_names}})) {
        next if ($oid !~ /\.([0-9]+)$/);
        my $instance = $1;
        
        # Get all without a name
        if (!defined($self->{option_results}->{name})) {
            push @{$self->{volume_id_selected}}, $instance; 
            next;
        }
        
        if (!defined($self->{option_results}->{use_regexp}) && $self->{result_names}->{$oid} eq $self->{option_results}->{name}) {
            push @{$self->{volume_id_selected}}, $instance; 
        }
        if (defined($self->{option_results}->{use_regexp}) && $self->{result_names}->{$oid} =~ /$self->{option_results}->{name}/) {
            push @{$self->{volume_id_selected}}, $instance;
        }
    }

    if (scalar(@{$self->{volume_id_selected}}) <= 0) {
        $self->{output}->add_option_msg(short_msg => "No volume found for name '" . $self->{option_results}->{name} . "'.");
        $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    $self->{snmp}->load(oids => [$oid_volOptions], instances => $self->{volume_id_selected});
    my $result = $self->{snmp}->get_leef();
    
    if (!defined($self->{option_results}->{name}) || defined($self->{option_results}->{use_regexp})) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => 'All volume options are ok.');
    }
    
    my $failed = 0;
    foreach my $instance (sort @{$self->{volume_id_selected}}) {
        my $name = $self->{result_names}->{$oid_volName . '.' . $instance};
        my $option = $result->{$oid_volOptions . '.' . $instance};
       
        $self->{output}->output_add(long_msg => sprintf("Volume '%s' options: %s", $name, $option));

        my $status;
        if (defined($self->{option_results}->{option}) && $option !~ /$self->{option_results}->{option}/) {
            $status = $self->{status};
            $failed++;
        }
        
        # Can be 'ok' if we don't set a threshold option '--warn', '--crit'
        if (defined($status)) {
            $self->{output}->output_add(severity => $status,
                                        short_msg => sprintf("Volume '%s' pattern '%s' is not matching", $name, $self->{option_results}->{option}));
        } elsif (defined($self->{option_results}->{name}) && !defined($self->{option_results}->{use_regexp})) {
             $self->{output}->output_add(severity => $status,
                                         short_msg => sprintf("Volume '%s' option is ok", $name));
        }
    }
    
    $self->{output}->perfdata_add(label => 'failed',
                                  value => $failed,
                                  min => 0);
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check options from volumes.

=over 8

=item B<--warn>

Return Warning (need '--option' also).

=item B<--critical>

Return Critical (need '--option' also).

=item B<--name>

Set the volume name.

=item B<--regexp>

Allows to use regexp to filter volume name (with option --name).

=item B<--option>

Options to check (Example: if 'nosnap=off' not maching, returns Warning or Critical for the volume).

=back

=cut
    