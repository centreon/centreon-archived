package snmp_standard::mode::diskio;

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
                                  "warning-read:s"          => { name => 'warning_read' },
                                  "critical-read:s"         => { name => 'critical_read' },
                                  "warning-write:s"         => { name => 'warning_write' },
                                  "critical-write:s"        => { name => 'critical_write' },
                                  "reload-cache-time:s"     => { name => 'reload_cache_time' },
                                  "name"                    => { name => 'use_name' },
                                  "device:s"                => { name => 'device' },
                                  "regexp"                  => { name => 'use_regexp' },
                                  "regexp-isensitive"       => { name => 'use_regexpi' },            
                                  "show-cache"              => { name => 'show_cache' },
                                });

    $self->{device_id_selected} = [];
    $self->{statefile_cache} = centreon::plugins::statefile->new(%options);
    $self->{statefile_value} = centreon::plugins::statefile->new(%options);
    
    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (($self->{perfdata}->threshold_validate(label => 'warning-read', value => $self->{option_results}->{warning_read})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'read' threshold '" . $self->{option_results}->{warning_read} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical-read', value => $self->{option_results}->{critical_read})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'read' threshold '" . $self->{option_results}->{critical_read} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'warning-write', value => $self->{option_results}->{warning_write})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong warning 'write' threshold '" . $self->{option_results}->{warning_write} . "'.");
        $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical-write', value => $self->{option_results}->{critical_write})) == 0) {
        $self->{output}->add_option_msg(short_msg => "Wrong critical 'write' threshold '" . $self->{option_results}->{critical_write} . "'.");
        $self->{output}->option_exit();
    }
    
    $self->{statefile_cache}->check_options(%options);
    $self->{statefile_value}->check_options(%options);
}

