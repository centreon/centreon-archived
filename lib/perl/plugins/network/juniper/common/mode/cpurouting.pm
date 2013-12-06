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

package network::juniper::common::mode::cpurouting;

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
    
    my $oid_jnxOperatingDescr = '.1.3.6.1.4.1.2636.3.1.13.1.5';
    my $oid_jnxOperatingCPU = '.1.3.6.1.4.1.2636.3.1.13.1.8';
    my $oid_jnxOperating1MinLoadAvg = '.1.3.6.1.4.1.2636.3.1.13.1.20';
    my $oid_jnxOperating5MinLoadAvg = '.1.3.6.1.4.1.2636.3.1.13.1.21';
    my $oid_jnxOperating15MinLoadAvg = '.1.3.6.1.4.1.2636.3.1.13.1.22';
    
    my $result = $self->{snmp}->get_table(oid => $oid_jnxOperatingDescr, nothing_quit => 1);
    my $routing_engine_find = 0;
    my $oid_routing_engine;
    foreach my $oid (keys %$result) {        
        if ($result->{$oid} =~ /routing/i) {
            $routing_engine_find = 1;
            $oid_routing_engine = $oid;
            last;
        }
    }
    
    if ($routing_engine_find == 0) {
        $self->{output}->add_option_msg(short_msg => "Cannot find operating with 'routing' in description.");
        $self->{output}->option_exit();
    }
    
    $self->{snmp}->load(oids => [$oid_jnxOperatingCPU, $oid_jnxOperating1MinLoadAvg, $oid_jnxOperating5MinLoadAvg, $oid_jnxOperating15MinLoadAvg],
                        instances => [$oid_routing_engine],
                        instance_regexp => "^" . $oid_jnxOperatingDescr . '\.(.+)');
    my $result2 = $self->{snmp}->get_leef();
    
    $oid_routing_engine =~ /^$oid_jnxOperatingDescr\.(.+)/;
    my $instance = $1;
    my $cpu_usage = $result2->{$oid_jnxOperatingCPU . '.' . $instance};
    my $cpu_load1 = $result2->{$oid_jnxOperating1MinLoadAvg . '.' . $instance};
    my $cpu_load5 = $result2->{$oid_jnxOperating5MinLoadAvg . '.' . $instance};
    my $cpu_load15 = $result2->{$oid_jnxOperating15MinLoadAvg . '.' . $instance};
    
    my $exit_code = $self->{perfdata}->threshold_check(value => $cpu_usage, 
                               threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    $self->{output}->output_add(severity => $exit_code,
                                short_msg => sprintf("CPU(s) average usage is: %s%%", $cpu_usage));
    $self->{output}->perfdata_add(label => 'cpu', unit => '%',
                                  value => $cpu_usage,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                  min => 0, max => 100);
    $self->{output}->perfdata_add(label => 'load1',
                                  value => $cpu_load1,
                                  min => 0);
    $self->{output}->perfdata_add(label => 'load5',
                                  value => $cpu_load5,
                                  min => 0);
    $self->{output}->perfdata_add(label => 'load15',
                                  value => $cpu_load15,
                                  min => 0);

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check CPU Usage of routing engine.

=over 8

=item B<--warning>

Threshold warning in percent.

=item B<--critical>

Threshold critical in percent.

=back

=cut
