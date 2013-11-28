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

package os::windows::mode::service;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %map_installed_state = (
    1 => 'uninstalled', 
    2 => 'install-pending', 
    3 => 'uninstall-pending', 
    4 => 'installed'
);
my %map_operating_state = (
    1 => 'active',
    2 => 'continue-pending',
    3 => 'pause-pending',
    4 => 'paused'
);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"          => { name => 'warning', },
                                  "critical:s"         => { name => 'critical', },
                                  "service:s@"         => { name => 'service', },
                                  "regexp"             => { name => 'use_regexp', },
                                  "state:s"            => { name => 'state', },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (!defined($self->{option_results}->{service})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify at least one '--service' option.");
       $self->{output}->option_exit();
    }
    
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
    
    my $oid_svSvcEntry = '.1.3.6.1.4.1.77.1.2.3.1';
    my $oid_svSvcName  = '.1.3.6.1.4.1.77.1.2.3.1.1';
    my $oid_svSvcInstalledState  = '.1.3.6.1.4.1.77.1.2.3.1.2';
    my $oid_svSvcOperatingState  = '.1.3.6.1.4.1.77.1.2.3.1.3';
    my $result = $self->{snmp}->get_table(oid => $oid_svSvcEntry);
    
    my %services_match = ();
    $self->{output}->output_add(severity => 'OK',
                                short_msg => 'All service states is ok');
    foreach my $oid ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($oid !~ /^$oid_svSvcName/);
        $oid =~ /^$oid_svSvcName\.([0-9\.]+)$/;
        my $instance = $1;

        my $svc_name = $self->{output}->to_utf8($result->{$oid});
        my $svc_installed_state = $result->{$oid_svSvcInstalledState . '.' . $instance};
        my $svc_operating_state = $result->{$oid_svSvcOperatingState . '.' . $instance};
        
        for (my $i = 0; $i < scalar(@{$self->{option_results}->{service}}); $i++) {
            my $filter = ${$self->{option_results}->{service}}[$i];
            if (defined($self->{option_results}->{use_regexp}) && $svc_name =~ /$filter/) {
                $services_match{$i}{$svc_name}{operating_state} = $svc_operating_state;
                $services_match{$i}{$svc_name}{installed_state} = $svc_installed_state;
            } elsif ($svc_name eq $filter) {
                $services_match{$i}{$svc_name}{operating_state} = $svc_operating_state;
                $services_match{$i}{$svc_name}{installed_state} = $svc_installed_state;
            }
        }
    }
    
    for (my $i = 0; $i < scalar(@{$self->{option_results}->{service}}); $i++) {
        my $numbers = 0;
        my %svc_name_state_wrong = ();
        foreach my $svc_name (keys %{$services_match{$i}}) {
            my $operating_state = $services_match{$i}{$svc_name}{operating_state};
            my $installed_state = $services_match{$i}{$svc_name}{installed_state};
            $self->{output}->output_add(long_msg => sprintf("Service '%s' match (pattern: '%s') [operating state = %s, installed state = %s]", 
                                                            $svc_name, ${$self->{option_results}->{service}}[$i],
                                                            $map_operating_state{$operating_state}, $map_installed_state{$installed_state}));
            if (defined($self->{option_results}->{state}) && $map_operating_state{$operating_state} !~ /$self->{option_results}->{state}/) {
                delete $services_match{$i}{$svc_name};
                $svc_name_state_wrong{$svc_name} = $operating_state;
                next;
            }
            $numbers++;
        }
        
        my $exit = $self->{perfdata}->threshold_check(value => $numbers, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
        $self->{output}->output_add(long_msg => sprintf("Service pattern '%s': service list %s",
                                       ${$self->{option_results}->{service}}[$i],
                                       join(', ', keys %{$services_match{$i}})));
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1)) {
            if (scalar(keys %svc_name_state_wrong) > 0) {
                $self->{output}->output_add(severity => $exit,
                                            short_msg => sprintf("Service pattern '%s' problem: %s [following services match but has the wrong state]",
                                                                 ${$self->{option_results}->{service}}[$i], join(', ', keys %svc_name_state_wrong)));
            } else {
                $self->{output}->output_add(severity => $exit,
                                            short_msg => sprintf("Service problem '%s'", ${$self->{option_results}->{service}}[$i]));
            }
        }
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check Windows Services in SNMP

=over 8

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=item B<--service>

Services to check. (can set multiple times)

=item B<--regexp>

Allows to use regexp to filter services.

=item B<--state>

Service state. (Regexp allowed)

=back

=cut
