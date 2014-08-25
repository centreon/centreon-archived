#!/usr/bin/perl

use strict;
use warnings;

centreon::script::email_status->new()->run();

package centreon::script::email_status;

use strict;
use warnings;
use POSIX;
use centreon::common::misc;
use File::Temp qw(tempfile);

use base qw(centreon::script);

#
# Need to put some "\n" at end. There is an email limit of 1000 characters for a line
# Otherwise some '\n' is put each 1000 character block.
#

sub new {
    my $class = shift;
    my $self = $class->SUPER::new("email_status",
        centreon_db_conn => 0,
        centstorage_db_conn => 0,
        noconfig => 0
    );

    bless $self, $class;
    
    $self->{color_host_states} = {
        0 => { name => 'UP', color => '#A7FAAE'},
        1 => { name => 'DOWN', color => '#FFBCBC'},
        2 => { name => 'UNREACHABLE', color => '#04ABFA'},
    };
    $self->{color_service_states} = {
        0 => {name => 'OK', color => '#A7FAAE'},
        1 => {name => 'WARNING', color => '#F8EEA4'},
        2 => {name => 'CRITICAL', color => '#FFBCBC'},
        3 => {name => 'UNKNOWN', color => '#E5E5E5'},
    };
    
    # Some styles (html mail is a mess. Cannot use css section :(
    $self->{host_title_style} = 'style="width: 100%; text-align: center; margin-bottom: 10px; text-transform:uppercase; font: 18px Arial, Helvetica, sans-serif; font-weight: bold;"';
    $self->{table_host} = 'style="border-collapse: collapse; border: 1px solid black;"';
    $self->{cell_title_host} = 'style="background-color: #D2F5BB; border: 1px solid black; text-align: center; padding: 10px; text-transform:uppercase; font-weight:bold;"';
    
    $self->{service_title_style} = 'style="width: 100%; text-align: center; margin-bottom: 10px; margin-top: 30px; text-transform:uppercase; font: 18px Arial, Helvetica, sans-serif; font-weight: bold;"';
    $self->{table_service} = 'style="border-collapse: collapse; border: 1px solid black;"';
    $self->{cell_title_service} = 'style="background-color: #D2F5BB; border: 1px solid black; text-align: center; padding: 10px; text-transform:uppercase; font-weight:bold;"';
    
    $self->{generated_style} = 'style="margin-top: 10px; font: 10px Arial, Helvetica, sans-serif;"';
    
    $self->{styles} = {
                        cell_host_host => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_criticity_host => 'style="border-bottom: 1px solid black; text-align: center; padding: 5px;"',
                        cell_state_host => 'style="border-bottom: 1px solid black; text-align: center; padding: 5px;"',
                        cell_duration_host => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_output_host => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_noerror_host => 'style="border-bottom: 1px solid black; text-align: center; padding: 5px;"',
                      
                        cell_host_service => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_service_service => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_criticity_service => 'style="border-bottom: 1px solid black; text-align: center; padding: 5px;"',
                        cell_state_service => 'style="border-bottom: 1px solid black; text-align: center; padding: 5px;"',
                        cell_duration_service => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_output_service => 'style="border-bottom: 1px solid black; padding: 5px;"',
                        cell_noerror_service => 'style="border-bottom: 1px solid black; text-align: center; padding: 5px;"',
                      };

    $self->{host_add_column} = undef;
    $self->{host_state} = "('0', '4')";
    $self->{host_ack} = "('0')";
    $self->{host_downtime} = "('0')";
    $self->{default_host_order} = undef;
    $self->{host_order} = 'FIELD(state, 1, 2, 0), last_hard_state_change';
    $self->{host_limit} = '50';
    
    $self->{service_add_column} = undef;
    $self->{service_state} = "('0', '4')";
    $self->{service_ack} = "('0')";
    $self->{service_downtime} = "('0')";
    $self->{default_service_order} = undef;
    $self->{service_order} = 'FIELD(services.state, 2, 1, 3, 0), services.last_hard_state_change';
    $self->{service_limit} = '50';
    
    $self->{output_limit} = 255;
    
    $self->{subject} = 'Current monitoring problems';
    $self->{email_html} = '';
    $self->{email_from} = 'centreon';
    
    $self->{emails} = undef;
    $self->{hostgroup_filters} = undef;
    
    $self->{criticity} = undef;
    $self->{criticity_host_get_sql} = '';
    $self->{criticity_host_join_sql} = '';
    
    $self->{criticity_service_get_sql} = '';
    $self->{criticity_service_join_sql} = '';
    
    $self->add_options(
        "email:s@"          => \$self->{emails},
        "hostgroup:s@"      => \$self->{hostgroup_filters},
        "host-state:s"      => \$self->{host_state},
        "host-order:s"      => \$self->{default_host_order},
        "host-limit:s"      => \$self->{host_limit},
        "host-add-column:s@"    => \$self->{default_host_add_column},
        "service-add-column:s@" => \$self->{default_service_add_column},
        "service-state:s"   => \$self->{service_state},
        "service-order:s"   => \$self->{default_service_order},
        "service-limit:s"   => \$self->{service_limit},
        "output-limit:s"    => \$self->{output_limit},
        "criticity:s"       => \$self->{criticity},
    );
    
    return $self;
}

