################################################################################
# Copyright 2005-2014 MERETHIS
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
#
####################################################################################

package centreon::script::centreond;

use strict;
use File::Copy;
use File::Path qw(mkpath);
use centreon::script;
use centreon::common::db;
use centreon::common::misc;

use base qw(centreon::script);

my %handlers = ('TERM' => {}, 'HUP' => {}, 'DIE' => {});
use vars qw($centreon_config);

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("centcore",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noroot => 1
    );

    bless $self, $class;

    $self->{timeout} = 5;

    $self->{stop} = 1;
    $self->{reload} = 1;

    $self->set_signal_handlers;

    return $self;
}

sub init {
    my $self = shift;
    $self->SUPER::init();

    $self->{cmdFile} = $self->{centreon_config}->{VarLib} . "/centcore.cmd";
    $self->{cmdDir} = $self->{centreon_config}->{VarLib} . "/centcore/";
    $self->{centreonDir} = $self->{centreon_config}->{CentreonDir};
}

sub set_signal_handlers {
    my $self = shift;

    $SIG{TERM} = \&class_handle_TERM;
    $handlers{TERM}->{$self} = sub { $self->handle_TERM() };

    $SIG{__DIE__} = \&class_handle_DIE;
    $handlers{DIE}->{$self} = sub { $self->handle_DIE($_[0]) };

    $SIG{HUP} = \&class_handle_HUP;
    $handlers{HUP}->{$self} = sub { $self->handle_HUP() };
}

sub class_handle_TERM {
    foreach (keys %{$handlers{TERM}}) {
        &{$handlers{TERM}->{$_}}();
    }
}

sub class_handle_DIE {
    my ($msg) = @_;

    foreach (keys %{$handlers{DIE}}) {
        &{$handlers{DIE}->{$_}}($msg);
    }
}

sub class_handle_HUP {
    foreach (keys %{$handlers{HUP}}) {
        &{$handlers{HUP}->{$_}}();
    }
}

sub handle_HUP {
    my $self = shift;

    $self->{logger}->writeLogInfo("Receiving order to reload...");
    $self->{reload} = 0;
}

sub handle_TERM {
    my $self = shift;
    $self->{logger}->writeLogInfo("$$ Receiving order to stop...");
    $self->{stop} = 0;
}

sub handle_DIE {
    my $self = shift;
    my $msg = shift;

    $self->{logger}->writeLogInfo("Receiving die: $msg");
    $self->{logger}->writeLogInfo("Dont die...");
}

sub reload {
    my $self = shift;
    
    if (defined($self->{log_file})) {
        $self->{logger}->file_mode($self->{log_file});
    }
    $self->{logger}->redirect_output();
    
    # Get Config
    unless (my $return = do $self->{config_file}) {
        $self->{logger}->writeLogError("couldn't parse $self->{config_file}: $@") if $@;
        $self->{logger}->writeLogError("couldn't do $self->{config_file}: $!") unless defined $return;
        $self->{logger}->writeLogError("couldn't run $self->{config_file}") unless $return;
    } else {
        $self->{centreon_config} = $centreon_config;
    }
    
    if ($self->{centreon_config}->{centreon_db} ne $self->{centreon_dbc}->db() ||
        $self->{centreon_config}->{db_host} ne $self->{centreon_dbc}->host() ||
        $self->{centreon_config}->{db_user} ne $self->{centreon_dbc}->user() ||
        $self->{centreon_config}->{db_passwd} ne $self->{centreon_dbc}->password() ||
        $self->{centreon_config}->{db_port} ne $self->{centreon_dbc}->port()) {
        $self->{logger}->writeLogInfo("Database config had been modified");
        $self->{centreon_dbc}->disconnect();
        $self->{centreon_dbc}->db($self->{centreon_config}->{centreon_db});
        $self->{centreon_dbc}->host($self->{centreon_config}->{db_host});
        $self->{centreon_dbc}->user($self->{centreon_config}->{db_user});
        $self->{centreon_dbc}->password($self->{centreon_config}->{db_passwd});
        $self->{centreon_dbc}->port($self->{centreon_config}->{db_port});
    }
}

###########################################################
# Function to move command file on temporary file
#
sub moveCmdFile($){
    my $self = shift;
    my $cmdfile = $_[0];

    if (move($cmdfile, $cmdfile."_read")) {
        return(1);
    } else {
        $self->{logger}->writeLogError("Cannot move $cmdfile to ".$cmdfile."_read");
        return(0);
    }
}

# -------------------
#      Functions 
# -------------------

sub getNagiosConfigurationField($$){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT " . $_[1] . " FROM `cfg_nagios` WHERE `nagios_server_id` = '" . $_[0] . "' AND nagios_activate = '1'");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();
    return $data->{$_[1]};
}

sub getServerConfig($){
    my $self = shift;

    my ($status, $sth) = $self->{centreon_dbc}->query("SELECT * FROM `nagios_server` WHERE `id` = '" . $_[0] . "' AND `activate` = '1' LIMIT 1");
    if ($status == -1) {
        $self->{logger}->writeLogError("Error when getting server properties");
        return undef;
    }
    my $data = $sth->fetchrow_hashref();

    # Get Nagios User
    $data->{'nagios_user'} = $self->getNagiosConfigurationField($_[0], 'nagios_user');
    return $data;
}

