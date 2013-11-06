package network::bluecoat::plugin;

use strict;
use warnings;
use base qw(centreon::plugins::script_snmp);

sub new {
    my ($class, %options) = @_;
    my $self = $class->SUPER::new(package => __PACKAGE__, %options);
    bless $self, $class;
    # $options->{options} = options object

    $self->{version} = '1.0';
    %{$self->{modes}} = (
                         'client-connections' => 'network::bluecoat::mode::clientconnections',
                         'client-requests' => 'network::bluecoat::mode::clientrequests',
                         'client-traffic' => 'network::bluecoat::mode::clienttraffic',
                         'cpu' => 'network::bluecoat::mode::cpu',
                         'disk' => 'network::bluecoat::mode::disk',
                         'hardware' => 'network::bluecoat::mode::hardware',
                         'memory' => 'network::bluecoat::mode::memory',
                         'server-connections' => 'network::bluecoat::mode::serverconnections',
                         );

    return $self;
}

1;

__END__

=head1 PLUGIN DESCRIPTION

Check Bluecoat hardware in SNMP.

=cut
