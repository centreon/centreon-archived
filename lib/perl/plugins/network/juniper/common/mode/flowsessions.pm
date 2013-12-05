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

package network::juniper::common::mode::flowsessions;

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
                                  "warning:s"               => { name => 'warning', },
                                  "critical:s"              => { name => 'critical', },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{option_results}->{warning} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{option_results}->{critical} . "'.");
        $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    
    my $oid_jnxJsSPUMonitoringSPUIndex = '.1.3.6.1.4.1.2636.3.39.1.12.1.1.1.3';
    my $oid_jnxJsSPUMonitoringCurrentFlowSession = '.1.3.6.1.4.1.2636.3.39.1.12.1.1.1.6';
    my $oid_jnxJsSPUMonitoringMaxFlowSession = '.1.3.6.1.4.1.2636.3.39.1.12.1.1.1.7';
    
    my $result = $self->{snmp}->get_table(oid => $oid_jnxJsSPUMonitoringSPUIndex, nothing_quit => 1);
    $self->{snmp}->load(oids => [$oid_jnxJsSPUMonitoringCurrentFlowSession, $oid_jnxJsSPUMonitoringMaxFlowSession],
                        instances => [keys %$result],
                        instance_regexp => '\.(\d+)$');
    my $result2 = $self->{snmp}->get_leef(nothing_quit => 1);
    
    my $spu_done = 0;
    foreach my $oid (keys %$result) {        
        $oid =~ /\.(\d+)$/;
        my $instance = $1;
        my $flow_total = $result2->{$oid_jnxJsSPUMonitoringMaxFlowSession . '.' . $instance};
        my $flow_used = $result2->{$oid_jnxJsSPUMonitoringCurrentFlowSession . '.' . $instance};
        
        next if ($flow_total == 0);
        my $prct_used = $flow_used * 100 / $flow_total;
    
        $spu_done = 1;
        my $exit_code = $self->{perfdata}->threshold_check(value => $prct_used, 
                                threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(severity => $exit_code,
                                    short_msg => sprintf("SPU '%d': %.2f%% of the flow sessions limit reached (%d of max. %d)", 
                                        $instance, $prct_used, $flow_used, $flow_total));
        $self->{output}->perfdata_add(label => 'sessions_' . $instance,
                                      value => $flow_used,
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning', total => $flow_total),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical', total => $flow_total),
                                      min => 0, max => $flow_total);
    }

    if ($spu_done == 0) {
        $self->{output}->add_option_msg(short_msg => "Cannot check flow sessions usage (no total values).");
        $self->{output}->option_exit();
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check Packet Forwarding Engine sessions usage.

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=back

=cut
