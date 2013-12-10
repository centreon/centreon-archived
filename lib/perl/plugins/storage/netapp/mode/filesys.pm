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

package storage::netapp::mode::filesys;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my $oid_dfFileSys = '.1.3.6.1.4.1.789.1.5.4.1.2';
my $oid_dfType = '.1.3.6.1.4.1.789.1.5.4.1.23';
my $oid_dfKBytesTotal = '.1.3.6.1.4.1.789.1.5.4.1.3';
my $oid_dfKBytesUsed = '.1.3.6.1.4.1.789.1.5.4.1.4';
my $oid_dfKBytesAvail = '.1.3.6.1.4.1.789.1.5.4.1.5';
my $oid_df64TotalKBytes = '.1.3.6.1.4.1.789.1.5.4.1.29';
my $oid_df64UsedKBytes = '.1.3.6.1.4.1.789.1.5.4.1.30';
my $oid_df64AvailKBytes = '.1.3.6.1.4.1.789.1.5.4.1.31';
my $oid_dfDedupeSavedPercent = '.1.3.6.1.4.1.789.1.5.4.1.40';
my $oid_dfCompressSavedPercent = '.1.3.6.1.4.1.789.1.5.4.1.38';

my %map_types = (
    1 => 'traditionalVolume',
    2 => 'flexibleVolume',
    3 => 'aggregate',
    4 => 'stripedAggregate',
    5 => 'stripedVolume'
);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                {
                                  "warning:s"             => { name => 'warning' },
                                  "critical:s"            => { name => 'critical' },
                                  "units:s"               => { name => 'units', default => '%' },
                                  "free"                  => { name => 'free' },
                                  "name:s"                => { name => 'name' },
                                  "regexp"                => { name => 'use_regexp' },
                                  "type:s"                => { name => 'type' },
                                });
    $self->{filesys_id_selected} = [];

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

