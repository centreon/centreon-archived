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

package snmp_standard::mode::listinterfaces;

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

my $oid_operstatus = '.1.3.6.1.2.1.2.2.1.8';
my $oid_speed32 = '.1.3.6.1.2.1.2.2.1.5'; # in b/s
my $oid_speed64 = '.1.3.6.1.2.1.31.1.1.1.15';

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "name"                    => { name => 'use_name' },
                                  "interface:s"             => { name => 'interface' },
                                  "speed:s"                 => { name => 'speed' },
                                  "filter-status:s"         => { name => 'filter_status' },
                                  "regexp"                  => { name => 'use_regexp' },
                                  "regexp-isensitive"       => { name => 'use_regexpi' },
                                  "oid-filter:s"            => { name => 'oid_filter', default => 'ifname'},
                                  "oid-display:s"           => { name => 'oid_display', default => 'ifname'},
                                  "display-transform-src:s" => { name => 'display_transform_src' },
                                  "display-transform-dst:s" => { name => 'display_transform_dst' },
                                });

    $self->{interface_id_selected} = [];
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

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
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    my $result = $self->get_additional_information();
    
    my $interfaces_display = '';
    my $interfaces_display_append = '';
    foreach (sort @{$self->{interface_id_selected}}) {
        my $display_value = $self->get_display_value(id => $_);

        my $interface_speed = (defined($result->{$oid_speed64 . "." . $_}) && $result->{$oid_speed64 . "." . $_} ne '' ? ($result->{$oid_speed64 . "." . $_}) : (int($result->{$oid_speed32 . "." . $_} / 1000 / 1000)));        
        if (defined($self->{option_results}->{speed}) && $self->{option_results}->{speed} ne '') {
            $interface_speed = $self->{option_results}->{speed};
        }
        if (defined($self->{option_results}->{filter_status}) && $operstatus[$result->{$oid_operstatus . "." . $_} - 1] !~ /$self->{option_results}->{filter_status}/i) {
            next;
        }

        $interfaces_display .= $interfaces_display_append . "name = $display_value [speed = $interface_speed, status = " . $operstatus[$result->{$oid_operstatus . "." . $_} - 1] . ", id = $_]";
        $interfaces_display_append = ', ';
    }

    $self->{output}->output_add(severity => 'OK',
                                short_msg => 'List interfaces: ' . $interfaces_display);
    $self->{output}->display(nolabel => 1);
    $self->{output}->exit();
}

sub get_additional_information {
    my ($self, %options) = @_;

    my $oids = [$oid_operstatus, $oid_speed32];
    if (!$self->{snmp}->is_snmpv1()) {
        push @$oids, $oid_speed64;
    }
    
    $self->{snmp}->load(oids => $oids, instances => $self->{interface_id_selected});
    return $self->{snmp}->get_leef();
}

sub get_display_value {
    my ($self, %options) = @_;
    my $value = $self->{datas}->{$self->{option_results}->{oid_display} . "_" . $options{id}};

    if (defined($self->{option_results}->{display_transform_src})) {
        $self->{option_results}->{display_transform_dst} = '' if (!defined($self->{option_results}->{display_transform_dst}));
        eval "\$value =~ s{$self->{option_results}->{display_transform_src}}{$self->{option_results}->{display_transform_dst}}";
    }
    return $value;
}

sub manage_selection {
    my ($self, %options) = @_;

    $self->{datas} = {};
    $self->{datas}->{oid_filter} = $self->{option_results}->{oid_filter};
    $self->{datas}->{oid_display} = $self->{option_results}->{oid_display};
    my $result = $self->{snmp}->get_table(oid => $oids_iftable{$self->{option_results}->{oid_filter}});
    my $total_interface = 0;
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($key !~ /\.([0-9]+)$/);
        $self->{datas}->{$self->{option_results}->{oid_filter} . "_" . $1} = $self->{output}->to_utf8($result->{$key});
        $total_interface = $1;
    }
    
    if (scalar(keys %{$self->{datas}}) <= 0) {
        $self->{output}->add_option_msg(short_msg => "Can't get interfaces...");
        $self->{output}->option_exit();
    }

    if ($self->{option_results}->{oid_filter} ne $self->{option_results}->{oid_display}) {
       $result = $self->{snmp}->get_table(oid => $oids_iftable{$self->{option_results}->{oid_display}});
       foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
            next if ($key !~ /\.([0-9]+)$/);
            $self->{datas}->{$self->{option_results}->{oid_display} . "_" . $1} = $self->{output}->to_utf8($result->{$key});
       }
    }
    
    if (!defined($self->{option_results}->{use_name}) && defined($self->{option_results}->{interface})) {
        # get by ID
        push @{$self->{interface_id_selected}}, $self->{option_results}->{interface}; 
        my $name = $self->{datas}->{$self->{option_results}->{oid_display} . "_" . $self->{option_results}->{interface}};
        if (!defined($name)) {
            $self->{output}->add_option_msg(short_msg => "No interface found for id '" . $self->{option_results}->{interface} . "'.");
            $self->{output}->option_exit();
        }
    } else {
        for (my $i = 0; $i <= $total_interface; $i++) {
            my $filter_name = $self->{datas}->{$self->{option_results}->{oid_filter} . "_" . $i};
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
            $self->{output}->add_option_msg(short_msg => "No interface found for name '" . $self->{option_results}->{interface} . "'.");
            $self->{output}->option_exit();
        }
    }
}

sub disco_format {
    my ($self, %options) = @_;
    
    $self->{output}->add_disco_format(elements => ['name', 'total', 'status', 'interfaceid']);
}

sub disco_show {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    my $result = $self->get_additional_information();
    foreach (sort @{$self->{interface_id_selected}}) {
        my $display_value = $self->get_display_value(id => $_);
        
        my $interface_speed = (defined($result->{$oid_speed64 . "." . $_}) && $result->{$oid_speed64 . "." . $_} ne '' ? ($result->{$oid_speed64 . "." . $_}) : (int($result->{$oid_speed32 . "." . $_} / 1000 / 1000)));        
        if (defined($self->{option_results}->{speed}) && $self->{option_results}->{speed} ne '') {
            $interface_speed = $self->{option_results}->{speed};
        }
        if (defined($self->{option_results}->{filter_status}) && $operstatus[$result->{$oid_operstatus . "." . $_} - 1] !~ /$self->{option_results}->{filter_status}/i) {
            next;
        }

        $self->{output}->add_disco_entry(name => $display_value,
                                         total => $interface_speed,
                                         status => $result->{$oid_operstatus . "." . $_},
                                         interfaceid => $_);
    }
}

1;

__END__

=head1 MODE

=over 8

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

=item B<--filter-status>

Display interfaces matching the filter (example: 'up').

=item B<--oid-filter>

Choose OID used to filter interface (default: ifName) (values: ifDesc, ifAlias, ifName).

=item B<--oid-display>

Choose OID used to display interface (default: ifName) (values: ifDesc, ifAlias, ifName).

=item B<--display-transform-src>

Regexp src to transform display value. (security risk!!!)

=item B<--display-transform-dst>

Regexp dst to transform display value. (security risk!!!)

=back

=cut
