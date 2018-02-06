<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

class Correlation extends AbstractObjectXML {
    protected $engine = null;
    protected $broker = null;
    protected $generate_filename = 'correlation.xml';
    protected $object_name = null;
    protected $stmt_correlation = null;
    private $correlation_object = array();
    private $correlation_dependency_object = array();
    private $correlation_parentship_object = array();
    private $has_correlation = null;
    private $correlation_file_path = null;
    private $poller_ids = array();

    public function generateFromPollerId($poller_id, $localhost) {
        if ($localhost) {
            $this->generateMainCorrelation();
            $this->generateDependency();
            $this->generateParentship();
        }

        $this->generate_filename = 'correlation_' . $poller_id . '.xml';

        $this->doHost($poller_id);
        $this->doService($poller_id);

        # Generate correlation files
        $this->generateFile($this->correlation_object, false, 'conf');
        $this->writeFile($this->backend_instance->getPath());
    }

    private function generateMainCorrelation() {
        $object = array();
        $this->generate_filename = basename($this->correlation_file_path);
        $dir = dirname($this->correlation_file_path);

        if (!count($this->poller_ids)) {
            $this->getPollerIds();
        }

        foreach ($this->poller_ids as $poller_id) {
            $object[]['include'] = $dir . '/correlation_' . $poller_id . '.xml';
        }
        $object[]['include'] = $dir . '/correlation_dependency.xml';
        $object[]['include'] = $dir . '/correlation_parentship.xml';

        # Generate main file
        $this->generateFile($object, true, 'conf');
        $this->writeFile($this->backend_instance->getPath());
    }

    private function generateDependency() {
        $this->generate_filename = 'correlation_dependency.xml';
        $dir = dirname($this->correlation_file_path);

        $this->doHostHostDependency();
        $this->doServiceServiceDependency();
        $this->doServiceHostDependency();
        $this->doHostServiceDependency();

        # Generate dependency file
        $this->generateFile($this->correlation_dependency_object, false, 'conf');
        $this->writeFile($this->backend_instance->getPath());
    }

    private function generateParentship() {
        $this->generate_filename = 'correlation_parentship.xml';
        $dir = dirname($this->correlation_file_path);

        $this->doParentship();

        # Generate parentship file
        $this->generateFile($this->correlation_parentship_object, false, 'conf');
        $this->writeFile($this->backend_instance->getPath());
    }

    private function doHost($poller_id) {
        $host_instance = Host::getInstance();
        $hosts = $host_instance->getGeneratedHosts();
        foreach ($hosts as $hostId) {
            $this->correlation_object[]['host'] = array(
                '@attributes' => array(
                    'id' => $hostId,
                    'instance_id' => $poller_id
                )
            );
        }
    }

    private function doService($poller_id) {
        $service_instance = Service::getInstance();
        $services_exported = $service_instance->getGeneratedServices();
        foreach ($services_exported as $hostId => $services) {
            foreach ($services as $serviceId) {
                $this->correlation_object[]['service'] = array(
                    '@attributes' => array(
                        'host' => $hostId,
                        'id' => $serviceId,
                        'instance_id' => $poller_id
                    )
                );
            }
        }
    }

