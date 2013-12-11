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

package network::cisco::asa::mode::sessions;

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
                                  "warning-average:s"       => { name => 'warning_average', default => '' },
                                  "critical-average:s"      => { name => 'critical_average', default => '' },
                                  "warning-current:s"       => { name => 'warning_current' },
                                  "critical-current:s"      => { name => 'critical_current' },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
    
    ($self->{warn_avg1m}, $self->{warn_avg5m}) = split /,/, $self->{option_results}->{warning_average};
    ($self->{crit_avg1m}, $self->{crit_avg5m}) = split /,/, $self->{option_results}->{critical_average};
    
    if (($self->{perfdata}->threshold_validate(label => 'warn_avg1m', value => $self->{warn_avg1m})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning average (1min) threshold '" . $self->{warn_avg1m} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warn_avg5m', value => $self->{warn_avg5m})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning average (5min) threshold '" . $self->{warn_avg5m} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit_avg1m', value => $self->{crit_avg1m})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical average (1min) threshold '" . $self->{crit_avg1m} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'crit_avg5m', value => $self->{crit_avg5m})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical average (5min) threshold '" . $self->{crit_avg5m} . "'.");
       $self->{output}->option_exit();
    }

    if (($self->{perfdata}->threshold_validate(label => 'warning_current', value => $self->{option_results}->{warning_current})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning current threshold '" . $self->{option_results}->{warning_current} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical_current', value => $self->{option_results}->{critical_current})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical current threshold '" . $self->{option_results}->{critical_current} . "'.");
       $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    my $oid_cufwConnGlobalNumActive = '.1.3.6.1.4.1.9.9.491.1.1.1.6.0';
    my $oid_cufwConnGlobalConnSetupRate1 = '.1.3.6.1.4.1.9.9.491.1.1.1.10.0';
    my $oid_cufwConnGlobalConnSetupRate5 = '.1.3.6.1.4.1.9.9.491.1.1.1.11.0';
    my $result = $self->{snmp}->get_leef(oids => [$oid_cufwConnGlobalNumActive, $oid_cufwConnGlobalConnSetupRate1, 
                                                  $oid_cufwConnGlobalConnSetupRate5], nothing_quit => 1);
    
    my $exit1 = $self->{perfdata}->threshold_check(value => $result->{$oid_cufwConnGlobalConnSetupRate1}, 
                                        threshold => [ { label => 'crit_avg1m', 'exit_litteral' => 'critical' }, { label => 'warn_avg1m', exit_litteral => 'warning' } ]);
    my $exit2 = $self->{perfdata}->threshold_check(value => $result->{$oid_cufwConnGlobalConnSetupRate5}, 
                                        threshold => [ { label => 'crit_avg5m', 'exit_litteral' => 'critical' }, { label => 'warn_avg5m', exit_litteral => 'warning' } ]);
    my $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2 ]);
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("Average Connections per seconds: %d (last 1min), %d (last 5min)",
                                                     $result->{$oid_cufwConnGlobalConnSetupRate1}, $result->{$oid_cufwConnGlobalConnSetupRate5}));
    
    $exit = $self->{perfdata}->threshold_check(value => $result->{$oid_cufwConnGlobalNumActive}, 
                                        threshold => [ { label => 'critical_current', 'exit_litteral' => 'critical' }, { label => 'warning_current', exit_litteral => 'warning' } ]);
    $self->{output}->output_add(severity => $exit,
                                short_msg => sprintf("Current active connections: %d",
                                                     $result->{$oid_cufwConnGlobalNumActive}));                                              
                                                     
    $self->{output}->perfdata_add(label => "connections_1m", unit => 'con/s',
                                  value => $result->{$oid_cufwConnGlobalConnSetupRate1},
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn_avg1m'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit_avg1m'),
                                  min => 0);
    $self->{output}->perfdata_add(label => "connections_5m", unit => 'con/s',
                                  value => $result->{$oid_cufwConnGlobalConnSetupRate1},
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warn_avg1m'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'crit_avg1m'),
                                  min => 0);
    $self->{output}->perfdata_add(label => "connections_current",
                                  value => $result->{$oid_cufwConnGlobalNumActive},
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning_current'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical_current'),
                                  min => 0);
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check current/average connections on Cisco ASA (CISCO-UNIFIED-FIREWALL-MIB).

=over 8

=item B<--warning-average>

Threshold warning: averaged number of connections which the firewall establishing per second (1min,5min).

=item B<--critical-average>

Threshold critical: averaged number of connections which the firewall establishing per second (1min,5min).

=item B<--warning-current>

Threshold warning: number of connections which are currently active.

=item B<--critical-current>

Threshold critical: number of connections which are currently active.

=back

=cut
    