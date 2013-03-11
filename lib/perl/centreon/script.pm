package centreon::script;

use strict;
use warnings;
use Getopt::Long;
use Pod::Usage;
use centreon::logger;
use centreon::db;
use centreon::lock;

use vars qw($centreon_config);

sub new {
    my ($class, $name, %options) = @_;
    my %defaults = 
      (
       config_file => "/etc/centreon/centreon-config.pm",
       log_file => undef,
       centreon_db_conn => 0,
       centstorage_db_conn => 0,
       debug_mode => 0
      );
    my $self = {%defaults, %options};

    bless $self, $class;
    $self->{name} = $name;
    $self->{logger} = centreon::logger->new();
    $self->{options} = {
        "config=s" => \$self->{config_file},
        "logfile=s" => \$self->{log_file},
        "debug" => \$self->{debug_mode},
        "help|?" => \$self->{help}
    };
    return $self;
}

sub init {
    my $self = shift;

    if (defined $self->{log_file}) {
        $self->{logger}->file_mode($self->{log_file});
    }

    if ($self->{centreon_db_conn}) {
        $self->{cdb} = centreon::db->new
          (db => $self->{centreon_config}->{centreon_db},
           host => $self->{centreon_config}->{db_host},
           user => $self->{centreon_config}->{db_user},
           password => $self->{centreon_config}->{db_passwd},
           logger => $self->{logger});
        $self->{lock} = centreon::lock::sql->new($self->{name}, dbc => $self->{cdb});
        $self->{lock}->set();
    }
    if ($self->{centstorage_db_conn}) {
        $self->{csdb} = centreon::db->new
          (db => $self->{centreon_config}->{centstorage_db},
           host => $self->{centreon_config}->{db_host},
           user => $self->{centreon_config}->{db_user},
           password => $self->{centreon_config}->{db_passwd},
           logger => $self->{logger});
    }
}

sub DESTROY {
    my $self = shift;

    if (defined $self->{cdb}) {
        $self->{cdb}->disconnect();
    }
    if (defined $self->{csdb}) {
        $self->{csdb}->disconnect();
    }
}

sub add_options {
    my ($self, %options) = @_;

    $self->{options} = {%{$self->{options}}, %options};
}

sub parse_options {
    my $self = shift;

    Getopt::Long::Configure('bundling');
    die "Command line error" if !GetOptions(%{$self->{options}});
    pod2usage(1) if $self->{help};
    require $self->{config_file};
    $self->{centreon_config} = $centreon_config;
}

sub run {
    my $self = shift;

    $self->parse_options();
    $self->init();
}

1;
