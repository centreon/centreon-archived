package database::mysql::plugin;

use strict;
use warnings;
use base qw(centreon::plugins::script_sql);

sub new {
    my ($class, %options) = @_;
    $options{options}->add_options(
                                   arguments => {
                                                'host:s' => { name => 'db_host' },
                                                'port:s' => { name => 'db_port' },
                                                }
                                  );    
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    # $options->{options} = options object

    $self->{version} = '0.1';
    %{$self->{modes}} = (
                         'connection-time' => 'database::mysql::mode::connectiontime',
                         'databases-size' => 'database::mysql::mode::databasessize',
                         'queries' => 'database::mysql::mode::queries',
                         'slow-queries' => 'database::mysql::mode::slowqueries',
                         'threads-connected' => 'database::mysql::mode::threadsconnected',
                         'uptime' => 'database::mysql::mode::uptime',
                         'open-files' => 'database::mysql::mode::openfiles',
                         'innodb-bufferpool-hitrate' => 'database::mysql::mode::innodbbufferpoolhitrate',
                         'myisam-keycache-hitrate' => 'database::mysql::mode::myisamkeycachehitrate',
                         );
    $self->{sql_modes}{mysqlcmd} = 'database::mysql::mysqlcmd';

    if (defined($self->{db_host}) && $self->{db_host} ne '') {
        $self->{sqldefault}->{dbi} = { data_source => 'mysql:host=' . $self->{db_host} };
        $self->{sqldefault}->{mysqlcmd} = { host => $self->{db_host} };
        if (defined($self->{db_port}) && $self->{db_port} ne '') {
            $self->{sqldefault}->{dbi}->{data_source} .= ';port=' . $self->{db_port};
            $self->{sqldefault}->{mysqlcmd}->{port} = $self->{db_port};
        }
    }

    return $self;
}

1;

__END__

=head1 PLUGIN DESCRIPTION

Check MySQL Server.

=over 8

You can use following options or options from 'sqlmode' directly.

=item B<--host>

Hostname to query.

=item B<--port>

Database Server Port.

=back

=cut
