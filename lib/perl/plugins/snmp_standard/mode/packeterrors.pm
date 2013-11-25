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

package snmp_standard::mode::packeterrors;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;
use Digest::MD5 qw(md5_hex);

my @operstatus = ("up", "down", "testing", "unknown", "dormant", "notPresent", "lowerLayerDown");
my %oids_iftable = (
    'ifdesc' => '.1.3.6.1.2.1.2.2.1.2',
    'ifalias' => '.1.3.6.1.2.1.31.1.1.1.18',
    'ifname' => '.1.3.6.1.2.1.31.1.1.1.1'
);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning-in-discard:s"    => { name => 'warning_in_discard' },
                                  "critical-in-discard:s"   => { name => 'critical_in_discard' },
                                  "warning-out-discard:s"   => { name => 'warning_out_discard' },
                                  "critical-out-discard:s"  => { name => 'critical_out_discard' },
                                  "warning-in-error:s"    => { name => 'warning_in_error' },
                                  "critical-in-error:s"   => { name => 'critical_in_error' },
                                  "warning-out-error:s"   => { name => 'warning_out_error' },
                                  "critical-out-error:s"  => { name => 'critical_out_error' },
                                  "reload-cache-time:s"     => { name => 'reload_cache_time' },
                                  "name"                    => { name => 'use_name' },
                                  "interface:s"             => { name => 'interface' },
                                  "skip"                    => { name => 'skip' },
                                  "regexp"                  => { name => 'use_regexp' },
                                  "regexp-isensitive"       => { name => 'use_regexpi' },
                                  "oid-filter:s"            => { name => 'oid_filter', default => 'ifname'},
                                  "oid-display:s"           => { name => 'oid_display', default => 'ifname'},
                                  "display-transform-src:s" => { name => 'display_transform_src' },
                                  "display-transform-dst:s" => { name => 'display_transform_dst' },
                                  "show-cache"              => { name => 'show_cache' },
                                });

    $self->{interface_id_selected} = [];
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);
    $self->{statefile_value} = centreon::plugins::statefile->new(%options);
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    # 'discard' treshold
    if (($self->{perfdata}->threshold_validate(label => 'warning-in-discard', value => $self->{option_results}->{warning_in_discard})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'in discard' threshold '" . $self->{option_results}->{warning_in_discard} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical-in-discard', value => $self->{option_results}->{critical_in_discard})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'in discard' threshold '" . $self->{option_results}->{critical_in_discard} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warning-out-discard', value => $self->{option_results}->{warning_out_discard})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'out discard' threshold '" . $self->{option_results}->{warning_out_disard} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical-out-discard', value => $self->{option_results}->{critical_out_discard})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'out discard' threshold '" . $self->{option_results}->{critical_out_discard} . "'.");
        $self->{output}->option_exit();
    }
    
    # 'errror' treshold
    if (($self->{perfdata}->threshold_validate(label => 'warning-in-error', value => $self->{option_results}->{warning_in_error})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'in error' threshold '" . $self->{option_results}->{warning_in_error} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical-in-error', value => $self->{option_results}->{critical_in_error})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'in error' threshold '" . $self->{option_results}->{critical_in_error} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warning-out-error', value => $self->{option_results}->{warning_out_error})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'out error' threshold '" . $self->{option_results}->{warning_out_disard} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical-out-error', value => $self->{option_results}->{critical_out_error})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'out error' threshold '" . $self->{option_results}->{critical_out_error} . "'.");
        $self->{output}->option_exit();
    }
    
    $self->{option_results}->{oid_filter} = lc($self->{option_results}->{oid_filter});
    if ($self->{option_results}->{oid_filter} !~ /^(ifdesc|ifalias|ifname)$/) {
        $self->{output}->add_option_msg(short_msg => "Unsupported --oid-filter option.");
        $self->{output}->option_exit();
    }
    $self->{option_results}->{oid_display} = lc($self->{option_results}->{oid_display});
    if ($self->{option_results}->{oid_display} !~ /^(ifdesc|ifalias|ifname)$/) {
        $self->{output}->add_option_msg(short_msg => "Unsupported --oid-display option.");
        $self->{output}->option_exit();
    }
    
    $self->{statefile_cache}->check_options(%options);
    $self->{statefile_value}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();

    $self->manage_selection();
    
    my $oid_adminstatus = '.1.3.6.1.2.1.2.2.1.7';
    my $oid_operstatus = '.1.3.6.1.2.1.2.2.1.8';
  
    # 32bits
    my $oid_ifInUcastPkts = '.1.3.6.1.2.1.2.2.1.11';
    my $oid_ifInBroadcastPkts = '.1.3.6.1.2.1.31.1.1.1.3';
    my $oid_ifInMulticastPkts = '.1.3.6.1.2.1.31.1.1.1.2';
    my $oid_ifOutUcastPkts = '.1.3.6.1.2.1.2.2.1.17';
    my $oid_ifOutMulticastPkts = '.1.3.6.1.2.1.31.1.1.1.4';
    my $oid_ifOutBroadcastPkts = '.1.3.6.1.2.1.31.1.1.1.5';
    
    # 64 bits
    my $oid_ifHCInUcastPkts = '.1.3.6.1.2.1.31.1.1.1.7';
    my $oid_ifHCInMulticastPkts = '.1.3.6.1.2.1.31.1.1.1.8';
    my $oid_ifHCInBroadcastPkts = '.1.3.6.1.2.1.31.1.1.1.9';
    my $oid_ifHCOutUcastPkts = '.1.3.6.1.2.1.31.1.1.1.11';
    my $oid_ifHCOutMulticastPkts = '.1.3.6.1.2.1.31.1.1.1.12';
    my $oid_ifHCOutBroadcastPkts = '.1.3.6.1.2.1.31.1.1.1.13';

    # 'discard' 'error' only 32 bits
    my $oid_ifInDiscards = '.1.3.6.1.2.1.2.2.1.13';
    my $oid_ifInErrors = '.1.3.6.1.2.1.2.2.1.14';
    my $oid_ifOutDiscards = '.1.3.6.1.2.1.2.2.1.19';
    my $oid_ifOutErrors = '.1.3.6.1.2.1.2.2.1.20';

    my $new_datas = {};
    $self->{statefile_value}->read(statefile => "snmpstandard_" . $self->{hostname}  . '_' . $self->{mode} . '_' . (defined($self->{option_results}->{interface}) ? md5_hex($self->{option_results}->{interface}) : md5_hex('all')));
    
    foreach (@{$self->{interface_id_selected}}) {
        $self->{snmp}->load(oids => [$oid_adminstatus . "." . $_, $oid_operstatus . "." . $_, 
                                     $oid_ifInUcastPkts . "." . $_, $oid_ifInBroadcastPkts . "." . $_, $oid_ifInMulticastPkts . "." . $_,
                                     $oid_ifOutUcastPkts . "." . $_, $oid_ifOutMulticastPkts . "." . $_, $oid_ifOutBroadcastPkts . "." . $_,
                                     $oid_ifInDiscards . "." . $_, $oid_ifInErrors . "." . $_,
                                     $oid_ifOutDiscards . "." . $_, $oid_ifOutErrors . "." . $_]);
        if (!$self->{snmp}->is_snmpv1()) {
            $self->{snmp}->load(oids => [$oid_ifHCInUcastPkts . "." . $_, $oid_ifHCInMulticastPkts . "." . $_, $oid_ifHCInMulticastPkts . "." . $_,
                                         $oid_ifHCOutUcastPkts . "." . $_, $oid_ifHCOutMulticastPkts . "." . $_, $oid_ifHCOutBroadcastPkts . "." . $_]);
        }
    }

    my $result = $self->{snmp}->get_leef();
    $new_datas->{last_timestamp} = time();
    my $old_timestamp;
    if (!defined($self->{option_results}->{interface}) || defined($self->{option_results}->{use_regexp})) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => 'All interfaces are ok.');
    }

    foreach (sort @{$self->{interface_id_selected}}) {
        my $display_value = $self->get_display_value(id => $_);
        
        if ($operstatus[$result->{$oid_operstatus . "." . $_} - 1] ne "up") {
            if (!defined($self->{option_results}->{skip}) && (!defined($result->{$oid_adminstatus . "." . $_}) || $operstatus[$result->{$oid_adminstatus . "." . $_} - 1] eq 'up') ) {
                $self->{output}->output_add(severity => 'CRITICAL',
                                            short_msg => "Interface '" . $display_value . "' is not ready: " . $operstatus[$result->{$oid_operstatus . "." . $_} - 1]);
            } else {
                $self->{output}->output_add(long_msg => "Skip interface '" . $display_value . "'.");
            }
            next;
        }
        
        #################
        # New values
        #################
        my $old_mode = $self->{statefile_value}->get(name => 'mode_' . $_);
        $new_datas->{'mode_' . $_} = '32';
        $new_datas->{'in_discard_' . $_} = $result->{$oid_ifInDiscards . "." . $_};
        $new_datas->{'in_error_' . $_} = $result->{$oid_ifInErrors . "." . $_};
        $new_datas->{'out_discard_' . $_} = $result->{$oid_ifOutDiscards . "." . $_};
        $new_datas->{'out_error_' . $_} = $result->{$oid_ifOutErrors . "." . $_};
    
        $new_datas->{'in_ucast_' . $_} = $result->{$oid_ifInUcastPkts . "." . $_};
        $new_datas->{'in_bcast_' . $_} = defined($result->{$oid_ifInBroadcastPkts . "." . $_}) ? $result->{$oid_ifInBroadcastPkts . "." . $_} : 0;
        $new_datas->{'in_mcast_' . $_} = defined($result->{$oid_ifInMulticastPkts . "." . $_}) ? $result->{$oid_ifInMulticastPkts . "." . $_} : 0;
        $new_datas->{'out_ucast_' . $_} = $result->{$oid_ifOutUcastPkts . "." . $_};
        $new_datas->{'out_bcast_' . $_} = defined($result->{$oid_ifOutMulticastPkts . "." . $_}) ? $result->{$oid_ifOutMulticastPkts . "." . $_} : 0;
        $new_datas->{'out_mcast_' . $_} = defined($result->{$oid_ifOutBroadcastPkts . "." . $_}) ? $result->{$oid_ifOutBroadcastPkts . "." . $_} : 0;
        
        if (defined($result->{$oid_ifHCInUcastPkts . "." . $_}) && $result->{$oid_ifHCInUcastPkts . "." . $_} ne '' && $result->{$oid_ifHCInUcastPkts . "." . $_} != 0) {
            $new_datas->{'in_ucast_' . $_} = $result->{$oid_ifHCInUcastPkts . "." . $_};
            $new_datas->{'in_mcast_' . $_} = defined($result->{$oid_ifHCInMulticastPkts . "." . $_}) ? $result->{$oid_ifHCInMulticastPkts . "." . $_} : 0;
            $new_datas->{'in_bcast_' . $_} = defined($result->{$oid_ifHCInBroadcastPkts . "." . $_}) ? $result->{$oid_ifHCInBroadcastPkts . "." . $_} : 0;
            $new_datas->{'out_ucast_' . $_} = $result->{$oid_ifHCOutUcastPkts . "." . $_};
            $new_datas->{'out_mcast_' . $_} = defined($result->{$oid_ifHCOutMulticastPkts . "." . $_}) ? $result->{$oid_ifHCOutMulticastPkts . "." . $_} : 0;
            $new_datas->{'out_bcast_' . $_} = defined($result->{$oid_ifHCOutBroadcastPkts . "." . $_}) ? $result->{$oid_ifHCOutBroadcastPkts . "." . $_} : 0;
            $new_datas->{'mode_' . $_} = '64';
        }
        
        # We change mode. need to recreate a buffer
        if (!defined($old_mode) || $new_datas->{'mode_' . $_} ne $old_mode) {
            next;
        }
        
        #################
        # Old values
        #################
        my @getting = ('in_ucast', 'in_bcast', 'in_mcast', 'out_ucast', 'out_bcast', 'out_mcast',
                       'in_discard', 'in_error', 'out_discard', 'out_error');
        my $old_datas = {};
        $old_timestamp = $self->{statefile_value}->get(name => 'last_timestamp');
        foreach my $key (@getting) {
            $old_datas->{$key} = $self->{statefile_value}->get(name => $key . '_' . $_);
            if (!defined($old_datas->{$key}) || $new_datas->{$key . '_' . $_} < $old_datas->{$key}) {
                # We set 0. Has reboot.
                $old_datas->{$key} = 0;
            }
        }
        
        if (!defined($old_timestamp)) {
            next;
        }
        my $time_delta = $new_datas->{last_timestamp} - $old_timestamp;
        if ($time_delta <= 0) {
            # At least one second. two fast calls ;)
            $time_delta = 1;
        }
        
        ############

        my $total_in_packets = ($new_datas->{'in_ucast_' . $_} - $old_datas->{in_ucast}) + ($new_datas->{'in_bcast_' . $_} - $old_datas->{in_bcast}) + ($new_datas->{'in_mcast_' . $_} - $old_datas->{in_mcast});
        my $total_out_packets = ($new_datas->{'out_ucast_' . $_} - $old_datas->{out_ucast}) + ($new_datas->{'out_bcast_' . $_} - $old_datas->{out_bcast}) + ($new_datas->{'out_mcast_' . $_} - $old_datas->{out_mcast});
        
        my $in_discard_absolute_per_sec = ($new_datas->{'in_discard_' . $_} - $old_datas->{in_discard}) / $time_delta;
        my $in_error_absolute_per_sec = ($new_datas->{'in_error_' . $_} - $old_datas->{in_error}) / $time_delta;
        my $out_discard_absolute_per_sec = ($new_datas->{'out_discard_' . $_} - $old_datas->{out_discard}) / $time_delta;
        my $out_error_absolute_per_sec = ($new_datas->{'out_error_' . $_} - $old_datas->{out_error}) / $time_delta;
        my $in_discard_prct = ($total_in_packets == 0) ? 0 : ($new_datas->{'in_discard_' . $_} - $old_datas->{in_discard}) * 100 / $total_in_packets;
        my $in_error_prct = ($total_in_packets == 0) ? 0 : ($new_datas->{'in_error_' . $_} - $old_datas->{in_error}) * 100 / $total_in_packets;
        my $out_discard_prct = ($total_out_packets == 0) ? 0 : ($new_datas->{'out_discard_' . $_} - $old_datas->{out_discard}) * 100 / $total_out_packets;
        my $out_error_prct = ($total_out_packets == 0) ? 0 : ($new_datas->{'out_error_' . $_} - $old_datas->{out_error}) * 100 / $total_out_packets;
       
        ###########
        # Manage Output
        ###########
        my $exit1 = $self->{perfdata}->threshold_check(value => $in_discard_prct, threshold => [ { label => 'critical-in-discard', 'exit_litteral' => 'critical' }, { label => 'warning-in-discard', exit_litteral => 'warning' } ]);
        my $exit2 = $self->{perfdata}->threshold_check(value => $in_error_prct, threshold => [ { label => 'critical-in-error', 'exit_litteral' => 'critical' }, { label => 'warning-in-error', exit_litteral => 'warning' } ]);
        my $exit3 = $self->{perfdata}->threshold_check(value => $out_discard_prct, threshold => [ { label => 'critical-out-discard', 'exit_litteral' => 'critical' }, { label => 'warning-out-discard', exit_litteral => 'warning' } ]);
        my $exit4 = $self->{perfdata}->threshold_check(value => $out_error_prct, threshold => [ { label => 'critical-out-error', 'exit_litteral' => 'critical' }, { label => 'warning-out-error', exit_litteral => 'warning' } ]);

        my $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2, $exit3, $exit4 ]);
        $self->{output}->output_add(long_msg => sprintf("Interface '%s' Packets In Discard : %.2f %% (%d), In Error : %.2f %% (%d), Out Discard: %.2f %% (%d), Out Error: %.2f %% (%d)", $display_value,
                                       $in_discard_prct, $new_datas->{'in_discard_' . $_} - $old_datas->{in_discard},
                                       $in_error_prct, $new_datas->{'in_error_' . $_} - $old_datas->{in_error},
                                       $out_discard_prct, $new_datas->{'out_discard_' . $_} - $old_datas->{out_discard},
                                       $out_error_prct, $new_datas->{'out_error_' . $_} - $old_datas->{out_error}
                                       ));
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1) || (defined($self->{option_results}->{interface}) && !defined($self->{option_results}->{use_regexp}))) {
            $self->{output}->output_add(severity => $exit,
                                        short_msg => sprintf("Interface '%s' Packets In Discard : %.2f %% (%d), In Error : %.2f %% (%d), Out Discard: %.2f %% (%d), Out Error: %.2f %% (%d)", $display_value,
                                       $in_discard_prct, $new_datas->{'in_discard_' . $_} - $old_datas->{in_discard},
                                       $in_error_prct, $new_datas->{'in_error_' . $_} - $old_datas->{in_error},
                                       $out_discard_prct, $new_datas->{'out_discard_' . $_} - $old_datas->{out_discard},
                                       $out_error_prct, $new_datas->{'out_error_' . $_} - $old_datas->{out_error}
                                       ));
        }

        my $extra_label = '';
        $extra_label = '_' . $display_value if (!defined($self->{option_results}->{interface}) || defined($self->{option_results}->{use_regexp}));
        $self->{output}->perfdata_add(label => 'packets_discard_in' . $extra_label, unit => '%',
                                      value => sprintf("%.2f", $in_discard_prct),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning-in-discard'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical-in-discard'),
                                      min => 0, max => 100);
        $self->{output}->perfdata_add(label => 'packets_error_in' . $extra_label, unit => '%',
                                      value => sprintf("%.2f", $in_error_prct),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning-in-error'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical-in-error'),
                                      min => 0, max => 100);
        $self->{output}->perfdata_add(label => 'packets_discard_out' . $extra_label, unit => '%',
                                      value => sprintf("%.2f", $out_discard_prct),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning-out-discard'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical-out-discard'),
                                      min => 0, max => 100);
        $self->{output}->perfdata_add(label => 'packets_error_out' . $extra_label, unit => '%',
                                      value => sprintf("%.2f", $out_error_prct),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning-out-error'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical-out-error'),
                                      min => 0, max => 100);
    }

    $self->{statefile_value}->write(data => $new_datas);    
    if (!defined($old_timestamp)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
    }

    $self->{output}->display();
    $self->{output}->exit();
}

