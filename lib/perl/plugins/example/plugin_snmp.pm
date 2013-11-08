package example::plugin_snmp;

use strict;
use warnings;
use base qw(centreon::plugins::script_snmp);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    # $options->{options} = options object

    $self->{version} = '0.1';
    %{$self->{modes}} = (
                         'getvalue' => 'example::mode::getvalue'
                         );

    return $self;
}

1;

__END__

=head1 PLUGIN DESCRIPTION

An example of SNMP plugin type.

=cut
