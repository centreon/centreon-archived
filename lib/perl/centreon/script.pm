package centreon::script;

use strict;
use warnings;
use FindBin;
use Getopt::Long;
use Pod::Usage;
use centreon::common::logger;
use centreon::common::db;
use centreon::common::lock;

use vars qw($centreon_config);

$SIG{__DIE__} = sub {
    my $error = shift;
    print "Error: $error";
    exit 1;
};

sub new {
    my ($class, $name, %options) = @_;
    my %defaults = 
      (
       config_file => "@CENTREON_ETC@/centreon-config.pm",
       log_file => undef,
       centreon_db_conn => 0,
       centstorage_db_conn => 0,
       severity => "info",
       noconfig => 0,
       noroot => 0
      );
    my $self = {%defaults, %options};

    bless $self, $class;
    $self->{name} = $name;
    $self->{logger} = centreon::common::logger->new();
    $self->{options} = {
        "config=s" => \$self->{config_file},
        "logfile=s" => \$self->{log_file},
        "severity=s" => \$self->{severity},
        "help|?" => \$self->{help}
    };
    return $self;
}

sub init {
    my $self = shift;

    if (defined $self->{log_file}) {
        $self->{logger}->file_mode($self->{log_file});
    }
    $self->{logger}->severity($self->{severity});

    if ($self->{noroot} == 1) {
        # Stop exec if root
        if ($< == 0) {
            $self->{logger}->writeLogError("Can't execute script as root.");
            die("Quit");
        }
    }
    
    if ($self->{centreon_db_conn}) {
        $self->{cdb} = centreon::common::db->new
          (db => $self->{centreon_config}->{centreon_db},
           host => $self->{centreon_config}->{db_host},
           user => $self->{centreon_config}->{db_user},
           password => $self->{centreon_config}->{db_passwd},
           logger => $self->{logger});
        $self->{lock} = centreon::common::lock::sql->new($self->{name}, dbc => $self->{cdb});
        $self->{lock}->set();
    }
    if ($self->{centstorage_db_conn}) {
        $self->{csdb} = centreon::common::db->new
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
    pod2usage(-exitval => 1, -input => $FindBin::Bin . "/" . $FindBin::Script) if $self->{help};
    if ($self->{noconfig} == 0) {
        require $self->{config_file};
        $self->{centreon_config} = $centreon_config;
    }
}

sub run {
    my $self = shift;

    $self->parse_options();
    $self->init();
}

1;