sub get_display_value {
    my ($self, %options) = @_;
    my $value = $self->{statefile_cache}->get(name => $self->{option_results}->{oid_display} . "_" . $options{id});

    if (defined($self->{option_results}->{display_transform_src})) {
        $self->{option_results}->{display_transform_dst} = '' if (!defined($self->{option_results}->{display_transform_dst}));
        eval "\$value =~ s{$self->{option_results}->{display_transform_src}}{$self->{option_results}->{display_transform_dst}}";
    }
    return $value;
}

sub reload_cache {
    my ($self) = @_;
    my $datas = {};

    $datas->{oid_filter} = $self->{option_results}->{oid_filter};
    $datas->{oid_display} = $self->{option_results}->{oid_display};
    my $result = $self->{snmp}->get_table(oid => $oids_iftable{$self->{option_results}->{oid_filter}});
    my $last_num = 0;
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($key !~ /\.([0-9]+)$/);
        $datas->{$self->{option_results}->{oid_filter} . "_" . $1} = $self->{output}->to_utf8($result->{$key});
        $last_num = $1;
    }
    
    if (scalar(keys %$datas) <= 0) {
        $self->{output}->add_option_msg(short_msg => "Can't construct cache...");
        $self->{output}->option_exit();
    }

    if ($self->{option_results}->{oid_filter} ne $self->{option_results}->{oid_display}) {
       $result = $self->{snmp}->get_table(oid => $oids_iftable{$self->{option_results}->{oid_display}});
       foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
            next if ($key !~ /\.([0-9]+)$/);
            $datas->{$self->{option_results}->{oid_display} . "_" . $1} = $self->{output}->to_utf8($result->{$key});
       }
    }
    
    $datas->{total_interface} = $last_num;
    $self->{statefile_cache}->write(data => $datas);
}

