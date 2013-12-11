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

package snmp_standard::mode::numericvalue;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;
use Digest::MD5 qw(md5_hex);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "oid:s"                   => { name => 'oid' },
                                  "oid-type:s"              => { name => 'oid_type', default => 'gauge' },
                                  "counter-per-seconds"     => { name => 'counter_per_seconds' },
                                  "warning:s"               => { name => 'warning' },
                                  "critical:s"              => { name => 'critical' },
                                  "format:s"                => { name => 'format', default => 'current value is %s' },
                                  "format-scale"            => { name => 'format_scale' },
                                  "format-scale-unit:s"     => { name => 'format_scale_unit', default => 'other'},
                                  "perfdata-unit:s"         => { name => 'perfdata_unit', default => ''},
                                  "perfdata-name:s"         => { name => 'perfdata_name', default => 'value'},
                                  "perfdata-max:s"          => { name => 'perfdata_min', default => ''},
                                  "perfdata-max:s"          => { name => 'perfdata_max', default => ''},
                                });
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (!defined($self->{option_results}->{oid})) {
       $self->{output}->add_option_msg(short_msg => "Need to specify an OID.");
       $self->{output}->option_exit(); 
    }
    if ($self->{option_results}->{oid_type} !~ /^gauge|counter$/i) {
       $self->{output}->add_option_msg(short_msg => "Wrong --oid-type argument '" . $self->{option_results}->{oid_type} . "' ('gauge' or 'counter').");
       $self->{output}->option_exit();
    }
    if ($self->{option_results}->{format_scale_unit} !~ /^other|network$/i) {
       $self->{output}->add_option_msg(short_msg => "Wrong --format-scale-unit argument '" . $self->{option_results}->{format_scale_unit} . "' ('other' or 'network').");
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
    
    if ($self->{option_results}->{oid_type} =~ /^counter$/i)  {
        $self->{statefile_cache}->check_options(%options);
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();

    my $result = $self->{snmp}->get_leef(oids => [$self->{option_results}->{oid}], nothing_quit => 1);
    my $value = $result->{$self->{option_results}->{oid}};
    
    if ($self->{option_results}->{oid_type} =~ /^counter$/i)  {
        my $datas = {};

        $self->{statefile_cache}->read(statefile => "snmpstandard_" . $self->{hostname}  . '_' . $self->{mode} . '_' . md5_hex($self->{option_results}->{oid}));
        my $old_timestamp = $self->{statefile_cache}->get(name => 'timestamp');
        my $old_value = $self->{statefile_cache}->get(name => 'value');
        
        $datas->{timestamp} = time();
        $datas->{value} = $value;
        $self->{statefile_cache}->write(data => $datas);
        if (!defined($old_timestamp)) {
            $self->{output}->output_add(severity => 'OK',
                                        short_msg => "Buffer creation...");
            $self->{output}->display();
            $self->{output}->exit();
        }
        
        $value = $value - $old_value;
        if (defined($self->{option_results}->{counter_per_seconds})) {
            my $delta_time = $datas->{timestamp} - $old_timestamp;
            $delta_time = 1 if ($delta_time == 0); # at least 1 sec
            $value = $value / $delta_time;
        }
    }
    
    my $exit = $self->{perfdata}->threshold_check(value => $value, 
                               threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
    if (defined($self->{option_results}->{format_scale})) {
        my ($value_mod, $value_unit) = $self->{perfdata}->change_bytes(value => $value);
        if ($self->{option_results}->{format_scale} =~ /^network$/i) {
            ($value_mod, $value_unit) = $self->{perfdata}->change_bytes(value => $value, network => 1);
        }
        $self->{output}->output_add(severity => $exit,
                                    short_msg => sprintf($self->{option_results}->{format}, $value_mod . $value_unit));
    } else {
        $self->{output}->output_add(severity => $exit,
                                    short_msg => sprintf($self->{option_results}->{format}, $value));
    }

    $self->{output}->perfdata_add(label => $self->{option_results}->{perfdata_name}, unit => $self->{option_results}->{perfdata_unit},
                                  value => $value,
                                  warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                  critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                  min => $self->{option_results}->{perfdata_min}, max => $self->{option_results}->{perfdata_max});

    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check an SNMP numeric value: can be a Counter, Integer, Gauge, TimeTicks.
Use 'stringvalue' mode if you want to check: 
- 'warning' value is 2, 4 and 5.
- 'critical' value is 1.
- 'ok' value is 10.

=over 8

=item B<--oid>

OID value to check (numeric format only).

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=item B<--oid-type>

Type of the OID (Default: 'gauge').
Can be 'counter' also. 'counter' will use a retention file.

=item B<--counter-per-seconds>

Convert counter value on a value per seconds (only with type 'counter'.

=item B<--format>

Output format (Default: 'current value is %s')

=item B<--format-scale>

Scale bytes value. We'll display value in output.

=item B<--format-scale-type>

Could be 'network' (value divide by 1000) or 'other' (divide by 1024) (Default: 'other')

Output format (Default: 'current value is %s')

=item B<--perfdata-unit>

Perfdata unit in perfdata output (Default: '')

=item B<--perfdata-name>

Perfdata name in perfdata output (Default: 'value')

=item B<--perfdata-min>

Minimum value to add in perfdata output (Default: '')

=item B<--perfdata-max>

Maximum value to add in perfdata output (Default: '')

=back

=cut
