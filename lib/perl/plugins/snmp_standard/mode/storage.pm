package snmp_standard::mode::storage;

use base qw(centreon::plugins::mode);

use strict;
use warnings;
use centreon::plugins::statefile;
use Digest::MD5 qw(md5_hex);

my %oids_hrStorageTable = (
    'hrstoragedescr' => '.1.3.6.1.2.1.25.2.3.1.3',
    'hrfsmountpoint' => '.1.3.6.1.2.1.25.3.8.1.2',
    'hrstoragetype' => '.1.3.6.1.2.1.25.2.3.1.2',
);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"               => { name => 'warning' },
                                  "critical:s"              => { name => 'critical' },
                                  "units:s"                 => { name => 'units', default => '%' },
                                  "free"                    => { name => 'free' },
                                  "reload-cache-time:s"     => { name => 'reload_cache_time' },
                                  "name"                    => { name => 'use_name' },
                                  "storage:s"               => { name => 'storage' },
                                  "regexp"                  => { name => 'use_regexp' },
                                  "regexp-isensitive"       => { name => 'use_regexpi' },
                                  "oid-filter:s"            => { name => 'oid_filter', default => 'hrStorageDescr'},
                                  "oid-display:s"           => { name => 'oid_display', default => 'hrStorageDescr'},
                                  "display-transform-src:s" => { name => 'display_transform_src' },
                                  "display-transform-dst:s" => { name => 'display_transform_dst' },
                                  "show-cache"              => { name => 'show_cache' },
                                });

    $self->{storage_id_selected} = [];
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);
    
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
    
    $self->{statefile_cache}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();

    $self->manage_selection();
    
    my $oid_hrStorageAllocationUnits = '.1.3.6.1.2.1.25.2.3.1.4';
    my $oid_hrStorageSize = '.1.3.6.1.2.1.25.2.3.1.5';
    my $oid_hrStorageUsed = '.1.3.6.1.2.1.25.2.3.1.6';
    my $oid_hrStorageType = '.1.3.6.1.2.1.25.2.3.1.2';
    my $oid_hrStorageFixedDisk = '.1.3.6.1.2.1.25.2.1.4';
    my $oid_hrStorageNetworkDisk = '.1.3.6.1.2.1.25.2.1.10';

    $self->{snmp}->load(oids => [$oid_hrStorageAllocationUnits, $oid_hrStorageSize, $oid_hrStorageUsed], instances => $self->{storage_id_selected});
    my $result = $self->{snmp}->get_leef();

    if (!defined($self->{option_results}->{storage}) || defined($self->{option_results}->{use_regexp})) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => 'All storages are ok.');
    }

    my $num_disk_check = 0;
    foreach (sort @{$self->{storage_id_selected}}) {
    # Skipped disks
        my $storage_type = $self->{statefile_cache}->get(name => "type_" . $_);
        next if (!defined($storage_type) || ($storage_type ne $oid_hrStorageFixedDisk && $storage_type ne $oid_hrStorageNetworkDisk));

        my $name_storage = $self->get_display_value(id => $_);
        $num_disk_check++;

        # in bytes hrStorageAllocationUnits
        my $total_size = $result->{$oid_hrStorageSize . "." . $_} * $result->{$oid_hrStorageAllocationUnits . "." . $_};
        next if ($total_size == 0);
        my $total_used = $result->{$oid_hrStorageUsed . "." . $_} * $result->{$oid_hrStorageAllocationUnits . "." . $_};
        my $total_free = $total_size - $total_used;
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

        $self->{output}->output_add(long_msg => sprintf("Storage '%s' Total: %s Used: %s (%.2f%%) Free: %s (%.2f%%)", $name_storage,
                                            $total_size_value . " " . $total_size_unit,
                                            $total_used_value . " " . $total_used_unit, $prct_used,
                                            $total_free_value . " " . $total_free_unit, $prct_free));
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1) || (defined($self->{option_results}->{storage}) && !defined($self->{option_results}->{use_regexp}))) {
            $self->{output}->output_add(severity => $exit,
                                        short_msg => sprintf("Storage '%s' Total: %s Used: %s (%.2f%%) Free: %s (%.2f%%)", $name_storage,
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
        $extra_label = '_' . $name_storage if (!defined($self->{option_results}->{storage}) || defined($self->{option_results}->{use_regexp}));
        my %total_options = ();
        $total_options{total} = $total_size if ($self->{option_results}->{units} eq '%');
        $self->{output}->perfdata_add(label => $label . $extra_label, unit => 'o',
                                      value => $value_perf,
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning', %total_options),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical', %total_options),
                                      min => 0, max => $total_size);
    }

    if ($num_disk_check == 0) {
        $self->{output}->add_option_msg(short_msg => "Not a disk with a good 'type'.");
        $self->{output}->option_exit();
    }

    $self->{output}->display();
    $self->{output}->exit();
}

