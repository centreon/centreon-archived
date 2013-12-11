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

package snmp_standard::mode::liststorages;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my %oids_hrStorageTable = (
    'hrstoragedescr' => '.1.3.6.1.2.1.25.2.3.1.3',
    'hrfsmountpoint' => '.1.3.6.1.2.1.25.3.8.1.2',
    'hrstoragetype' => '.1.3.6.1.2.1.25.2.3.1.2',
);

my $oid_hrStorageAllocationUnits = '.1.3.6.1.2.1.25.2.3.1.4';
my $oid_hrStorageSize = '.1.3.6.1.2.1.25.2.3.1.5';
my $oid_hrStorageType = '.1.3.6.1.2.1.25.2.3.1.2';
my $oid_hrStorageFixedDisk = '.1.3.6.1.2.1.25.2.1.4';
my $oid_hrStorageNetworkDisk = '.1.3.6.1.2.1.25.2.1.10';

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "storage:s"               => { name => 'storage' },
                                  "name"                    => { name => 'use_name' },
                                  "regexp"                  => { name => 'use_regexp' },
                                  "regexp-isensitive"       => { name => 'use_regexpi' },
                                  "oid-filter:s"            => { name => 'oid_filter', default => 'hrStorageDescr'},
                                  "oid-display:s"           => { name => 'oid_display', default => 'hrStorageDescr'},
                                  "display-transform-src:s" => { name => 'display_transform_src' },
                                  "display-transform-dst:s" => { name => 'display_transform_dst' },
                                });

    $self->{storage_id_selected} = [];
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    $self->{option_results}->{oid_filter} = lc($self->{option_results}->{oid_filter});
    if ($self->{option_results}->{oid_filter} !~ /^(hrstoragedescr|hrfsmountpoint)$/) {
       $self->{output}->add_option_msg(short_msg => "Unsupported --oid-filter option.");
       $self->{output}->option_exit();
    }
    $self->{option_results}->{oid_display} = lc($self->{option_results}->{oid_display});
    if ($self->{option_results}->{oid_display} !~ /^(hrstoragedescr|hrfsmountpoint)$/) {
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
    
    my $storage_display = '';
    my $storage_display_append = '';
    foreach (sort @{$self->{storage_id_selected}}) {
        my $display_value = $self->get_display_value(id => $_);
        my $storage_type = $result->{$oid_hrStorageType . "." . $_};
        next if (!defined($storage_type) || ($storage_type ne $oid_hrStorageFixedDisk && $storage_type ne $oid_hrStorageNetworkDisk));
        
        $storage_display .= $storage_display_append . "name = $display_value [size = " . $result->{$oid_hrStorageSize . "." . $_} * $result->{$oid_hrStorageAllocationUnits . "." . $_}  . "B, id = $_]";
        $storage_display_append = ', ';
    }

    $self->{output}->output_add(severity => 'OK',
                                short_msg => 'List storage: ' . $storage_display);
    $self->{output}->display(nolabel => 1);
    $self->{output}->exit();
}

