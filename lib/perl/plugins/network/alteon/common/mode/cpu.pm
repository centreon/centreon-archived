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

package network::alteon::common::mode::cpu;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                {
                                  "warning:s"               => { name => 'warning', default => '' },
                                  "critical:s"              => { name => 'critical', default => '' },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
    
    ($self->{warn1s}, $self->{warn4s}, $self->{warn64s}) = split /,/, $self->{option_results}->{warning};
    ($self->{crit1s}, $self->{crit4s}, $self->{crit64s}) = split /,/, $self->{option_results}->{critical};
    
    if (($self->{perfdata}->threshold_validate(label => 'warn1s', value => $self->{warn1s})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning (1sec) threshold '" . $self->{warn1s} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warn4s', value => $self->{warn4s})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning (4sec) threshold '" . $self->{warn4s} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warn64s', value => $self->{warn64s})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning (64sec) threshold '" . $self->{warn64s} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit1s', value => $self->{crit1s})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical (1sec) threshold '" . $self->{crit1s} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit4s', value => $self->{crit4s})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical (4sec) threshold '" . $self->{crit4s} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit64s', value => $self->{crit64s})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical (64sec) threshold '" . $self->{crit64s} . "'.");
       $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    my $oid_mpCpuStatsUtil1Second = '.1.3.6.1.4.1.1872.2.5.1.2.2.1.0';
    my $oid_mpCpuStatsUtil4Seconds = '.1.3.6.1.4.1.1872.2.5.1.2.2.2.0';
    my $oid_mpCpuStatsUtil64Seconds = '.1.3.6.1.4.1.1872.2.5.1.2.2.3.0';
    my $result = $self->{snmp}->get_leef(oids => [$oid_mpCpuStatsUtil1Second, $oid_mpCpuStatsUtil4Seconds,
                                                  $oid_mpCpuStatsUtil64Seconds], nothing_quit => 1);
    
    my $cpu1sec = $result->{$oid_mpCpuStatsUtil1Second};
    my $cpu4sec = $result->{$oid_mpCpuStatsUtil4Seconds};
    my $cpu64sec = $result->{$oid_mpCpuStatsUtil64Seconds};
    
    my $exit1 = $self->{perfdata}->threshold_check(value => $cpu1sec, 
                           threshold => [ { label => 'crit1s', 'exit_litteral' => 'critical' }, { label => 'warn1s', exit_litteral => 'warning' } ]);
    my $exit2 = $self->{perfdata}->threshold_check(value => $cpu4sec, 
                           threshold => [ { label => 'crit4s', 'exit_litteral' => 'critical' }, { label => 'warn4s', exit_litteral => 'warning' } ]);
    my $exit3 = $self->{perfdata}->threshold_check(value => $cpu64sec, 
                           threshold => [ { label => 'crit64s', 'exit_litteral' => 'critical' }, { label => 'warn64s', exit_litteral => 'warning' } ]);
    my $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2, $exit3 ]);
    
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("MP CPU Usage: %.2f%% (1sec), %.2f%% (4sec), %.2f%% (64sec)",
                                      $cpu1sec, $cpu4sec, $cpu64sec));
    
    $self->{output}->perfdata_add(label => "cpu_1s",
                                  value => $cpu1sec,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn1s'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit1s'),
                                  min => 0, max => 100);
    $self->{output}->perfdata_add(label => "cpu_4s",
                                  value => $cpu4sec,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn4s'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit4s'),
                                  min => 0, max => 100);
    $self->{output}->perfdata_add(label => "cpu_64s",
                                  value => $cpu64sec,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn64s'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit64s'),
                                  min => 0, max => 100);
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check MP cpu usage (ALTEON-CHEETAH-SWITCH-MIB).

=over 8

=item B<--warning>

Threshold warning in percent (1s,4s,64s).

=item B<--critical>

Threshold critical in percent (1s,4s,64s).

=back

=cut
    