sub reload_cache {
    my ($self) = @_;
    my $datas = {};

    $datas->{oid_filter} = $self->{option_results}->{oid_filter};
    $datas->{oid_display} = $self->{option_results}->{oid_display};
    my ($exit, $result) = $self->{snmp}->get_table(oid => $oids_hrStorageTable{$self->{option_results}->{oid_filter}});
    my $last_num = 0;
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($key !~ /\.([0-9]+)$/);
        $datas->{$self->{option_results}->{oid_filter} . "_" . $1} = $result->{$key};
        $last_num = $1;
    }
    
    if (scalar(keys %$datas) <= 0) {
        $self->{output}->add_option_msg(short_msg => "Can't construct cache...");
        $self->{output}->option_exit();
    }

    if ($self->{option_results}->{oid_filter} ne $self->{option_results}->{oid_display}) {
        ($exit, $result) = $self->{snmp}->get_table(oid => $oids_hrStorageTable{$self->{option_results}->{oid_display}});
        foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
            next if ($key !~ /\.([0-9]+)$/);
            $datas->{$self->{option_results}->{oid_display} . "_" . $1} = $result->{$key};
        }
    }
    
    ($exit, $result) = $self->{snmp}->get_table(oid => $oids_hrStorageTable{hrstoragetype});
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($key !~ /\.([0-9]+)$/);
        $datas->{"type_" . $1} = $result->{$key};
    }

    $datas->{total_storage} = $last_num;
    $self->{statefile_cache}->write(data => $datas);
}

sub manage_selection {
    my ($self, %options) = @_;

    # init cache file
    my $has_cache_file = $self->{statefile_cache}->read(statefile => 'cache_' . $self->{hostname}  . '_' . $self->{mode});
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

    my $total_storage = $self->{statefile_cache}->get(name => 'total_storage');
    if (!defined($self->{option_results}->{use_name}) && defined($self->{option_results}->{storage})) {
        # get by ID
        push @{$self->{storage_id_selected}}, $self->{option_results}->{storage}; 
        my $name = $self->{statefile_cache}->get(name => $self->{option_results}->{oid_display} . "_" . $self->{option_results}->{storage});
        if (!defined($name)) {
            $self->{output}->add_option_msg(short_msg => "No storage found for id '" . $self->{option_results}->{storage} . "'.");
            $self->{output}->option_exit();
        }
    } else {
        for (my $i = 0; $i <= $total_storage; $i++) {
            my $filter_name = $self->{statefile_cache}->get(name => $self->{option_results}->{oid_filter} . "_" . $i);
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
            $self->{output}->add_option_msg(short_msg => "No storage found for name '" . $self->{option_results}->{storage} . "' (maybe you should reload cache file).");
            $self->{output}->option_exit();
        }
    }
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

sub disco_format {
    my ($self, %options) = @_;
    
    $self->{output}->add_disco_format(elements => ['name', 'total', 'storageid']);
}

sub disco_show {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();

    my $oid_hrStorageAllocationUnits = '.1.3.6.1.2.1.25.2.3.1.4';
    my $oid_hrStorageSize = '.1.3.6.1.2.1.25.2.3.1.5';
    my $oid_hrStorageType = '.1.3.6.1.2.1.25.2.3.1.2';
    my $oid_hrStorageFixedDisk = '.1.3.6.1.2.1.25.2.1.4';
    my $oid_hrStorageNetworkDisk = '.1.3.6.1.2.1.25.2.1.10';

    $self->manage_selection();
    $self->{snmp}->load(oids => [$oid_hrStorageAllocationUnits, $oid_hrStorageSize], instances => $self->{storage_id_selected});
    my $result = $self->{snmp}->get_leef();
    foreach (sort @{$self->{storage_id_selected}}) {
        my $display_value = $self->get_display_value(id => $_);
        my $storage_type = $self->{statefile_cache}->get(name => "type_" . $_);
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

=item B<--warning>

Threshold warning.

=item B<--critical>

Threshold critical.

=item B<--units>

Units of thresholds (Default: '%') ('%', 'B').

=item B<--free>

Thresholds are on free space left.

=item B<--storage>

Set the storage (number expected) ex: 1, 2,... (empty means 'check all storage').

=item B<--name>

Allows to use storage name with option --storage instead of storage oid index.

=item B<--regexp>

Allows to use regexp to filter storage (with option --name).

=item B<--regexp-isensitive>

Allows to use regexp non case-sensitive (with --regexp).

=item B<--reload-cache-time>

Time in seconds before reloading cache file (default: 180).

=item B<--oid-filter>

Choose OID used to filter storage (default: hrStorageDescr) (values: hrStorageDescr, hrFSRemoteMountPoint).

=item B<--oid-display>

Choose OID used to display storage (default: hrStorageDescr) (values: hrStorageDescr, hrFSRemoteMountPoint).

=item B<--display-transform-src>

Regexp src to transform display value. (security risk!!!)

=item B<--display-transform-dst>

Regexp dst to transform display value. (security risk!!!)

=item B<--show-cache>

Display cache storage datas.

=back

=cut