sub manage_selection {
    my ($self, %options) = @_;

    # init cache file
    my $has_cache_file = $self->{statefile_cache}->read(statefile => 'cache_snmpstandard_' . $self->{hostname}  . '_' . $self->{mode});
    if (defined($self->{option_results}->{show_cache})) {
        $self->{output}->add_option_msg(long_msg => $self->{statefile_cache}->get_string_content());
        $self->{output}->option_exit();
    }

    my $timestamp_cache = $self->{statefile_cache}->get(name => 'last_timestamp');
    my $oid_display = $self->{statefile_cache}->get(name => 'oid_display');
    my $oid_filter = $self->{statefile_cache}->get(name => 'oid_filter');
    if ($has_cache_file == 0 ||
        ($self->{option_results}->{oid_display} !~ /^($oid_display|$oid_filter)$/i || $self->{option_results}->{oid_filter} !~ /^($oid_display|$oid_filter)$/i) ||
        (defined($timestamp_cache) && (time() - $timestamp_cache) > (($self->{option_results}->{reload_cache_time}) * 60))) {
        $self->reload_cache();
        $self->{statefile_cache}->read();
    }

    my $total_interface = $self->{statefile_cache}->get(name => 'total_interface');
    if (!defined($self->{option_results}->{use_name}) && defined($self->{option_results}->{interface})) {
        # get by ID
        push @{$self->{interface_id_selected}}, $self->{option_results}->{interface}; 
        my $name = $self->{statefile_cache}->get(name => $self->{option_results}->{oid_display} . "_" . $self->{option_results}->{interface});
        if (!defined($name)) {
            $self->{output}->add_option_msg(short_msg => "No interface found for id '" . $self->{option_results}->{interface} . "'.");
            $self->{output}->option_exit();
        }
    } else {
        for (my $i = 0; $i <= $total_interface; $i++) {
            my $filter_name = $self->{statefile_cache}->get(name => $self->{option_results}->{oid_filter} . "_" . $i);
            next if (!defined($filter_name));
            if (!defined($self->{option_results}->{interface})) {
                push @{$self->{interface_id_selected}}, $i; 
                next;
            }
            if (defined($self->{option_results}->{use_regexp}) && defined($self->{option_results}->{use_regexpi}) && $filter_name =~ /$self->{option_results}->{interface}/i) {
                push @{$self->{interface_id_selected}}, $i; 
            }
            if (defined($self->{option_results}->{use_regexp}) && !defined($self->{option_results}->{use_regexpi}) && $filter_name =~ /$self->{option_results}->{interface}/) {
                push @{$self->{interface_id_selected}}, $i; 
            }
            if (!defined($self->{option_results}->{use_regexp}) && !defined($self->{option_results}->{use_regexpi}) && $filter_name eq $self->{option_results}->{interface}) {
                push @{$self->{interface_id_selected}}, $i; 
            }
        }
        
        if (scalar(@{$self->{interface_id_selected}}) <= 0) {
            $self->{output}->add_option_msg(short_msg => "No interface found for name '" . $self->{option_results}->{interface} . "' (maybe you should reload cache file).");
            $self->{output}->option_exit();
        }
    }
}

