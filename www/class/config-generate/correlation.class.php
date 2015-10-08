<?php

class Correlation extends AbstractObjectXML {
    protected $engine = null;
    protected $broker = null;
    protected $generate_filename = 'correlation.xml';
    protected $object_name = null;
    protected $stmt_correlation = null;
    private $correlation_object = array();
    private $has_correlation = null;
    private $correlation_file_path = null;
    private $stmt_poller = null;
    
    public function generateFromPollerId($poller_id, $localhost) {
        if ($localhost) {
            $this->generateMainCorrelation();
        }

        $this->generate_filename = 'correlation_' . $poller_id . '.xml';

        $this->doHost($poller_id);
        $this->doService($poller_id);
        $this->doDependency($poller_id);
        $this->doHostParentship($poller_id);

        # Generate correlation files
        $this->generateFile($this->correlation_object, false, 'conf');
        $this->writeFile($this->backend_instance->getPath());        
    }

    public function generateMainCorrelation() {
        $object = array();
        $this->generate_filename = basename($this->correlation_file_path);
        $dir = dirname($this->correlation_file_path);

        $this->stmt_poller = $this->backend_instance->db->prepare("SELECT
              id
            FROM nagios_server
            WHERE ns_activate = '1'
            ");
        $this->stmt_poller->execute();
        $result = $this->stmt_poller->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $object[]['include'] = $dir . '/correlation_' . $row['id'] . '.xml';
        }

        # Generate main file
        $this->generateFile($object, true, 'conf');
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

    private function doDependency($poller_id) {
        $dependency_instance = Dependency::getInstance();
        $dependencies_exported = $dependency_instance->getGeneratedDependencies();
        foreach ($dependencies_exported as $value) {
            $this->correlation_object[]['dependency'] = $value;
        }

        $this->doServiceHostDependency($poller_id);
        $this->doHostServiceDependency($poller_id);
    }

    private function doServiceHostDependency($poller_id) {
        $this->stmt_service_host_dependency = $this->backend_instance->db->prepare("SELECT
              dsp.host_host_id as parent_host_id, dsp.service_service_id as parent_service_id, dhc.host_host_id as child_host_id
            FROM dependency_serviceParent_relation dsp, dependency_hostChild_relation dhc, ns_host_relation nhr
            WHERE dsp.dependency_dep_id = dhc.dependency_dep_id
              AND dsp.host_host_id = nhr.host_host_id
              AND nhr.nagios_server_id = :poller_id
            ");
        $this->stmt_service_host_dependency->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt_service_host_dependency->execute();
        $result = $this->stmt_service_host_dependency->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_object[]['dependency'] = array(
                    '@attributes' => array(
                        'host' => $row['parent_host_id'],
                        'service' => $row['parent_service_id'],
                        'dependent_host' => $row['child_host_id']
                    )
                );
        }
    }

    private function doHostServiceDependency($poller_id) {
        $this->stmt_host_service_dependency = $this->backend_instance->db->prepare("SELECT
              dhp.host_host_id as parent_host_id, dsc.host_host_id as child_host_id, dsc.service_service_id as child_service_id
            FROM dependency_hostParent_relation dhp, dependency_serviceChild_relation dsc, ns_host_relation nhr
            WHERE dhp.dependency_dep_id = dsc.dependency_dep_id
              AND dhp.host_host_id = nhr.host_host_id
              AND nhr.nagios_server_id = :poller_id
            ");
        $this->stmt_host_service_dependency->bindParam(':poller_id', $poller_id, PDO::PARAM_INT);
        $this->stmt_host_service_dependency->execute();
        $result = $this->stmt_host_service_dependency->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $this->correlation_object[]['dependency'] = array(
                    '@attributes' => array(
                        'host' => $row['parent_host_id'],
                        'dependent_host' => $row['child_host_id'],
                        'dependent_service' => $row['child_service_id']
                    )
                );
        }
    }

    private function dohostParentship($poller_id) {
        $host_instance = Host::getInstance();
        $hosts_parentship = $host_instance->getGeneratedParentship();
        foreach ($hosts_parentship as $value) {
            $this->correlation_object[]['parent'] = $value;
        }
    }

    public function reset() {
        parent::reset();
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
}

?>