sub column_option {
    my ($self, %options) = @_;

    $self->{host_add_columns} = [];
    $self->{host_add_column_sql} = '';
    if (defined($self->{default_host_add_column})) {
        foreach my $entry (@{$self->{default_host_add_column}}) {
            my ($name, $sql_name, $get) = split /,/, $entry;
            if (defined($name) && defined($sql_name) && defined($get)) {
                $self->{host_add_column_sql} .= ', ' . $sql_name . " ";
                push @{$self->{host_add_columns}}, { name => $name, sql_name => $sql_name, get => $get };
            }
        }
    }
    
    $self->{service_add_columns} = [];
    $self->{service_add_column_sql} = '';
    if (defined($self->{default_service_add_column})) {
        foreach my $entry (@{$self->{default_service_add_column}}) {
            my ($name, $sql_name, $get) = split /,/, $entry;
            if (defined($name) && defined($sql_name) && defined($get)) {
                $self->{service_add_column_sql} .= ', ' . $sql_name . " ";
                push @{$self->{service_add_columns}}, { name => $name, sql_name => $sql_name, get => $get } ;
            }
        }
    }
}

sub criticity_option {
    my ($self, %options) = @_;
    
    if (defined($self->{default_host_order})) {
        $self->{host_order} = $self->{default_host_order};
    }
    if (defined($self->{default_service_order})) {
        $self->{service_order} = $self->{default_service_order};
    }
    
    if (defined($self->{criticity})) {
        $self->{criticity_host_get_sql} = ", cvhost.value as cvhvalue ";
        $self->{criticity_host_join_sql} = " LEFT JOIN customvariables cvhost ON cvhost.host_id = hosts.host_id AND cvhost.name = 'CRITICALITY_LEVEL' AND service_id IS NULL ";
    
        $self->{criticity_service_get_sql} = ", cvservice.value as cvsvalue ";
        $self->{criticity_service_join_sql} = " LEFT JOIN customvariables cvservice INNER JOIN hosts hosts2 ON cvservice.host_id = hosts2.host_id ON cvservice.name = 'CRITICALITY_LEVEL' AND cvservice.service_id = services.service_id ";
   
        if (!defined($self->{default_host_order})) {
            $self->{host_order} = 'ISNULL(cvhost.value), state, last_hard_state_change';
        }
        if (!defined($self->{default_service_order})) {
            $self->{service_order} = 'ISNULL(cvhost.value), ISNULL(cvservice.value), FIELD(services.state, 2, 1, 3), services.last_hard_state_change';
        }
    }
}