sub run {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    
    if ($self->{snmp}->is_snmpv1()) {
        $self->{output}->add_option_msg(short_msg => "Need to use SNMP v2c or v3.");
        $self->{output}->option_exit();
    }
    
    $self->{hostname} = $self->{snmp}->get_hostname();

    $self->manage_selection();
    
    my $oid_diskIODevice = '.1.3.6.1.4.1.2021.13.15.1.1.2';
    my $oid_diskIONReadX = '.1.3.6.1.4.1.2021.13.15.1.1.12'; # in B
    my $oid_diskIONWrittenX = '.1.3.6.1.4.1.2021.13.15.1.1.13'; # in B

    my $new_datas = {};
    $self->{statefile_value}->read(statefile => $self->{hostname}  . '_' . $self->{mode} . '_' . (defined($self->{option_results}->{device}) ? md5_hex($self->{option_results}->{device}) : md5_hex('all')));

    $self->{snmp}->load(oids => [$oid_diskIONReadX, $oid_diskIONWrittenX], 
                        instances => $self->{device_id_selected});
    my $result = $self->{snmp}->get_leef();
    $new_datas->{last_timestamp} = time();
    my $old_timestamp = $self->{statefile_value}->get(name => 'last_timestamp');
    if (!defined($self->{option_results}->{device}) || defined($self->{option_results}->{use_regexp})) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => 'All devices are ok.');
    }

    foreach (sort @{$self->{device_id_selected}}) {
        my $device_name = $self->{statefile_cache}->get(name => "device_" . $_);
    
        if ($result->{$oid_diskIONReadX . "." . $_} == 0 && $result->{$oid_diskIONWrittenX . "." . $_} == 0 &&
            (!defined($self->{option_results}->{device}) || defined($self->{option_results}->{use_regexp}))) {
            $self->{output}->add_option_msg(long_msg => "Skip device '" . $device_name . "' with no values.");
            next;
        }
 
        $new_datas->{'readio_' . $_} = $result->{$oid_diskIONReadX . "." . $_};
        $new_datas->{'writeio_' . $_} = $result->{$oid_diskIONWrittenX . "." . $_};

        my $old_readio = $self->{statefile_value}->get(name => 'readio_' . $_);
        my $old_writeio = $self->{statefile_value}->get(name => 'writeio_' . $_);
        if (!defined($old_timestamp) || !defined($old_readio) || !defined($old_writeio)) {
            next;
        }
        if ($new_datas->{'readio_' . $_} < $old_readio) {
            # We set 0. Has reboot.
            $old_readio = 0;
        }
        if ($new_datas->{'writeio_' . $_} < $old_writeio) {
            # We set 0. Has reboot.
            $old_writeio = 0;
        }

        my $time_delta = $new_datas->{last_timestamp} - $old_timestamp;
        if ($time_delta <= 0) {
            # At least one second. two fast calls ;)
            $time_delta = 1;
        }
        
        my $readio_absolute_per_sec = ($new_datas->{'readio_' . $_} - $old_readio) / $time_delta;
        my $writeio_absolute_per_sec = ($new_datas->{'writeio_' . $_} - $old_writeio) / $time_delta;
       
        ###########
        # Manage Output
        ###########
        my $exit1 = $self->{perfdata}->threshold_check(value => $readio_absolute_per_sec, threshold => [ { label => 'critical-read', 'exit_litteral' => 'critical' }, { label => 'warning-read', exit_litteral => 'warning' } ]);
        my $exit2 = $self->{perfdata}->threshold_check(value => $writeio_absolute_per_sec, threshold => [ { label => 'critical-write', 'exit_litteral' => 'critical' }, { label => 'warning-write', exit_litteral => 'warning' } ]);

        my ($readio_value, $readio_unit) = $self->{perfdata}->change_bytes(value => $readio_absolute_per_sec);
        my ($writeio_value, $writeio_unit) = $self->{perfdata}->change_bytes(value => $writeio_absolute_per_sec);
        my $exit = $self->{output}->get_most_critical(status => [ $exit1, $exit2 ]);
        $self->{output}->output_add(long_msg => sprintf("Device '%s' Read I/O : %s/s, Write I/O : %s/s", $device_name,
                                    $readio_value . $readio_unit,
                                    $writeio_value . $writeio_unit));
        if (!$self->{output}->is_status(value => $exit, compare => 'ok', litteral => 1) || (defined($self->{option_results}->{device}) && !defined($self->{option_results}->{use_regexp}))) {
            $self->{output}->output_add(severity => $exit,
                                        short_msg => sprintf("Device '%s' Read I/O : %s/s, Write I/O : %s/s", $device_name,
                                            $readio_value . $readio_unit,
                                            $writeio_value . $writeio_unit));
        }

        my $extra_label = '';
        $extra_label = '_' . $device_name if (!defined($self->{option_results}->{device}) || defined($self->{option_results}->{use_regexp}));
        $self->{output}->perfdata_add(label => 'readio' . $extra_label, unit => 'b/s',
                                      value => sprintf("%.2f", $readio_absolute_per_sec),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning-read'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical-read'),
                                      min => 0);
        $self->{output}->perfdata_add(label => 'writeio' . $extra_label, unit => 'b/s',
                                      value => sprintf("%.2f", $writeio_absolute_per_sec),
                                      warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning-write'),
                                      critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical-write'),
                                      min => 0);
    }

    $self->{statefile_value}->write(data => $new_datas);    
    if (!defined($old_timestamp)) {
        $self->{output}->output_add(severity => 'OK',
                                    short_msg => "Buffer creation...");
    }

    $self->{output}->display();
    $self->{output}->exit();
}

