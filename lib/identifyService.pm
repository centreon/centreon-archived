################################################################################
# Copyright 2005-2011 MERETHIS
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
# 
# SVN : $URL$
# SVN : $Id$
#
####################################################################################

# Identify concerned service
# need parameter hostname and service description
sub identify_service($$){
	my $sth1 = $con_ods->prepare("SELECT id, storage_type, must_be_rebuild FROM index_data WHERE host_name = '".$_[0]."' AND service_description = '".$_[1]."'");
	if (!$sth1->execute) {
		return error_thrown(1, "Error : " . $sth1->errstr);
	}

	# IF service unknown, insert it.
	if ($sth1->rows() == 0) {
	    if ($_[0] && $_[1]) {

		# Get Host_id FROM host_name
		$host_id = getHostID($_[0], $con_oreon);
		return undef if (!defined($host_id));

		if (defined($host_id) && $host_id ne 0){

		    # Get Service id from description
		    $service_id = getServiceID($host_id, $_[1]);
		    return undef if (!defined($service_id));

		    if ($service_id){
			$sth1 = $con_ods->prepare("SELECT * FROM `index_data` WHERE `host_id` = '".$host_id."' AND `service_id` = '".$service_id."'");
			if (!$sth1->execute) {
				return error_thrown(1, "Error : " . $sth1->errstr);
			}
			if ($sth1->rows() == 0){
			    $sth1 = $con_ods->prepare(	"INSERT INTO `index_data` (`host_name`, `host_id`, `service_description`, `service_id`) ".
							"VALUES ('".$_[0]."', '".$host_id."', '".$_[1]."', '".$service_id."')");
			    if (!$sth1->execute) {
				return error_thrown(1, "Error : " . $sth1->errstr);
			    }

			} else {
			    $sth1 = $con_ods->prepare("UPDATE `index_data` SET `host_name` = '".$_[0]."' , `service_description` = '".$_[1]."' where `host_id` = '".$host_id."' AND `service_id` = '".$service_id."'");
			    if (!$sth1->execute) {
				return error_thrown(1, "Error : " . $sth1->errstr);
			    }

			}
			undef($sth1);
		    }
		}
	    }
	    $sth1 = $con_ods->prepare("SELECT id, storage_type FROM index_data WHERE host_name = '".$_[0]."' AND service_description = '".$_[1]."'");
	    if (!$sth1->execute) {
		return error_thrown(1, "Error : " . $sth1->errstr);
	    }
	}
	undef($host_id);
	undef($service_id);
	my $data = $sth1->fetchrow_hashref();
	undef($sth1);
	my @data_return = ($data->{'id'}, $data->{'storage_type'}, $data->{'must_be_rebuild'});
	undef($data);
	return @data_return;
}

sub identify_hidden_service($$){
	my $sth1 = $con_ods->prepare("SELECT id, storage_type, must_be_rebuild FROM index_data WHERE host_name = '".$_[0]."' AND service_description = '".$_[1]."'");
	if (!$sth1->execute) { return error_thrown(1, "Error : " . $sth1->errstr); }
	# IF service unknown, insert it.
	if ($sth1->rows() == 0){
	    if ($_[0] && $_[1]){
		$sth1 = $con_ods->prepare("INSERT INTO `index_data` (`host_name`, `service_description`, `special`) VALUES ('".$_[0]."', '".$_[1]."', '1')");
		if (!$sth1->execute) {
		    return error_thrown(1, "Error : " . $sth1->errstr);
		}
		undef($sth1);
	    }
	    $sth1 = $con_ods->prepare("SELECT id, storage_type FROM index_data WHERE host_name = '".$_[0]."' AND service_description = '".$_[1]."'");
	    if (!$sth1->execute) { return error_thrown(1, "Error : " . $sth1->errstr); }
	}
	my $data = $sth1->fetchrow_hashref();
	undef($sth1);
	my @data_return = ($data->{'id'}, $data->{'storage_type'}, $data->{'must_be_rebuild'});
	undef($data);
	return @data_return;
}
1;