sub get_duration {
    my ($self, $time) = @_;
    my ($str, $str_append) = ('', '');
    my $periods = [
                    { unit => 'y', value => 31556926 },
                    { unit => 'M', value => 2629743 },
                    { unit => 'w', value => 604800 },
                    { unit => 'd', value => 86400 },
                    { unit => 'h', value => 3600 },
                    { unit => 'm', value => 60 },
                    { unit => 's', value => 1 },
    ];

    foreach (@$periods) {
        my $count = floor($time / $_->{value});

        next if ($count == 0);
        $str .= $str_append . $count . $_->{unit};
        $time = $time % $_->{value};
        $str_append = ' ';
    }

    return $str;
}

sub manage_hosts {
    my ($self, %options) = @_;
    my ($hg_name, $hg_from_sql, $hg_where_sql) = ('', '', '');
    
    if (defined($options{hostgroup})) {
        $hg_name = ' (hostgroup ' . $options{hostgroup} . ')';
        $hg_from_sql = ' , hostgroups, hosts_hostgroups ';
        $hg_where_sql = ' hostgroups.name = ' . $self->{centreon_db_centstorage}->quote($options{hostgroup}) . 
                        " AND hostgroups.enabled = '1' AND hostgroups.hostgroup_id = hosts_hostgroups.hostgroup_id AND hosts_hostgroups.host_id = hosts.host_id AND ";
    }
    
    # Get Hosts down (not ack, not down)
    
    $self->{email_html} .= '<div ' . $self->{host_title_style} . '>Unhandled Host problems' . $hg_name . '</div>';
    my ($status, $sth) = $self->{centreon_db_centstorage}->query(
            "SELECT hosts.name as hostname, state, output, last_hard_state_change " . $self->{host_add_column_sql} . " " . $self->{criticity_host_get_sql} . " FROM hosts $self->{criticity_host_join_sql} $hg_from_sql 
            WHERE $hg_where_sql state NOT IN " . $self->{host_state} . " and hosts.enabled = '1' and acknowledged IN " . $self->{host_ack} . 
                  " and scheduled_downtime_depth IN " . $self->{host_downtime} . " and state_type = '1' ORDER BY " . $self->{host_order} . " LIMIT 0, " . $self->{host_limit});
    if ($status == -1) {
        $self->{email_html} .= '<span style="color: red">Cannot retrieve host informations.</span>';
        $self->{logger}->writeLogError("Cannot retrieve host informations.");
        return;
    }
    
    my $total = 0;
    $self->{email_html} .= '<table cellpading="0" cellspacing="0" ' . $self->{table_host} . '><tr>';
    $self->{email_html} .= '<td ' . $self->{cell_title_host} . '>Host</td>' . "\n";
    if (defined($self->{criticity})) {
        $self->{email_html} .= '<td ' . $self->{cell_title_host} . '>Criticity</td>' . "\n";
    }
    $self->{email_html} .= '<td ' . $self->{cell_title_host} . '>State</td><td ' . $self->{cell_title_host} . '>Duration</td><td ' . $self->{cell_title_host} . '>Output</td>' . "\n";
    foreach (@{$self->{host_add_columns}}) {
        $self->{email_html} .= '<td ' . $self->{cell_title_host} . '>' . $_->{name} . '</td>';
    }
    $self->{email_html} .= '</tr>';
    while (my $data = $sth->fetchrow_hashref()) {
        chomp $data->{output};
        $data->{output} = substr($data->{output}, 0, $self->{output_limit});
        $self->{email_html} .= '<tr>';
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_host_host} . ' >' . $data->{hostname} . '</td>' . "\n";
        if (defined($self->{criticity})) {
            my $criticity = '-';
            $criticity = $data->{cvhvalue} if (defined($data->{cvhvalue}));
            $self->{email_html} .= '<td ' . $self->{styles}->{cell_criticity_host} . ' >' . $criticity . '</td>' . "\n";
        }
        $self->{email_html} .= '<td bgcolor="' . $self->{color_host_states}->{$data->{state}}->{color} . '" ' . $self->{styles}->{cell_state_host} . ' >' . $self->{color_host_states}->{$data->{state}}->{name}  . '</td>' . "\n";
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_duration_host} . ' >' . $self->get_duration(time() - $data->{last_hard_state_change}) . '</td>' . "\n";
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_output_host} . ' >' . $data->{output} . '</td>' . "\n";
        foreach (@{$self->{host_add_columns}}) {
            $self->{email_html} .= '<td ' . $self->{styles}->{cell_output_host} . '>' . $data->{$_->{get}} . "\n</td>";
        }
        $self->{email_html} .= '</tr>';
        $total++;
    }
    
    if ($total == 0) {
        my $colspan = 4;
        $colspan = 5 if (defined($self->{criticity}));
        $colspan += scalar(@{$self->{host_add_columns}});
        $self->{email_html} .= '<tr>';
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_noerror_host} . ' colspan="' . $colspan . '">No errors found</td>';
        $self->{email_html} .= '</tr>';
    }
    
    $self->{email_html} .= '</table>' . "\n";
}

