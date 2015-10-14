<?php

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
        $hosts_exported = $host_instance->getExported();
        foreach ($hosts_exported as $key => $value) {
            if ($value != 1) {
                continue;
            }
            $this->correlation_object[]['host'] = array(
                '@attributes' => array(
                    'id' => $key,
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
            FROM dependency_hostParent_relation dhp, dependency_hostChild_relation dhc
            WHERE dhp.dependency_dep_id = dhc.dependency_dep_id
            ");
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
              dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id, dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
            FROM dependency_serviceParent_relation dsp, dependency_serviceChild_relation dsc
            WHERE dsp.dependency_dep_id = dsc.dependency_dep_id
            ");
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
              dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id, dhc.host_host_id as child_host_id
            FROM dependency_serviceParent_relation dsp, dependency_hostChild_relation dhc
            WHERE dsp.dependency_dep_id = dhc.dependency_dep_id
            ");
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
              dhp.host_host_id as parent_host_id, dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
            FROM dependency_hostParent_relation dhp, dependency_serviceChild_relation dsc
            WHERE dhp.dependency_dep_id = dsc.dependency_dep_id
            ");
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
        $stmt = $this->backend_instance->db->prepare("SELECT
              host_host_id, host_parent_hp_id
            FROM host_hostparent_relation
            ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_parentship_object[]['parent'] = array(
                '@attributes' => array(
                    'parent' => $row['host_parent_hp_id'],
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
              config_value
            FROM cfg_centreonbroker_info
            WHERE config_key = 'file' AND config_group = 'correlation'
            ");
        }
        $this->stmt_correlation->execute();
        $this->has_correlation = 0;
        if ($this->stmt_correlation->rowCount()) {
            $row = $this->stmt_correlation->fetch(PDO::FETCH_ASSOC);
            $this->has_correlation = 1;
            $this->correlation_file_path = $row['config_value'];
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

?>
