<?php
/*
 * MERETHIS
 *
 * Source Copyright 2005-2010 MERETHIS
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more information : contact@merethis.com
 *
 */

require_once _CENTREON_PATH_ . '/www/include/configuration/configKnowledge/functions.php';
require_once _CENTREON_PATH_ . '/www/class/centreonHost.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonService.class.php';

class procedures_Proxy
{
    private $DB;
    private $hflag;
    private $sflag;
    private $proc;
    public $url;
    private $wikiUrl;
    private $hostObj;
    private $serviceObj;

    public function __construct($pearDB, $db_prefix, $host_name, $service_description = null)
    {
        $this->DB = $pearDB;
        $this->hflag = 0;
        $this->sflag = 0;
        $this->hostObj = new CentreonHost($this->DB);
        $this->serviceObj = new CentreonService($this->DB);

        $conf = getWikiConfig($this->DB);
        $this->wikiUrl = $conf['kb_wiki_url'];
        $this->proc = new procedures(
            3,
            $conf['kb_db_name'],
            $conf['kb_db_user'],
            $conf['kb_db_host'],
            $conf['kb_db_password'],
            $this->DB,
            $conf['kb_db_prefix']
        );

        if (isset($host_name)) {
            if (isset($service_description)) {
                $this->returnServiceWikiUrl($this->DB->escape($host_name), $this->DB->escape($service_description));
            } else {
                $this->returnHostWikiUrl($this->DB->escape($host_name));
            }
        }
    }

    private function getHostId($hostName)
    {
        $result = $this->DB->query("SELECT host_id FROM host WHERE host_name LIKE '" . $hostName . "' ");
        $row = $result->fetchRow();
        $hostId = 0;
        if ($row["host_id"]) {
            $hostId = $row["host_id"];
        }
        return $hostId;
    }

    private function getServiceId($hostName, $serviceDescription)
    {
        /*
         * Get Services attached to hosts
         */
        $query = "SELECT s.service_id " .
            "FROM host h, service s, host_service_relation hsr " .
            "WHERE hsr.host_host_id = h.host_id " .
            "AND hsr.service_service_id = service_id " .
            "AND h.host_name LIKE '" . $hostName . "' " .
            "AND s.service_description LIKE '" . $serviceDescription . "' ";
        $result = $this->DB->query($query);
        while ($row = $result->fetchRow()) {
            return $row["service_id"];
        }
        $result->free();
        /*
         * Get Services attached to hostgroups
         */
        $query = "SELECT s.service_id " .
            "FROM hostgroup_relation hgr, host h, service s, host_service_relation hsr " .
            "WHERE hgr.host_host_id = h.host_id " .
            "AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id " .
            "AND h.host_name LIKE '" . $hostName . "' " .
            "AND service_id = hsr.service_service_id " .
            "AND service_description LIKZ '" . $serviceDescription . "' ";
        $result = $this->DB->query($query);
        while ($row = $result->fetchRow()) {
            return $row["service_id"];
        }
        $result->free();

    }

    private function returnHostWikiUrl($host_name)
    {
        $this->proc->setHostInformations();

        $procList = $this->proc->getProcedures();

        /*
         * Check if host has a procedure directly on Host
         */
        if (isset($procList["Host_:_" . $host_name])) {
            $this->url = $this->wikiUrl . "/index.php?title=Host_:_" . $host_name;
            return;
        }

        /*
         * Check if host can get a procedure on templates
         */
        $hostId = $this->getHostId($host_name);
        $templates = $this->hostObj->getTemplateChain($hostId);
        foreach ($templates as $template) {
            $templateName = $template['host_name'];
            if (isset($procList["Host-Template_:_" . $templateName])) {
                $this->url = $this->wikiUrl . "/index.php?title=Host-Template_:_" . $templateName;
                return;
            }
        }
    }

    private function returnServiceWikiUrl($host_name, $service_description)
    {
        if ($this->hflag != 0) {
            $this->proc->setHostInformations();
        }
        $this->proc->setServiceInformations();
        $this->sflag;

        $procList = $this->proc->getProcedures();

        /*
         * Check Service
         */
        $service_description = str_replace(' ', '_', $service_description);

        if (isset($procList["Service_:_" . trim($host_name . "_/_" . $service_description)])) {
            $this->url = $this->wikiUrl . "/index.php?title=Service_:_" . $host_name . "_/_" . $service_description;
            return;
        }

        /*
         * Check service Template
         */
        $serviceId = $this->getServiceId($host_name, $service_description);
        $templates = $this->serviceObj->getTemplatesChain($serviceId);
        foreach ($templates as $templateId) {
            $templateDescription = $this->serviceObj->getServiceDesc($templateId);
            if (isset($procList["Service-Template_:_" . $templateDescription])) {
                $this->url = $this->wikiUrl . "/index.php?title=Service-Template_:_" . $templateDescription;
                return;
            }
        }

        $this->returnHostWikiUrl($host_name);
    }
}
