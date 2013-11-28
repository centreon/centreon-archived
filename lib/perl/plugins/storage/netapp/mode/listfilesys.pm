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

package storage::netapp::mode::listfilesys;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

my $oid_dfFileSys = '.1.3.6.1.4.1.789.1.5.4.1.2';
my $oid_dfType = '.1.3.6.1.4.1.789.1.5.4.1.23';
my $oid_dfKBytesTotal = '.1.3.6.1.4.1.789.1.5.4.1.3';
my $oid_df64TotalKBytes = '.1.3.6.1.4.1.789.1.5.4.1.29';

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
                                  "name:s"                => { name => 'name' },
                                  "regexp"                => { name => 'use_regexp' },
                                  "type:s"                => { name => 'type' },
                                  "skip-total-zero"       => { name => 'skip_total_zero' },
                                });
    $self->{filesys_id_selected} = [];

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);
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
}

sub get_additional_information {
    my ($self, %options) = @_;

    return if (scalar(@{$self->{filesys_id_selected}}) <= 0);
    $self->{snmp}->load(oids => [$oid_dfKBytesTotal], instances => $self->{filesys_id_selected});
    if (!$self->{snmp}->is_snmpv1()) {
        $self->{snmp}->load(oids => [$oid_df64TotalKBytes], instances => $self->{filesys_id_selected});
    }    
    return $self->{snmp}->get_leef();
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    my $result = $self->get_additional_information();
    my $filesys_display = '';
    my $filesys_display_append = '';
    foreach my $instance (sort @{$self->{filesys_id_selected}}) { 
        my $name = $self->{result_names}->{$oid_dfFileSys . '.' . $instance};
        my $type = $self->{result_types}->{$oid_dfType . '.' . $instance};
        my $total_size = $result->{$oid_dfKBytesTotal . '.' . $instance} * 1024;
        if (defined($result->{$oid_df64TotalKBytes . '.' . $instance}) && $result->{$oid_df64TotalKBytes . '.' . $instance} != 0) {
            $total_size = $result->{$oid_df64TotalKBytes . '.' . $instance} * 1024;
        }
        next if (defined($self->{option_results}->{skip_total_zero}) && $total_size == 0);

        $filesys_display .= $filesys_display_append . "name = $name [total_size = $total_size B, type = " . $map_types{$type} . "]";
        $filesys_display_append = ', ';
    }
    
    $self->{output}->output_add(severity => 'OK',
                                short_msg => 'List filesys: ' . $filesys_display);
    $self->{output}->display(nolabel => 1);
    $self->{output}->exit();
}

sub disco_format {
    my ($self, %options) = @_;
    
    $self->{output}->add_disco_format(elements => ['name', 'total', 'type']);
}

sub disco_show {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};

    $self->manage_selection();
    my $result = $self->get_additional_information();
    foreach my $instance (sort @{$self->{filesys_id_selected}}) {        
        my $name = $self->{result_names}->{$oid_dfFileSys . '.' . $instance};
        my $type = $self->{result_types}->{$oid_dfType . '.' . $instance};
        my $total_size = $result->{$oid_dfKBytesTotal . '.' . $instance} * 1024;
        if (defined($result->{$oid_df64TotalKBytes . '.' . $instance}) && $result->{$oid_df64TotalKBytes . '.' . $instance} != 0) {
            $total_size = $result->{$oid_df64TotalKBytes . '.' . $instance} * 1024;
        }
        next if (defined($self->{option_results}->{skip_total_zero}) && $total_size == 0);
        
        $self->{output}->add_disco_entry(name => $name,
                                         total => $total_size,
                                         type => $map_types{$type});
    }
}

1;

__END__

=head1 MODE

List filesystems (volumes and aggregates also).

=over 8

=item B<--name>

Set the filesystem name.

=item B<--regexp>

Allows to use regexp to filter filesystem name (with option --name).

=item B<--type>

Filter filesystem type (a regexp. Example: 'flexibleVolume|aggregate').

=item B<--skip-total-zero>

Don't display filesys with total equals 0.

=back

=cut
    