sub get_additional_information {
    my ($self, %options) = @_;
    
    $self->{snmp}->load(oids => [$oid_hrStorageType, $oid_hrStorageAllocationUnits, $oid_hrStorageSize], instances => $self->{storage_id_selected});
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
    my $result = $self->{snmp}->get_table(oid => $oids_hrStorageTable{$self->{option_results}->{oid_filter}});
    my $total_storage = 0;
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($key !~ /\.([0-9]+)$/);
        $self->{datas}->{$self->{option_results}->{oid_filter} . "_" . $1} = $self->{output}->to_utf8($result->{$key});
        $total_storage = $1;
    }
    
    if (scalar(keys %{$self->{datas}}) <= 0) {
        $self->{output}->add_option_msg(short_msg => "Can't get storages...");
        $self->{output}->option_exit();
    }

    if ($self->{option_results}->{oid_filter} ne $self->{option_results}->{oid_display}) {
       $result = $self->{snmp}->get_table(oid => $oids_hrStorageTable{$self->{option_results}->{oid_display}});
       foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
            next if ($key !~ /\.([0-9]+)$/);
            $self->{datas}->{$self->{option_results}->{oid_display} . "_" . $1} = $self->{output}->to_utf8($result->{$key});
       }
    }
    
    if (!defined($self->{option_results}->{use_name}) && defined($self->{option_results}->{storage})) {
        # get by ID
        push @{$self->{storage_id_selected}}, $self->{option_results}->{storage}; 
        my $name = $self->{datas}->{$self->{option_results}->{oid_display} . "_" . $self->{option_results}->{storage}};
        if (!defined($name)) {
            $self->{output}->add_option_msg(short_msg => "No storage found for id '" . $self->{option_results}->{storage} . "'.");
            $self->{output}->option_exit();
        }
    } else {
        for (my $i = 0; $i <= $total_storage; $i++) {
            my $filter_name = $self->{datas}->{$self->{option_results}->{oid_filter} . "_" . $i};
            next if (!defined($filter_name));
            if (!defined($self->{option_results}->{storage})) {
                push @{$self->{storage_id_selected}}, $i; 
                next;
            }
            if (defined($self->{option_results}->{use_regexp}) && defined($self->{option_results}->{use_regexpi}) && $filter_name =~ /$self->{option_results}->{storage}/i) {
                push @{$self->{storage_id_selected}}, $i; 
            }
            if (defined($self->{option_results}->{use_regexp}) && !defined($self->{option_results}->{use_regexpi}) && $filter_name =~ /$self->{option_results}->{storage}/) {
                push @{$self->{storage_id_selected}}, $i; 
            }
            if (!defined($self->{option_results}->{use_regexp}) && !defined($self->{option_results}->{use_regexpi}) && $filter_name eq $self->{option_results}->{storage}) {
                push @{$self->{storage_id_selected}}, $i; 
            }
        }
        
        if (scalar(@{$self->{storage_id_selected}}) <= 0) {
            $self->{output}->add_option_msg(short_msg => "No storage found for name '" . $self->{option_results}->{storage} . "'.");
            $self->{output}->option_exit();
        }
    }
}

sub disco_format {
    my ($self, %options) = @_;
    
    $self->{output}->add_disco_format(elements => ['name', 'total', 'storageid']);
}

sub disco_show {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    my $result = $self->get_additional_information();
    foreach (sort @{$self->{storage_id_selected}}) {
        my $display_value = $self->get_display_value(id => $_);
        my $storage_type = $result->{$oid_hrStorageType . "." . $_};
        next if (!defined($storage_type) || ($storage_type ne $oid_hrStorageFixedDisk && $storage_type ne $oid_hrStorageNetworkDisk));

        $self->{output}->add_disco_entry(name => $display_value,
                                         total => $result->{$oid_hrStorageSize . "." . $_} * $result->{$oid_hrStorageAllocationUnits . "." . $_},
                                         storageid => $_);
    }
}

1;

__END__

=head1 MODE

=over 8

=item B<--storage>

Set the storage (number expected) ex: 1, 2,... (empty means 'check all storage').

=item B<--name>

Allows to use storage name with option --storage instead of storage oid index.

=item B<--regexp>

Allows to use regexp to filter storage (with option --name).

=item B<--regexp-isensitive>

Allows to use regexp non case-sensitive (with --regexp).

=item B<--oid-filter>

Choose OID used to filter storage (default: hrStorageDescr) (values: hrStorageDescr, hrFSRemoteMountPoint).

=item B<--oid-display>

Choose OID used to display storage (default: hrStorageDescr) (values: hrStorageDescr, hrFSRemoteMountPoint).

=item B<--display-transform-src>

Regexp src to transform display value. (security risk!!!)

=item B<--display-transform-dst>

Regexp dst to transform display value. (security risk!!!)

=back

=cut
