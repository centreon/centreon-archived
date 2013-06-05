
package centreon::centstorage::CentstorageLib;
use File::Basename;

sub start_or_not {
    my ($centreon_db_centreon) = @_;
    my $status = 1;
    
    my ($status2, $stmt) = $centreon_db_centreon->query("SELECT value FROM options WHERE `key` = 'centstorage' LIMIT 1");
    my $data = $stmt->fetchrow_hashref();
    if (defined($data) && int($data->{'value'}) == 0) {
        $status = 0;
    }
    return $status;
}

sub get_main_perfdata_file {
    my ($centreon_db_centreon) = @_;
    my $filename;

    my ($status, $stmt) = $centreon_db_centreon->query("SELECT `nagios_perfdata` FROM `nagios_server` WHERE `localhost` = '1'");
    my $data = $stmt->fetchrow_hashref();
    if (defined($data)) {
        $filename = $data->{'nagios_perfdata'};
    }
    return ($status, $filename);
}

sub check_pool_old_perfdata_file {
    my ($main_filename, $num_pool) = @_;

    my @files = ();
    for (my $i = 0; $i < $num_pool; $i++) {
        if (-e $main_filename . "_" . $i . ".bckp_read") {
            push @files, $main_filename . "_" . $i . ".bckp_read";    
        }
        if (-e $main_filename . "_" . $i . ".bckp") {
            push @files, $main_filename . "_" . $i . ".bckp";    
        }
    }

    if (-e $main_filename . "_read") {
        push @files, $main_filename . "_read";
    }
    return \@files;
}

sub call_pool_rebuild {
    my ($line, $pool_pipes, $routing_services, $roundrobin_pool_current, $pool_childs, $rebuild_progress, $rebuild_pool_choosen) = @_;

    # Send Info
    my ($method, $host_name, $service_description) = split(/\t/, $line);
    my $pool_choosen;
    if (!defined($routing_services->{$host_name . ";" . $service_description})) {
        my $pool_num = $$roundrobin_pool_current % $pool_childs;
        $$roundrobin_pool_current++;
        $routing_services->{$host_name . ";" . $service_description} = $pool_num;
    }
    $pool_choosen = $routing_services->{$host_name . ";" . $service_description};    
    for ($i = 0; $i < $pool_childs; $i++) {
        if ($i == $pool_choosen) {
            my $fh = $pool_pipes->{$i}->{'writer_two'};
            # It's when you loose a pool. You have to know
            $$rebuild_progress = 1;
            $$rebuild_pool_choosen = $pool_choosen;
            print $fh "REBUILDBEGIN\t$host_name\t$service_description\n";
        } else {
            my $fh = $pool_pipes->{$i}->{'writer_two'};
            print $fh "REBUILDBEGIN\n";
        }
    }
}

sub call_pool_rebuild_finish {
    my ($pool_pipes, $pool_childs, $delete_pipes, $rebuild_progress, $rebuild_pool_choosen) = @_;
    my $fh;

    $$rebuild_progress = 0;
    $$rebuild_pool_choosen = -1;
    for ($i = 0; $i < $pool_childs; $i++) {
        if ($rebuild_pool_choosen != $i) {
            $fh = $pool_pipes->{$i}->{'writer_two'};
            print $fh "REBUILDFINISH\n";
        }
    }
    $fh = $delete_pipes->{'writer_two'};
    print $fh "REBUILDFINISH\n";
}

sub call_pool_rename_clean {
    my ($line, $pool_pipes, $routing_services, $roundrobin_pool_current, $pool_childs) = @_;
    
    my ($method, $old_host_name, $old_service_description, $new_host_name, $new_service_description) = split(/\t/, $line);
    my $pool_choosen;
    if (!defined($routing_services->{$old_host_name . ";" . $old_service_description})) {
        # There is no for the old name. Can go back
        my $fh = $pool_pipes->{$routing_services->{$new_host_name . ";" . $new_service_description}}->{'writer_two'};
        print $fh "RENAMEFINISH\t" . $new_host_name . "\t" . $new_service_description . "\n";
        return ;
    }
    # Send to clean
    my $fh = $pool_pipes->{$routing_services->{$old_host_name . ";" . $old_service_description}}->{'writer_two'};
    print $fh "RENAMECLEAN\t" . $old_host_name . "\t" . $old_service_description . "\t" . $new_host_name . "\t" . $new_service_description . "\n";
}

sub call_pool_rename_finish {
    my ($line, $pool_pipes, $routing_services, $roundrobin_pool_current, $pool_childs) = @_;

    my ($method, $new_host_name, $new_service_description) = split(/\t/, $line);
    if (defined($routing_services->{$new_host_name . ";" . $new_service_description})) {
        my $fh = $pool_pipes->{$routing_services->{$new_host_name . ";" . $new_service_description}}->{'writer_two'};
        print $fh "RENAMEFINISH\t" . $new_host_name . "\t" . $new_service_description . "\n";
        return ;
    }
}

sub call_pool_delete_clean {
    my ($line, $pool_pipes, $routing_services, $roundrobin_pool_current, $pool_childs) = @_;

    my ($method, $host_name, $service_description) = split(/\t/, $line);
    if (!defined($routing_services->{$host_name . ";" . $service_description})) {
        # No cache. so we return
        return ;
    }
    my $fh = $pool_pipes->{$routing_services->{$host_name . ";" . $service_description}}->{'writer_two'};
    print $fh "$line\n";
}

sub can_write {
    my $file = $_[0];
    my $dir = dirname($file);
    
    return 0 if (-d $dir && ! -w $dir);
    return 0 if (-e $file && ! -w $file);
    return 1;
}

1;
