<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use CentreonRemote\Domain\Value\ServerWizardIdentity;

class LinkedPollerConfigurationService
{

    public function configure($serverID)
    {
        $isRemoteConnection = ServerWizardIdentity::requestConfigurationIsRemote();
        $idBindString = '';

        // IF CONNECTING REMOTE
        // I can have (not required, can be empty) a $_POST list of poller ips from this current centreon
        // - then I need to make each of these pollers managed by the remote server I just inserted
        // - then export configuration xml file and restart
        // IF CONNECTING POLLER
        // I can have (not required, can be empty) a $_POST remote server ip linked to this centreon
        // - then I need to set the poller which I just inserted to be managed by this remote
        // - then export configuration xml file and restart

        if ($isRemoteConnection) {
            // $serverID is the one of the new remote
            // - get ips from $_POST and for each in their broker configs set this
            // - ['central-broker']['output_forward'] host to this ip
            // setPollerConfigurationRelation()
            $pollerIDs = $_POST['linked_pollers'] ?? '';
            $pollerIDs = (array) $pollerIDs;

            if (empty($pollerIDs)) {
                return false;
            }

            foreach ($pollerIDs as $key => $id) {
                $idBindString .= ":id_{$key},";
            }

            $idBindString = rtrim($idBindString, ',');
            $queryPollers = "SELECT id FROM nagios_server WHERE id IN({$idBindString})";
            //$stmt = $this->_db->prepare($queryPollers);

            //foreach ($pollerIDs as $key => $value) {
            //    $stmt->bindValue(':id_' . $key, $value);
            //}

            //try {
            //    $stmt->execute();
            //} catch (\Exception $e) {
            //    error_log($e->getMessage());
            //}
        } else {
            // $serverID is the one of the new poller
            // - get ip of remote from $_POST and in the broker config of the poller set
            // - ['central-broker']['output_forward'] host to this ip
            // setPollerConfigurationRelation()
        }
    }

    private function setPollerConfigurationRelation()
    {

    }
}