sub manage_selection {
    my ($self, %options) = @_;

    $self->{result_names} = $self->{snmp}->get_table(oid => $oid_dfFileSys, nothing_quit => 1);
    $self->{result_types} = $self->{snmp}->get_table(oid => $oid_dfType, nothing_quit => 1);
    foreach my $oid ($self->{snmp}->oid_lex_sort(keys %{$self->{result_names}})) {
        next if ($oid !~ /\.([0-9]+)$/);
        my $instance = $1;
        my $type = $map_types{$self->{result_types}->{$oid_dfType . '.' . $instance}};
        
        # Get all without a name
        if (!defined($self->{option_results}->{name})) {
            push @{$self->{filesys_id_selected}}, $instance; 
            next;
        }
        
        if (!defined($self->{option_results}->{use_regexp}) && $self->{result_names}->{$oid} eq $self->{option_results}->{name}) {
            next if (defined($self->{option_results}->{type}) && $type !~ /$self->{option_results}->{type}/i);
            push @{$self->{filesys_id_selected}}, $instance; 
        }
        if (defined($self->{option_results}->{use_regexp}) && $self->{result_names}->{$oid} =~ /$self->{option_results}->{name}/) {
            next if (defined($self->{option_results}->{type}) && $type !~ /$self->{option_results}->{type}/i);
            push @{$self->{filesys_id_selected}}, $instance;
        }
    }

    if (scalar(@{$self->{filesys_id_selected}}) <= 0) {
        $self->{output}->add_option_msg(short_msg => "No filesys found for name '" . $self->{option_results}->{name} . "' (can be the 'type' filter also).");
        $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    if (!$self->{snmp}->is_snmpv1()) {
        $self->{snmp}->load(oids => [$oid_df64TotalKBytes, $oid_df64UsedKBytes, $oid_df64AvailKBytes], instances => $self->{filesys_id_selected});
    }
    $self->{snmp}->load(oids => [$oid_dfKBytesTotal, $oid_dfKBytesUsed, $oid_dfKBytesAvail, $oid_dfDedupeSavedPercent, $oid_dfCompressSavedPercent], instances => $self->{filesys_id_selected});
    my $result = $self->{snmp}->get_leef();
    
    if (!defined($self->{option_results}->{name}) || defined($self->{option_results}->{use_regexp})) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => 'All filesys are ok.');
    }
    
    foreach my $instance (sort @{$self->{filesys_id_selected}}) {
        my $name = $self->{result_names}->{$oid_dfFileSys . '.' . $instance};
        my $type = $self->{result_types}->{$oid_dfType . '.' . $instance};
        my ($total_size, $total_used, $total_free) = ($result->{$oid_dfKBytesTotal . '.' . $instance} * 1024, $result->{$oid_dfKBytesUsed . '.' . $instance} * 1024, $result->{$oid_dfKBytesAvail . '.' . $instance} * 1024);
        if (defined($result->{$oid_df64TotalKBytes . '.' . $instance}) && $result->{$oid_df64TotalKBytes . '.' . $instance} != 0) {
            ($total_size, $total_used, $total_free) = ($result->{$oid_df64TotalKBytes . '.' . $instance} * 1024, $result->{$oid_df64UsedKBytes . '.' . $instance} * 1024, $result->{$oid_df64AvailKBytes . '.' . $instance} * 1024);
        }
        
        if ($total_size == 0) {
            $self->{output}->output_add(long_msg => sprintf("Skipping filesys '%s' (total size is 0)", $name));
            if (defined($self->{option_results}->{name}) && !defined($self->{option_results}->{use_regexp})) {
                $self->{output}->output_add(severity => 'UNKNOWN',
                                            short_msg => sprintf("Skipping filesys '%s' (total size is 0)", $name));
            }
            next;
        }
        
        #######
        # Calc
        #######
        my $prct_used = $total_used * 100 / $total_size;
        my $prct_free = 100 - $prct_used;
        my ($exit, $threshold_value);

        $threshold_value = $total_used;
        $threshold_value = $total_free if (defined($self->{option_results}->{free}));
        if ($self->{option_results}->{units} eq '%') {
            $threshold_value = $prct_used;
            $threshold_value = $prct_free if (defined($self->{option_results}->{free}));
        } 
        $exit = $self->{perfdata}->threshold_check(value => $threshold_value, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);

        my ($total_size_value, $total_size_unit) = $self->{perfdata}->change_bytes(value => $total_size);
        my ($total_used_value, $total_used_unit) = $self->{perfdata}->change_bytes(value => $total_used);
        my ($total_free_value, $total_free_unit) = $self->{perfdata}->change_bytes(value => ($total_size - $total_used));
        
        $self->{output}->output_add(long_msg => sprintf("Filesys '%s' Total: %s Used: %s (%.2f%%) Free: %s (%.2f%%) [type: %s]", $name,
                                            $total_size_value . " " . $total_size_unit,
                                            $total_used_value . " " . $total_used_unit, $prct_used,
                                            $total_free_value . " " . $total_free_unit, $prct_free, $map_types{$type}));
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1) || (defined($self->{option_results}->{name}) && !defined($self->{option_results}->{use_regexp}))) {
            $self->{output}->output_add(severity => $exit,
                                        short_msg => sprintf("Filesys '%s' Total: %s Used: %s (%.2f%%) Free: %s (%.2f%%)", $name,
                                            $total_size_value . " " . $total_size_unit,
                                            $total_used_value . " " . $total_used_unit, $prct_used,
                                            $total_free_value . " " . $total_free_unit, $prct_free));
        }
        
        my $label = 'used';
        my $value_perf = $total_used;
        if (defined($self->{option_results}->{free})) {
            $label = 'free';
            $value_perf = $total_free;
        }
        my $extra_label = '';
        $extra_label = '_' . $name if (!defined($self->{option_results}->{name}) || defined($self->{option_results}->{use_regexp}));
        my %total_options = ();
        if ($self->{option_results}->{units} eq '%') {
            $total_options{total} = $total_size;
            $total_options{cast_int} = 1;
        }
        $self->{output}->perfdata_add(label => $label . $extra_label, unit => 'o',
                                      value => $value_perf,
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning', %total_options),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical', %total_options),
                                      min => 0, max => $total_size);
        
        if (defined($result->{$oid_dfDedupeSavedPercent . '.' . $instance})) {
            $self->{output}->perfdata_add(label => 'dedupsaved' . $extra_label, unit => '%',
                                          value => $result->{$oid_dfDedupeSavedPercent . '.' . $instance},
                                          min => 0, max => 100);
        }
        if (defined($result->{$oid_dfCompressSavedPercent . '.' . $instance})) {
            $self->{output}->perfdata_add(label => 'compresssaved' . $extra_label, unit => '%',
                                          value => $result->{$oid_dfCompressSavedPercent . '.' . $instance},
                                          min => 0, max => 100);
        }
    }
    
    $self->{output}->display();
    $self->{output}->exit();
}

1;

__END__

=head1 MODE

Check filesystem usage (volumes and aggregates also).

=over 8

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=item B<--units>

Units of thresholds (Default: '%') ('%', 'B').

=item B<--free>

Thresholds are on free space left.

=item B<--name>

Set the filesystem name.

=item B<--regexp>

Allows to use regexp to filter filesystem name (with option --name).

=item B<--type>

Filter filesystem type (a regexp. Example: 'flexibleVolume|aggregate').

=back

=cut
    