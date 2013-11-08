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

package network::bluecoat::mode::clientrequests;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning-errors:s"      => { name => 'warning_errors' },
                                  "critical-errors:s"     => { name => 'critical_errors' },
                                  "warning-misses:s"      => { name => 'warning_misses' },
                                  "critical-misses:s"     => { name => 'critical_misses' },
                                });
    $self->{statefile_value} = centreon::plugins::statefile->new(%options);
                                
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
    
    if (($self->{perfdata}->threshold_validate(label => 'warning_errors', value => $self->{option_results}->{warning_errors})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'errors' threshold '" . $self->{option_results}->{warning_errors} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical_errors', value => $self->{option_results}->{critical_errors})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'errors' threshold '" . $self->{option_results}->{critical_errors} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warning_misses', value => $self->{option_results}->{warning_misses})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'misses' threshold '" . $self->{option_results}->{warning_misses} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical_misses', value => $self->{option_results}->{critical_misses})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'misses' threshold '" . $self->{option_results}->{critical_misses} . "'.");
        $self->{output}->option_exit();
    }
    
    $self->{statefile_value}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();
    
    if ($self->{snmp}->is_snmpv1()) {
        $self->{output}->add_option_msg(short_msg => "Need to use SNMP v2c or v3.");
        $self->{output}->option_exit();
    }

    $self->{statefile_value}->read(statefile => 'bluecoat_' . $self->{hostname}  . '_' . $self->{mode});
    my $result = $self->{snmp}->get_leef(oids => ['.1.3.6.1.4.1.3417.2.11.3.1.1.1.0', 
                                                  '.1.3.6.1.4.1.3417.2.11.3.1.1.2.0',
                                                  '.1.3.6.1.4.1.3417.2.11.3.1.1.3.0',
                                                  '.1.3.6.1.4.1.3417.2.11.3.1.1.4.0',
                                                  '.1.3.6.1.4.1.3417.2.11.3.1.1.5.0'], nothing_quit => 1);

    my $new_datas = {};
    my $old_timestamp = $self->{statefile_value}->get(name => 'last_timestamp');
    my $old_client_http_requests = $self->{statefile_value}->get(name => 'client_http_requests');
    my $old_client_http_hits = $self->{statefile_value}->get(name => 'client_http_hits');
    my $old_client_http_partial_hits = $self->{statefile_value}->get(name => 'client_http_partial_hits');
    my $old_client_http_misses = $self->{statefile_value}->get(name => 'client_http_misses');
    my $old_client_http_errors = $self->{statefile_value}->get(name => 'client_http_errors');

    $new_datas->{last_timestamp} = time();
    $new_datas->{client_http_requests} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.1.0'};
    $new_datas->{client_http_hits} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.2.0'};
    $new_datas->{client_http_partial_hits} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.3.0'};
    $new_datas->{client_http_misses} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.4.0'};
    $new_datas->{client_http_errors} = $result->{'.1.3.6.1.4.1.3417.2.11.3.1.1.5.0'};
    
    $self->{statefile_value}->write(data => $new_datas);
    
    if (!defined($old_timestamp) || !defined($old_client_http_misses)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
        $self->{output}->exit();
    }
        
    if ($new_datas->{client_http_requests} < $old_client_http_requests) {
        # We set 0. Has reboot.
        $old_client_http_requests = 0;
        $old_client_http_hits = 0;
        $old_client_http_partial_hits = 0;
        $old_client_http_misses = 0;
        $old_client_http_errors = 0;
    }
    
    my $delta_http_requests = $new_datas->{client_http_requests} - $old_client_http_requests;
    my $prct_misses = sprintf("%.2f", ($new_datas->{client_http_misses} - $old_client_http_misses) * 100 / $delta_http_requests);
    my $prct_hits = sprintf("%.2f", ($new_datas->{client_http_hits} - $old_client_http_hits) * 100 / $delta_http_requests);
    my $prct_partial_hits = sprintf("%.2f", ($new_datas->{client_http_partial_hits} - $old_client_http_partial_hits) * 100 / $delta_http_requests);
    my $prct_errors = sprintf("%.2f", ($new_datas->{client_http_errors} - $old_client_http_errors) * 100 / $delta_http_requests);
    
    my $exit1 = $self->{perfdata}->threshold_check(value => $prct_errors, threshold => [ { label => 'critical_errors', 'exit_litteral' => 'critical' }, { label => 'warning_errors', exit_litteral => 'warning' } ]);
    my $exit2 = $self->{perfdata}->threshold_check(value => $prct_misses, threshold => [ { label => 'critical_misses', 'exit_litteral' => 'critical' }, { label => 'warning_misses', exit_litteral => 'warning' } ]);
    my $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2 ]);
    
    $self->{output}->output_add(severity => $exit,
                                short_msg => "Client Requests: Hits = $prct_hits%, Partial Hits = $prct_partial_hits%, Misses = $prct_misses%, Errors = $prct_errors%");
    $self->{output}->perfdata_add(label => 'hits', unit => '%',
                                  value => $prct_hits,
                                  min => 0);
    $self->{output}->perfdata_add(label => 'partial_hits', unit => '%',
                                  value => $prct_partial_hits,
                                  min => 0);
    $self->{output}->perfdata_add(label => 'misses', unit => '%',
                                  value => $prct_misses,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning_misses'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical_misses'),
                                  min => 0);
    $self->{output}->perfdata_add(label => 'errors', unit => '%',
                                  value => $prct_errors,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning_errors'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical_errors'),
                                  min => 0);    
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check http client requests (in percent by type: hit, partial, misses, errors)

=over 8

=item B<--warning-errors>

Threshold warning of client http errors in percent.

=item B<--critical-errors>

Threshold critical of client http errors in percent.

=item B<--warning-misses>

Threshold warning of client http misses in percent.

=item B<--critical-misses>

Threshold critial of client http misses in percent.

=back

=cut