    private function doHostHostDependency() {
        $stmt = $this->backend_instance->db->prepare("SELECT
              dhp.host_host_id as parent_host_id, dhc.host_host_id as child_host_id
            FROM dependency_hostParent_relation dhp, dependency_hostChild_relation dhc, host h, host h2
            WHERE dhp.dependency_dep_id = dhc.dependency_dep_id
                AND h.host_id = dhp.host_host_id AND h.host_activate = '1'
                AND h2.host_id = dhc.host_host_id AND h2.host_activate = '1'");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_dependency_object[]['dependency'] = array(
                '@attributes' => array(
                    'host' => $row['parent_host_id'],
                    'dependent_host' => $row['child_host_id']
                )
            );
        }
    }

    private function doServiceServiceDependency() {
        $stmt = $this->backend_instance->db->prepare("SELECT
              dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id,
              dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
            FROM dependency_serviceParent_relation dsp, dependency_serviceChild_relation dsc, service s, service s2
            WHERE dsp.dependency_dep_id = dsc.dependency_dep_id
                AND dsp.service_service_id = s.service_id AND s.service_activate = '1'
                AND dsc.service_service_id = s2.service_id AND s2.service_activate = '1'");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_dependency_object[]['dependency'] = array(
                '@attributes' => array(
                    'host' => $row['parent_host_id'],
                    'service' => $row['parent_service_id'],
                    'dependent_host' => $row['child_host_id'],
                    'dependent_service' => $row['child_service_id']
                )
            );
        }
    }

    private function doServiceHostDependency() {
        $stmt = $this->backend_instance->db->prepare("SELECT
              dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id,
              dhc.host_host_id as child_host_id
            FROM dependency_serviceParent_relation dsp, dependency_hostChild_relation dhc, host h, service s
            WHERE dsp.dependency_dep_id = dhc.dependency_dep_id
                AND dsp.service_service_id = s.service_id AND s.service_activate = '1'
                AND dhc.host_host_id = h.host_id AND h.host_activate = '1'");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_dependency_object[]['dependency'] = array(
                    '@attributes' => array(
                        'host' => $row['parent_host_id'],
                        'service' => $row['parent_service_id'],
                        'dependent_host' => $row['child_host_id']
                    )
                );
        }
    }

    private function doHostServiceDependency() {
        $stmt = $this->backend_instance->db->prepare("SELECT
              dhp.host_host_id as parent_host_id, dsc.host_host_id as child_host_id,
              dsc.service_service_id as child_service_id
            FROM dependency_hostParent_relation dhp, dependency_serviceChild_relation dsc, host h, service s
            WHERE dhp.dependency_dep_id = dsc.dependency_dep_id
                AND dsc.service_service_id = s.service_id AND s.service_activate = '1'
                AND dhp.host_host_id = h.host_id AND h.host_activate = '1'");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_dependency_object[]['dependency'] = array(
                    '@attributes' => array(
                        'host' => $row['parent_host_id'],
                        'dependent_host' => $row['child_host_id'],
                        'dependent_service' => $row['child_service_id']
                    )
                );
        }
    }

    private function doParentship() {
        $stmt = $this->backend_instance->db->prepare("SELECT hp.host_host_id, hp.host_parent_hp_id
            FROM host_hostparent_relation hp, host h, host h2
            WHERE hp.host_host_id = h.host_id AND h.host_activate = '1'
                AND hp.host_parent_hp_id = h2.host_id AND h2.host_activate = '1'");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_parentship_object[]['parent'] = array(
                '@attributes' => array(
                    'parent_host' => $row['host_parent_hp_id'],
                    'host' => $row['host_host_id'],
                    'instance_id' => $this->backend_instance->getPollerId()
                )
            );
        }
    }

    public function setCorrelation() {
        if (!is_null($this->has_correlation)) {
            return $this->has_correlation;
        }
        if (is_null($this->stmt_correlation)) {
            $this->stmt_correlation = $this->backend_instance->db->prepare("SELECT
              config_id, config_group_id
            FROM cfg_centreonbroker_info
            WHERE config_key = 'type' AND config_value = 'correlation'
            ");
        }
        $this->stmt_correlation->execute();
        $this->has_correlation = 0;
        if ($this->stmt_correlation->rowCount()) {
            $row = $this->stmt_correlation->fetch(PDO::FETCH_ASSOC);
            $configId = $row['config_id'];
            $correlationGroupId = $row['config_group_id'];
            $query = 'SELECT config_value FROM cfg_centreonbroker_info
                WHERE config_key = "file" AND config_id = ' . $configId . ' AND config_group_id = ' .
                $correlationGroupId;
            $stmt = $this->backend_instance->db->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->correlation_file_path = $row['config_value'];
            $this->has_correlation = 1;

        }
    }

    public function hasCorrelation() {
        if (is_null($this->has_correlation)) {
            $this->setCorrelation();
        }
        return $this->has_correlation;
    }

    public function getPollerIds() {
        $stmt = $this->backend_instance->db->prepare("SELECT
              id
            FROM nagios_server
            WHERE ns_activate = '1'
            ");
        $stmt->execute();
        $this->poller_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function reset() {
        $this->correlation_object = array();
        $this->correlation_dependency_object = array();
        $this->correlation_parentship_object = array();
        parent::reset();
    }
}