1;

__END__

=head1 MODE

=over 8

=item B<--warning-in-discard>

Threshold warning in percent for 'in' discard packets.

=item B<--critical-in-discard>

Threshold critical in percent for 'in' discard packets.

=item B<--warning-out-discard>

Threshold warning in percent for 'out' discard packets.

=item B<--critical-out-discard>

Threshold critical in percent for 'out' discard packets.

=item B<--warning-in-error>

Threshold warning in percent for 'in' error packets.

=item B<--critical-in-error>

Threshold critical in percent for 'in' error packets.

=item B<--warning-out-error>

Threshold warning in percent for 'out' error packets.

=item B<--critical-out-error>

Threshold critical in percent for 'out' error packets.

=item B<--interface>

Set the interface (number expected) ex: 1, 2,... (empty means 'check all interface').

=item B<--name>

Allows to use interface name with option --interface instead of interface oid index.

=item B<--regexp>

Allows to use regexp to filter interfaces (with option --name).

=item B<--regexp-isensitive>

Allows to use regexp non case-sensitive (with --regexp).

=item B<--speed>

Set interface speed (in Mb).

=item B<--skip>

Skip errors on interface status.

=item B<--reload-cache-time>

Time in seconds before reloading cache file (default: 180).

=item B<--oid-filter>

Choose OID used to filter interface (default: ifName) (values: ifDesc, ifAlias, ifName).

=item B<--oid-display>

Choose OID used to display interface (default: ifName) (values: ifDesc, ifAlias, ifName).

=item B<--display-transform-src>

Regexp src to transform display value. (security risk!!!)

=item B<--display-transform-dst>

Regexp dst to transform display value. (security risk!!!)

=item B<--show-cache>

Display cache interface datas.

=back

=cut
