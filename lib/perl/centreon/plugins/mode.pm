package centreon::plugins::mode;

use strict;
use warnings;
use centreon::plugins::perfdata;

sub new {
    my ($class, %options) = @_;
    my $self  = {};
    bless $self, $class;

    $self->{perfdata} = centreon::plugins::perfdata->new(output => $options{output});
    %{$self->{option_results}} = ();
    $self->{output} = $options{output};
    $self->{mode} = $options{mode};
    $self->{version} = undef;

    return $self;
}

sub init {
    my ($self, %options) = @_;
    # options{default} = [ {option_name => '', option_value => '' }, ]

    %{$self->{option_results}} = %{$options{option_results}};
    # Manage default value
    return if (!defined($options{default}));
    foreach (keys %{$options{default}}) {
        if ($_ eq $self->{mode}) {
            foreach my $value (keys %{$options{default}->{$_}}) {
                if (!defined($self->{option_results}->{$value})) {
                    $self->{option_results}->{$value} = $options{default}->{$_}->{$value};
                }
            }
        }
    }
}

sub version {
    my ($self, %options) = @_;
    
    $self->{output}->add_option_msg(short_msg => "Mode Version: " . $self->{version});
}

sub disco_format {
    my ($self, %options) = @_;

}

sub disco_show {
    my ($self, %options) = @_;

}

1;

__END__