################################################
## Send an external command on a remote server.
## Param : id_remote_server, external command
#
sub sendExternalCommand($$) {
    my $self = shift;
    # Init Parameters
    my ($id, $cmd) = @_;
    my ($lerror, $stdout, $cmd2);

    # Get server informations
    my $server_info = $self->getServerConfig($id);
    my $port = checkSSHPort($server_info->{'ssh_port'});

    # Get command file 
    my $command_file = $self->getNagiosConfigurationField($id, "command_file");

    # check if ip address is defined
    if (defined($server_info->{'ip_address'})) {
        $cmd =~ s/\\/\\\\/g;
        if ($server_info->{'localhost'} == 1) {
            my $result = waitPipe($command_file);
            if ($result == 0) {
                $self->{logger}->writeLogInfo("External command on Central Server: ($id) : \"".$cmd."\"");

                $cmd2 = "$self->{echo} \"".$cmd."\" >> ".$command_file;
                ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd2,
                                                                      logger => $self->{logger},
                                                                      timeout => $self->{cmd_timeout}
                    );
                if ($lerror == -1) {
                    $self->{logger}->writeLogError("Could not write into pipe file ".$command_file." on poller ".$id);
                }
            } else {
                $self->{logger}->writeLogError("Cannot write external command on central server : \"".$cmd."\"");
            }
        } else {
            $cmd =~ s/\'/\'\\\'\'/g;
            $self->{logger}->writeLogInfo("External command : ".$server_info->{'ip_address'}." ($id) : \"".$cmd."\"");
            $cmd2 = "$self->{ssh} -q ". $server_info->{'ip_address'} ." -p $port \"$self->{echo} '".$cmd."' >> ".$command_file."\"";
            ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd2,
                                                                  logger => $self->{logger},
                                                                  timeout => $self->{cmd_timeout}
                );
            if ($lerror == -1) {
                $self->{logger}->writeLogError("Could not write into pipe file ".$command_file." on poller ".$id);
            }
        }

        if (defined($stdout) && $stdout){
            $self->{logger}->writeLogInfo("Result : $stdout");
        }

    } else {
        $self->{logger}->writeLogError("Ip address not defined for poller $id");
    }
}

#######################################
## Wait Nagios Pipe availability
#
sub waitPipe($) {
    my ($pipe) = @_;
    my $i = 0;
    while (! -p $pipe) {
        sleep(1);
        $i++;
        if ($i >= 30) {
            return 1;
        }
    }
    return 0;
}

# Store commands in order to group commands to send.
sub storeCommands($$) {
    my $self = shift;
    my ($poller_id, $command) = @_;
    
    if (!defined($self->{commandBuffer}{$poller_id})) {
        $self->{commandBuffer}{$poller_id} = "";
    }
    $self->{commandBuffer}{$poller_id} .= $command . "\n";
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->{logger}->redirect_output();
    $self->{logger}->writeLogInfo("Starting centcore engine...");

    $self->{centreon_dbc} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                      host => $self->{centreon_config}->{db_host},
                                                      port => $self->{centreon_config}->{db_port},
                                                      user => $self->{centreon_config}->{db_user},
                                                      password => $self->{centreon_config}->{db_passwd},
                                                      force => 0,
                                                      logger => $self->{logger});
    $self->checkDebugFlag();

    while ($self->{stop}) {
        if ($self->{reload} == 0) {
            $self->{logger}->writeLogInfo("Reload in progress...");
            $self->reload();
            $self->{reload} = 1;
        }
        # Read Centcore.cmd
        if (-e $self->{cmdFile}) {
            if ($self->moveCmdFile($self->{cmdFile}) && open(FILE, "< $self->{cmdFile}"."_read")) {
                while (<FILE>){
                    $self->parseRequest($_);
                }
                my $poller;
                foreach $poller (keys(%{$self->{commandBuffer}})) {
                    if (length($self->{commandBuffer}{$poller}) != 0) {
                        $self->sendExternalCommand($poller, $self->{commandBuffer}{$poller});
                        $self->{commandBuffer}{$poller} = "";
                    }
                }
                close(FILE);
                $self->{logger}->writeLogError("Error When removing ".$self->{cmdFile}."_read file : $!") if (!unlink($self->{cmdFile}."_read"));
            }
        }

        # Read Centcore Directory
        if (-d $self->{cmdDir}) {
            opendir(my $dh, $self->{cmdDir});
            while (my $file = readdir($dh)) {
                if ($file ne "." && $file ne ".." && $file ne "") {
                    if ($self->moveCmdFile($self->{cmdDir} . $file) && open(FILE, "< ". $self->{cmdDir} . $file . "_read")) {
                        while (<FILE>){
                            $self->parseRequest($_);
                        }
                        my $poller;
                        foreach $poller (keys(%{$self->{commandBuffer}})) {
                            if (length($self->{commandBuffer}{$poller}) != 0) {
                                $self->sendExternalCommand($poller, $self->{commandBuffer}{$poller});
                                $self->{commandBuffer}{$poller} = "";
                            }
                        }
                        close(FILE);
                        $self->{logger}->writeLogError("Error When removing ".$self->{cmdDir}.$file."_read file : $!") if (!unlink($self->{cmdDir}.$file."_read"));
                    }
                }
            }
            closedir $dh;
        }

        if (defined($self->{timeSyncPerf}) && $self->{timeSyncPerf}) {
            $self->{difTime} = time() - $self->{timeSyncPerf};
        }

        sleep(1);
    }

    $self->{logger}->writeLogInfo("Centcore stop...");
}

1;

__END__