sub manage_services {
    my ($self, %options) = @_;
    
    my ($hg_name, $hg_from_sql, $hg_where_sql) = ('', '', '');
    
    if (defined($options{hostgroup})) {
        $hg_name = ' (hostgroup ' . $options{hostgroup} . ')';
        $hg_from_sql = ' , hostgroups, hosts_hostgroups ';
        $hg_where_sql = ' hostgroups.name = ' . $self->{centreon_db_centstorage}->quote($options{hostgroup}) . 
                        " AND hostgroups.enabled = '1' AND hostgroups.hostgroup_id = hosts_hostgroups.hostgroup_id AND hosts_hostgroups.host_id = hosts.host_id AND ";
    }
    
    # Get Hosts down (not ack, not down)
    
    $self->{email_html} .= '<div ' . $self->{service_title_style} . '>Unhandled Service problems' . $hg_name . '</div>';
    my ($status, $sth) = $self->{centreon_db_centstorage}->query("SELECT hosts.name as hostname, services.description as servicename, services.state as service_state, services.output as service_output, services.last_hard_state_change as service_last_hard_state_change " . $self->{service_add_column_sql} . " " . $self->{criticity_host_get_sql} . $self->{criticity_service_get_sql} . 
            " FROM hosts $self->{criticity_host_join_sql}, services $self->{criticity_service_join_sql} $hg_from_sql  
            WHERE $hg_where_sql hosts.state = '0' and hosts.enabled = '1' and hosts.acknowledged IN " . $self->{host_ack} . 
                  " and hosts.scheduled_downtime_depth IN " . $self->{host_downtime} . 
                  " and hosts.host_id = services.host_id " .
                  " and services.state NOT IN " . $self->{service_state} .
                  " and services.state_type = '1'" .
                  " and services.enabled = '1'" . 
                  " and services.acknowledged IN " . $self->{service_ack} . 
                  " and services.scheduled_downtime_depth IN " . $self->{service_downtime} . 
                  " ORDER BY " . $self->{service_order} . " LIMIT 0, " . $self->{service_limit});
    if ($status == -1) {
        $self->{email_html} .= '<span style="color: red">Cannot retrieve service informations.</span>';
        $self->{logger}->writeLogError("Cannot retrieve service informations.");
        return;
    }
    
    my $total = 0;
    $self->{email_html} .= '<table cellpading="0" cellspacing="0" ' . $self->{table_service} . '><tr>' . "\n";
    $self->{email_html} .= '<td ' . $self->{cell_title_service} . '>Host</td><td ' . $self->{cell_title_service} . '>Service</td>' . "\n";
    if (defined($self->{criticity})) {
        $self->{email_html} .= '<td ' . $self->{cell_title_service} . '>Criticity</td>' . "\n";
    }
    $self->{email_html} .= '<td ' . $self->{cell_title_service} . '>State</td><td ' . $self->{cell_title_service} . '>Duration</td><td ' . $self->{cell_title_service} . '>Output</td>' . "\n";
    foreach (@{$self->{service_add_columns}}) {
        $self->{email_html} .= '<td ' . $self->{cell_title_service} . '>' . $_->{name} . '</td>' . "\n";
    }
    $self->{email_html} .= "</tr>";
    while (my $data = $sth->fetchrow_hashref()) {
        chomp $data->{service_output};
        $data->{service_output} =~ s/\\n/\x{0a}<br\/>/g;
        $data->{service_output} = substr($data->{service_output}, 0, $self->{output_limit});
        $self->{email_html} .= '<tr>';
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_host_service} . ' >' . $data->{hostname} . '</td>' . "\n";
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_service_service} . ' >' . $data->{servicename} . '</td>' . "\n";
        if (defined($self->{criticity})) {
            my ($ch, $cs) = ('-', '-');
            $ch = $data->{cvhvalue} if (defined($data->{cvhvalue}));
            $cs = $data->{cvsvalue} if (defined($data->{cvsvalue}));
            $self->{email_html} .= '<td ' . $self->{styles}->{cell_criticity_service} . ' >' . $ch . '/' . $cs . '</td>' . "\n";
        }
        $self->{email_html} .= '<td bgcolor="' . $self->{color_service_states}->{$data->{service_state}}->{color} . '" ' . $self->{styles}->{cell_state_service} . ' >' . $self->{color_service_states}->{$data->{service_state}}->{name}  . '</td>' . "\n";
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_duration_service} . ' >' . $self->get_duration(time() - $data->{service_last_hard_state_change}) . '</td>' . "\n";
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_output_service} . ' >' . $data->{service_output} . '</td>' . "\n";
        foreach (@{$self->{service_add_columns}}) {
            $self->{email_html} .= '<td ' . $self->{styles}->{cell_output_service} . '>' . $data->{$_->{get}} . "\n</td>";
        }
        $self->{email_html} .= '</tr>';
        $total++;
    }
    
    if ($total == 0) {
        my $colspan = 5;
        $colspan = 6 if (defined($self->{criticity}));
        $colspan += scalar(@{$self->{service_add_columns}});
        $self->{email_html} .= '<tr>';
        $self->{email_html} .= '<td ' . $self->{styles}->{cell_noerror_service} . ' colspan="' . $colspan . '">No errors found</td>';
        $self->{email_html} .= '</tr>';
    }
    
    $self->{email_html} .= '</table>' . "\n";
    
    my $datestring = scalar(localtime());
    $self->{email_html} .= '<div ' . $self->{generated_style} . '>Generated time: ' . $datestring .'</div>';
}

