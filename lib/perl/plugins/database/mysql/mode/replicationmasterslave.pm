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

package database::mysql::mode::replicationmasterslave;

use base qw(centreon::plugins::mode);

use strict;
use warnings;

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    
    $self->{version} = '1.0';
    $options{options}->add_options(arguments =>
                                { 
                                  "warning:s"               => { name => 'warning', },
                                  "critical:s"              => { name => 'critical', },
                                });

    return $self;
}

sub check_options {
    my ($self, %options) = @_;
    $self->SUPER::init(%options);

    if (($self->{perfdata}->threshold_validate(label => 'warning', value => $self->{option_results}->{warning})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong warning threshold '" . $self->{warn1} . "'.");
       $self->{output}->option_exit();
    }
    if (($self->{perfdata}->threshold_validate(label => 'critical', value => $self->{option_results}->{critical})) == 0) {
       $self->{output}->add_option_msg(short_msg => "Wrong critical threshold '" . $self->{critical} . "'.");
       $self->{output}->option_exit();
    }
}

sub run {
    my ($self, %options) = @_;
    # $options{sql} = sqlmode object
    
    if (ref($options{sql}) ne 'ARRAY') {
        $self->{output}->add_option_msg(short_msg => "Need to use --multiple options.");
        $self->{output}->option_exit();
    }
    if (scalar(@{$options{sql}}) < 2) {
        $self->{output}->add_option_msg(short_msg => "Need to specify two MySQL Server.");
        $self->{output}->option_exit();
    }

    my ($sql_one, $sql_two) = @{$options{sql}};
    my ($slave_status, $slave_status_error) = (0, "");
    my ($position_status, $position_status_error) = (0, "");
    my ($connection_status_name_srv1, $connection_status_name_srv2) = ($sql_one->get_id(), $sql_two->get_id());
    my ($master_save, $slave_save);
    
    my ($exit1, $msg_error1) = $sql_one->connect(dontquit => 1);
    my ($exit2, $msg_error2) = $sql_two->connect(dontquit => 1);
    $self->{output}->output_add(severity => 'OK',
                                short_msg => "No problems. Replication is ok.");
    if ($exit1 == -1) {
        $self->{output}->output_add(severity => 'CRITICAL',
                                    short_msg => "Connection Status '" . $sql_one->get_id() . "': " . $msg_error1);
    } else {
        $self->{output}->output_add(long_msg => "Connection Status '" . $sql_one->get_id() . "' [OK]");
    }
    if ($exit2 == -1) {
        $self->{output}->output_add(severity => 'CRITICAL',
                                    short_msg => "Connection Status '" . $sql_two->get_id() . "': " . $msg_error2);
    } else {
        $self->{output}->output_add(long_msg => "Connection Status '" . $sql_two->get_id() . "' [OK]");
    }
    
    #####
    # Find SLAVE
    #####
    my ($total_srv1, $total_srv2);
    my ($last_error1, $last_error2);

    my ($io_thread_status_srv1, $sql_thread_status_srv1);
    if ($exit1 != -1) {
        $sql_one->query(query => q{
            SHOW SLAVE STATUS
        });
        my $result = $sql_one->fetchrow_hashref();
        my $slave_io_running = $result->{Slave_IO_Running};
        my $slave_sql_running = $result->{Slave_SQL_Running};
        $last_error1 = $result->{Last_Error};
        
        if (defined($slave_io_running) && $slave_io_running =~ /^yes$/i) {
            $io_thread_status_srv1 = 0;
        } else {
            $io_thread_status_srv1 = 1;
        }
        if (defined($slave_sql_running) && $slave_sql_running =~ /^yes$/i) {
            $sql_thread_status_srv1 = 0;
        } else {
            $sql_thread_status_srv1 = 1;
        }
    } else {
        $io_thread_status_srv1 = 100;
        $sql_thread_status_srv1 = 100;
    }

    my ($io_thread_status_srv2, $sql_thread_status_srv2);
    if ($exit2 != -1) {
        $sql_two->query(query => q{
            SHOW SLAVE STATUS
        });
        my $result = $sql_two->fetchrow_hashref();
        my $slave_io_running = $result->{Slave_IO_Running};
        my $slave_sql_running = $result->{Slave_SQL_Running};
        $last_error2 = $result->{Last_Error};
        
        if (defined($slave_io_running) && $slave_io_running =~ /^yes$/i) {
            $io_thread_status_srv2 = 0;
        } else {
            $io_thread_status_srv2 = 1;
        }
        if (defined($slave_sql_running) && $slave_sql_running =~ /^yes$/i) {
            $sql_thread_status_srv2 = 0;
        } else {
            $sql_thread_status_srv2 = 1;
        }
    } else {
        $io_thread_status_srv2 = 100;
        $sql_thread_status_srv2 = 100;
    }

    $total_srv1 = $io_thread_status_srv1 + $sql_thread_status_srv1;
    $total_srv2 = $io_thread_status_srv2 + $sql_thread_status_srv2;
    
    # Check If there is two slave
    if ($total_srv1 < 2 && $total_srv2 < 2) {
        $slave_status = 1;
        $slave_status_error = "Two slave. Need to have only one.";
    } else {
        # Check if a thread is down
        if ($total_srv1 == 1) {
            $slave_status = -1;
            $slave_status_error = "A Replication thread is down on '" . $sql_one->get_id() . "'.";
            if ($sql_thread_status_srv1 != 0) {
                if (defined($last_error1) && $last_error1 ne "") {
                    $slave_status = 1;
                    $slave_status_error .= " SQL Thread is stopped because of an error (error='" . $last_error1 . "').";
                }
            }
        }
        if ($total_srv2 == 1) {
            $slave_status = -1;
            $slave_status_error = "A Replication thread is down on '" . $sql_two->get_id() . "'.";
            if ($sql_thread_status_srv2 != 0) {
                if (defined($last_error2) && $last_error2 ne "") {
                    $slave_status = 1;
                    $slave_status_error .= " SQL Thread is stopped because of an error (error='" . $last_error2 . "').";
                }
            }
        }
        
        # Check if we need to SKIP
        if ($io_thread_status_srv1 == 100) {
            $slave_status = -1;
            $slave_status_error .= " Skip check on '" . $sql_one->get_id() . "'.";
        }
        if ($io_thread_status_srv2 == 100) {
            $slave_status = -1;
            $slave_status_error .= " Skip check on '" . $sql_two->get_id() . "'.";
        }
        
        # Save Slave
        if ($total_srv1 < 2) {
            $slave_save = $sql_one;
            $master_save = $sql_two;
        }
        if ($total_srv2 < 2) {
            $slave_save = $sql_two;
            $master_save = $sql_one;
        }

        if ($total_srv2 > 1 && $total_srv1 > 1) {
            $slave_status = 1;
            $slave_status_error .= " No slave (maybe because we cannot check a server).";
        }
    }
    
    ####
    # Check Slave position
    ####    
    if (!defined($slave_save)) {
        $position_status = -2;
        $position_status_error = "Skip because we can't identify a unique slave.";
    } else {
        if ($master_save->get_id() eq $connection_status_name_srv1 && $exit1 == -1) {
            $position_status = -1;
            $position_status_error = "Can't get master position on '" . $master_save->get_id() . "'.";
        } elsif ($master_save->get_id() eq $connection_status_name_srv2 && $exit2 == -1) {
            $position_status = -1;
            $position_status_error = "Can't get master position on '" . $master_save->get_id() . "'.";
        } else {
            # Get Master Position
            $master_save->query(query => q{
                SHOW MASTER STATUS
            });
            my $result = $master_save->fetchrow_hashref();
            my $master_file = $result->{File};
            my $master_position = $result->{Position};
            
            $slave_save->query(query => q{
                SHOW SLAVE STATUS
            });
            my $result2 = $slave_save->fetchrow_hashref();
            my $slave_file = $result2->{Master_Log_File}; # 'Master_Log_File'
            my $slave_position = $result2->{Read_Master_Log_Pos}; # 'Read_Master_Log_Pos'
            my $num_sec_lates = $result2->{Seconds_Behind_Master};

            my $exit_code_sec = $self->{perfdata}->threshold_check(value => $num_sec_lates, threshold => [ { label => 'critical', 'exit_litteral' => 'critical' }, { label => 'warning', exit_litteral => 'warning' } ]);
            if (!$self->{output}->is_status(value => $exit_code_sec, compare => 'ok', litteral => 1)) {
                $self->{output}->output_add(severity => $exit_code_sec,
                                            short_msg => sprintf("Slave has %d seconds latency behind master", $num_sec_lates));
            }
            $self->{output}->perfdata_add(label => 'slave_latency', unit => 's',
                                          value => $num_sec_lates,
                                          warning => $self->{perfdata}->get_perfdata_for_output(label => 'warning'),
                                          critical => $self->{perfdata}->get_perfdata_for_output(label => 'critical'),
                                          min => 0);
            
            my $slave_sql_thread_ko = 1;
            my $slave_sql_thread_warning = 1;
            my $slave_sql_thread_ok = 1;
            
            $slave_save->query(query => q{
                SHOW FULL PROCESSLIST
            });
            while ((my $row = $slave_save->fetchrow_hashref())) {
                my $state = $row->{State};
                $slave_sql_thread_ko = 0 if (defined($state) && $state =~ /^(Waiting to reconnect after a failed binlog dump request|Connecting to master|Reconnecting after a failed binlog dump request|Waiting to reconnect after a failed master event read|Waiting for the slave SQL thread to free enough relay log space)$/i);
                $slave_sql_thread_warning = 0 if (defined($state) && $state =~ /^Waiting for the next event in relay log|Reading event from the relay log$/i);
                $slave_sql_thread_ok = 0 if (defined($state) && $state =~ /^Has read all relay log; waiting for the slave I\/O thread to update it$/i);
            }
            
            if ($slave_sql_thread_ko == 0) {
                $position_status = 1;
                $position_status_error .= " Slave replication has connection issue with the master.";
            } elsif (($master_file ne $slave_file || $master_position != $slave_position) && $slave_sql_thread_warning == 0) {
                $position_status = -1;
                $position_status_error .= " Slave replication is late but it's progressing..";
            } elsif (($master_file ne $slave_file || $master_position != $slave_position) && $slave_sql_thread_ok == 0) {
                $position_status = -1;
                $position_status_error .= " Slave replication is late but it's progressing..";
            }
        }
    }
    


    $self->replication_add($slave_status, "Slave Thread Status", $slave_status_error);
	$self->replication_add($position_status, "Position Status", $position_status_error);
    
    $self->{output}->display();
    $self->{output}->exit();
}

sub replication_add {
	my ($self, $lstate, $str_display, $lerr) = @_;
	my $status;
    my $status_msg;

	if ($lstate == 0) {
		$status = 'OK';
	} elsif ($lstate == -1) {
		$status = 'WARNING';
	} elsif ($lstate == -2) {
		$status = 'CRITICAL';
        $status_msg = 'SKIP';
	} else {
		$status = 'CRITICAL';
	}

    my $output;
	if (defined($lerr) && $lerr ne "") {
		$output = $str_display . " [" . (defined($status_msg) ? $status_msg : $status) . "] [" . $lerr . "]";
	} else {
		$output = $str_display . " [" . (defined($status_msg) ? $status_msg : $status) . "]";
	}
    if (!$self->{output}->is_status(value => $status, compare => 'ok', litteral => 1)) {
        $self->{output}->output_add(severity => $status,
                                    short_msg => $output);
    }
    
	$self->{output}->output_add(long_msg => $output);
}

1;

__END__

=head1 MODE

Check MySQL replication master/slave (need to use --multiple).

=over 8

=item B<--warning>

Threshold warning in seconds (slave latency).

=item B<--critical>

Threshold critical in seconds (slave latency).

=back

=cut