sub reload_cache {
    my ($self) = @_;
    my $datas = {};

    my $oid_diskIODevice = '.1.3.6.1.4.1.2021.13.15.1.1.2';
    my ($exit, $result) = $self->{snmp}->get_table(oid => $oid_diskIODevice);
    my $last_num = 0;
    foreach my $key ($self->{snmp}->oid_lex_sort(keys %$result)) {
        next if ($key !~ /\.([0-9]+)$/);
        $datas->{"device_" . $1} = $result->{$key};
        $last_num = $1;
    }
    
    if (scalar(keys %$datas) <= 0) {
        $self->{output}->add_option_msg(short_msg => "Can't construct cache...");
        $self->{output}->option_exit();
    }
   
    $datas->{total_device} = $last_num;
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
    if ($has_cache_file == 0 ||
        (defined($timestamp_cache) && (time() - $timestamp_cache) > (($self->{option_results}->{reload_cache_time}) * 60))) {
        $self->reload_cache();
        $self->{statefile_cache}->read();
    }

    my $total_device = $self->{statefile_cache}->get(name => 'total_device');
    if (!defined($self->{option_results}->{use_name}) && defined($self->{option_results}->{device})) {
        # get by ID
        push @{$self->{device_id_selected}}, $self->{option_results}->{device}; 
        my $name = $self->{statefile_cache}->get(name => "device_" . $self->{option_results}->{device});
        if (!defined($name)) {
            $self->{output}->add_option_msg(short_msg => "No device for id '" . $self->{option_results}->{device} . "'.");
            $self->{output}->option_exit();
        }
    } else {
        for (my $i = 0; $i <= $total_device; $i++) {
            my $filter_name = $self->{statefile_cache}->get(name => "device_" . $i);
            next if (!defined($filter_name));
            if (!defined($self->{option_results}->{device})) {
                push @{$self->{device_id_selected}}, $i; 
                next;
            }
            if (defined($self->{option_results}->{use_regexp}) && defined($self->{option_results}->{use_regexpi}) && $filter_name =~ /$self->{option_results}->{device}/i) {
                push @{$self->{device_id_selected}}, $i; 
            }
            if (defined($self->{option_results}->{use_regexp}) && !defined($self->{option_results}->{use_regexpi}) && $filter_name =~ /$self->{option_results}->{device}/) {
                push @{$self->{device_id_selected}}, $i; 
            }
            if (!defined($self->{option_results}->{use_regexp}) && !defined($self->{option_results}->{use_regexpi}) && $filter_name eq $self->{option_results}->{device}) {
                push @{$self->{device_id_selected}}, $i; 
            }
        }
        
        if (scalar(@{$self->{device_id_selected}}) <= 0) {
            $self->{output}->add_option_msg(short_msg => "No device found for name '" . $self->{option_results}->{device} . "' (maybe you should reload cache file).");
            $self->{output}->option_exit();
        }
    }
}

sub disco_format {
    my ($self, %options) = @_;
    
    $self->{output}->add_disco_format(elements => ['name', 'deviceid']);
}

sub disco_show {
    my ($self, %options) = @_;
    # $options{snmp} = snmp object
    $self->{snmp} = $options{snmp};
    $self->{hostname} = $self->{snmp}->get_hostname();

    $self->manage_selection();
    foreach (sort @{$self->{device_id_selected}}) {
        $self->{output}->add_disco_entry(name => $self->{statefile_cache}->get(name => "device_" . $_),
                                         deviceid => $_);
    }
}

1;

__END__

=head1 MODE

Check read/write I/O disks. 

=over 8

=item B<--warning-read>

Threshold warning in bytes for 'read' io disks.

=item B<--critical-read>

Threshold critical in bytes for 'read' io disks.

=item B<--warning-write>

Threshold warning in bytes for 'write' io disks.

=item B<--critical-write>

Threshold critical in bytes for 'write' io disks.

=item B<--device>

Set the device (number expected) ex: 1, 2,... (empty means 'check all devices').

=item B<--name>

Allows to use device name with option --device instead of devoce oid index.

=item B<--regexp>

Allows to use regexp to filter devices (with option --name).

=item B<--regexp-isensitive>

Allows to use regexp non case-sensitive (with --regexp).

=item B<--reload-cache-time>

Time in seconds before reloading cache file (default: 180).

=item B<--show-cache>

Display cache interface datas.

=back

=cut
