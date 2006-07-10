#
# $Id: trap_common.pm,v 1.0 2006/06/30 12:30:00 Nicolas Cordier for Merethis $
#
# Oreon's plugins are developped with GPL Licence :
# http://www.fsf.org/licenses/gpl.txt
# Developped by : Nicolas Cordier for Merethis
#
# The Software is provided to you AS IS and WITH ALL FAULTS.
# OREON makes no representation and gives no warranty whatsoever,
# whether express or implied, and without limitation, with regard to the quality,
# safety, contents, performance, merchantability, non-infringement or suitability for
# any particular or intended purpose of the Software found on the OREON web site.
# In no event will OREON be liable for any direct, indirect, punitive, special,
# incidental or consequential damages however they may arise and even if OREON has
# been previously advised of the possibility of such damages.

package trap_common;

use strict;
use warnings;
use DBI;

use Exporter();

our @ISA = qw(Exporter);
our @EXPORT_OK = qw(submit_res getTrapsInfos);
our @EXPORT = @EXPORT_OK;

#
## configuration for oreon db access
#
## /!\ you may have to edit this
#
sub set_db
{
## part that should be modified
    my $db_name = "oreondb";	## name of your database for oreon
    my $login = "root";		## user of your database
    my $mdp   = "";		## password for this user
## end of part that should be modified

    my $dsn   = "dbi:mysql:$db_name";
    return $dsn, $login, $mdp;
}

#
## send result to nagios using nagios.cmd
#
## /!\ you may have to edit this
#
sub submit_res($)
{
## part that should be modified
    open(FILE, ">>/var/run/nagios/nagios.cmd") or die ("Can't open"); ## modify this for your nagios.cmd location
## end of part that should be modified
    print FILE $_[0];
    close(FILE);
}

#
## remove useless things to get a good ip
#
sub epur_ip($)
{
    (my $ip) = split(/:/, $_[0]);
    $ip = substr($ip, 1, -1);

    return $ip;
}

#
## retrieve hostname and hostid from db using the ip
#
sub get_hostinfos($$)
{
    my $requete = "SELECT host_name, host_id FROM host WHERE host_address='$_[1]' ";
    my $sth = $_[0]->prepare($requete);
    $sth->execute();
    my @host = $sth -> fetchrow_array;
    $sth -> finish;
    return @host;
}

#
## retrieve servicedescription from db using the ip and host_id
#
sub get_servicename($$$)
{
    my $requete = "SELECT service_description FROM service WHERE service_id IN";
    $requete .= " (SELECT service_id FROM traps_service_relation WHERE traps_id='$_[1]')";
    $requete .= " AND service_id IN";
    $requete .= " (SELECT service_service_id FROM host_service_relation WHERE host_host_id='$_[2]')";
    my $sth = $_[0]->prepare($requete);
    if ($sth->execute() == 0)
    {
	$sth -> finish;
	$requete = "SELECT service_description FROM service WHERE service_id IN";
	$requete .= " (SELECT service_id FROM traps_service_relation WHERE traps_id='$_[1]')";
	$requete .= " AND service_id IN";
	$requete .= " (SELECT service_service_id FROM host_service_relation WHERE hostgroup_hg_id IN";
	$requete .= " (SELECT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id='$_[2]')";
	$requete .= ")";
	$sth = $_[0]->prepare($requete);
	$sth->execute();
    }
    my $service = $sth -> fetchrow_array;
    $sth -> finish;
    return $service;
}

#
## return informations about the trap for the generation of the result sent to nagios
#
sub getTrapsInfos(@)
{
    shift;
    my $ip = epur_ip($_[0]);
    shift;
    my $trap_id = $_[0];
    shift;

    my @db = set_db();
    my $dbh = DBI->connect($db[0], $db[1], $db[2]) or die "Echec de la connexion\n";
    my @host = get_hostinfos($dbh, $ip);
    my $servicename = get_servicename($dbh, $trap_id, $host[1]);
    $dbh -> disconnect;
    return $host[0], $servicename, @_;
}