sub send_email {
    my ($self, %options) = @_;
    
    my ($hg_name, $hg_from_sql, $hg_where_sql) = ('', '', '');
    
    if (defined($options{hostgroup})) {
        $hg_name = ' (' . $options{hostgroup} . ')';
    }
    
    foreach my $recipient (@{$self->{emails}}) {
        my ($handle, $path) = tempfile("email_status\_XXXXX", UNLINK => 1, TMPDIR => 1);
        print $handle 'From: ' . $self->{email_from} . '
To: ' . $recipient . '
Subject: ' . $self->{subject} . $hg_name . '
MIME-Version: 1.0
Content-type: text/html; charset=utf-8

<html>
<body>
' . $self->{email_html} . '
</body>
</html>';
    
        my $cmd = 'cat ' . File::Spec->rel2abs($path) . ' | sendmail -f "' . $self->{email_from} . '" ' . $recipient . ' 2>&1';

        my ($lerror, $stdout) = centreon::common::misc::backtick(command => $cmd,
                                                                 logger => $self->{logger},
                                                                 timeout => 5,
                                                                 wait_exit => 1
                                                                );
        if ($lerror != 0) {
            $self->{logger}->writeLogError("Problem on mail command: " . $stdout);
        } else {
            $self->{logger}->writeLogInfo("Email sent for user " . $recipient);
        }
    }
}

sub run {
    my $self = shift;

    $self->SUPER::run();
    $self->criticity_option();
    $self->column_option();
    
    if (!defined($self->{emails})) {
        $self->{logger}->writeLogError("Need to specify at least one --email option");
        exit(1);
    }
    
    $self->{centreon_db_centreon} = centreon::common::db->new(db => $self->{centreon_config}->{centreon_db},
                                                     host => $self->{centreon_config}->{db_host},
                                                     port => $self->{centreon_config}->{db_port},
                                                     user => $self->{centreon_config}->{db_user},
                                                     password => $self->{centreon_config}->{db_passwd},
                                                     force => 0,
                                                     logger => $self->{logger});
    $self->{centreon_db_centreon}->connect();
    $self->{centreon_db_centstorage} = centreon::common::db->new(db => $self->{centreon_config}->{centstorage_db},
                                                        host => $self->{centreon_config}->{db_host},
                                                        port => $self->{centreon_config}->{db_port},
                                                        user => $self->{centreon_config}->{db_user},
                                                        password => $self->{centreon_config}->{db_passwd},
                                                        force => 0,
                                                        logger => $self->{logger});
    $self->{centreon_db_centstorage}->connect();
    
    my ($status, $sth) = $self->{centreon_db_centreon}->query("SELECT `value` FROM `options` WHERE `key` = 'broker'");

    if ($status == -1) {
        $self->{logger}->writeLogError("Cannot get broker information");
        exit(1);
    }
    if ($sth->fetchrow_hashref()->{value} ne "broker") {
        $self->{logger}->writeLogError("This script is only suitable for centreon-broker");
        exit(1);
    }

    if (defined($self->{hostgroup_filters})) {
        foreach (@{$self->{hostgroup_filters}}) {
            $self->{logger}->writeLogInfo("Processing hostgroup " . $_);
            $self->manage_hosts(hostgroup => $_);
            $self->manage_services(hostgroup => $_);
            $self->send_email(hostgroup => $_);
            $self->{email_html} = '';
        }
    } else {
        $self->manage_hosts();
        $self->manage_services();
        $self->send_email();
    }
}

__END__

=head1 NAME

email_status.pl - command to send current status to email

=head1 SYNOPSIS

email_status.pl [options]

=head1 OPTIONS

=over 8

=item B<--config>

Specify the path to the main configuration file (default: /etc/centreon/conf.pm).

=item B<--help>

Print a brief help message and exits.

=item B<--email>

Specify recipient emails (multiple).

=item B<--hostgroup>

Specify hotgroup filters (multiple).

=item B<--host-state>

Specify which host problem states to retrieve (Default: '(0, 4)').

=item B<--host-order>

Specify how the host problem table is ordered (Default: 'state, last_hard_state_change').

=item B<--host-limit>

Specify how much row to retrieve for the host problem table (Default: '50').

=item B<--service-state>

Specify which service problem states to retrieve (Default: '(0, 4)').

=item B<--service-order>

Specify how the service problem table is ordered (Default: 'FIELD(services.state, 2, 1, 3), services.last_hard_state_change').

=item B<--service-limit>

Specify how much row to retrieve for the service problem table (Default: '50').

=item B<--output-limit>

Specify how much characters limit for outputs (host and service) (Default: '255').

=item B<--criticity>

Add criticity column (also default ORDER BY is changed).
By default, is ordered by criticity ASC. If you want 'DESC', set:
--service-order='-cvhost.value DESC, -cvservice.value DESC, FIELD(services.state, 2, 1, 3), services.last_hard_state_change'
--host-order='-cvhost.value DESC, state, last_hard_state_change'

=back

=head1 DESCRIPTION

B<email_status.pl>.

=cut
