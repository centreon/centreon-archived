<?php
/**
 *
 */

// Getting wiki configuration
require_once "/etc/centreon/centreon.conf.php";
$centreon_path = "/usr/share/centreon/";
$module_path = $centreon_path . "www/class/centreon-knowledge/";
require_once $centreon_path . "config/wiki.conf.php";
require_once $centreon_path . "www/class/centreonDB.class.php";
require_once $module_path . "procedures.class.php";
require_once $module_path . "procedures_DB_Connector.class.php";

// Define cron constants
define('_WIKIURL_', $WikiURL);
define('_CENTREONURL_', $CentreonURL);

// Last time the cron has been executed
$startTimestamp = time() - (3600*24);
define('_STARTDATE_', date('Y-m-d', $startTimestamp).'T00:00:00Z');


/**
 *
 * @return array
 */
function getCreatedPages()
{
    return getChangedPages('new');
}

/**
 *
 * @return array
 */
function getEditedPages()
{
    return getChangedPages('edit');
}

/**
 *
 * @param string $type
 * @return array
 */
function getChangedPages($type)
{
    // Connecting to Mediawiki API
    $apiUrl = _WIKIURL_.'/api.php?format=json&action=query&list=recentchanges&rclimit=50&rcprop=title&rctype='.$type;

    // Sending request
    $result = json_decode(file_get_contents($apiUrl));
    return $result->query->recentchanges;
}

/**
 *
 * @param array $pages
 * @return array
 */
function detectCentreonObjects($pages)
{
    $hosts = array();
    $hostsTemplates = array();
    $services = array();
    $servicesTemplates = array();

    $nbPages = count($pages);
    for ($i=0; $i<$nbPages; $i++)
    {
        $objectFlag = explode(':', $pages[$i]->title);
        switch($objectFlag[0])
        {
            case 'Host':
                if (!in_array($pages[$i]->title, $hosts))
                {
                    $hosts[] = $pages[$i]->title;
                }
                break;

            case 'Host-Template':
                if (!in_array($pages[$i]->title, $hostsTemplates))
                {
                    $hostsTemplates[] = $pages[$i]->title;
                }
                break;

            case 'Service':
                if (!in_array($pages[$i]->title, $services))
                {
                    $services[] = $pages[$i]->title;
                }
                break;

            case 'Service-Template':
                if (!in_array($pages[$i]->title, $servicesTemplates))
                {
                    $servicesTemplates[] = $pages[$i]->title;
                }
                break;

            default:
                continue;
                break;
        }
    }
    $centreonObjects = array(
        'hosts' => $hosts,
        'hostsTemplates' => $hostsTemplates,
        'services' => $services,
        'servicesTemplates' => $servicesTemplates,
    );
    return $centreonObjects;
}

/**
 *
 * @param CentreonDB $dbConnector
 * @param array $listOfObjects
 */
function synchronizeWithCentreon($dbConnector, $listOfObjects)
{
    foreach($listOfObjects as $categorie=>$object)
    {
        switch($categorie)
        {
            case 'hosts':
                foreach($object as $entity)
                {
                    $objName = substr($entity, 5);
                    editLinkForHost($dbConnector, str_replace(' ', '_', $objName));
                }
                break;

            case 'hostsTemplates':
                foreach($object as $entity)
                {
                    $objName = substr($entity, 14);
                    editLinkForHost($dbConnector, str_replace(' ', '_', $objName));
                }
                break;

            case 'services':
                foreach($object as $entity)
                {
                    $objName = explode(' ', $entity);
                    editLinkForService($dbConnector, $objName);
                }
                break;

            case 'servicesTemplates':
                foreach($object as $entity)
                {
                    $objName = substr($entity, 17);
                    editLinkForService($dbConnector, str_replace(' ', '_', $objName));
                }
                break;
        }
    }
}

/**
 *
 * @param CentreonDB $dbConnector
 * @param string $hostName
 */
function editLinkForHost($dbConnector, $hostName)
{
    $querySelect = "SELECT host_id FROM host WHERE host_name='$hostName'";
    $resHost = $dbConnector->query($querySelect);
    $tuple = $resHost->fetchRow();

    $valueToAdd = _CENTREONURL_.'/include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$';
    $queryUpdate = "UPDATE extended_host_information "
        ."SET ehi_notes_url = '$valueToAdd' "
        ."WHERE host_host_id = '".$tuple['host_id']."'";
    $dbConnector->query($queryUpdate);
}

/**
 *
 * @param CentreonDB $dbConnector
 * @param string $serviceName
 */
function editLinkForService($dbConnector, $objName)
{
    if (is_array($objName))
    {
        $serviceName = str_replace(' ', '_', $objName[count($objName) - 1]);
        unset($objName[count($objName) - 1]);
        $hostName = substr(implode('_', $objName), 8);
        $querySelect = "SELECT service_id "
            ."FROM service, host, host_service_relation "
            ."WHERE service.service_description = '$serviceName' "
            ."AND host.host_name='$hostName' "
            ."AND host_service_relation.host_host_id = host.host_id "
            ."AND host_service_relation.service_service_id = service.service_id";
    }
    else
    {
        $querySelect = "SELECT service_id FROM service WHERE service_description='$objName'";
    }

    $resService = $dbConnector->query($querySelect);
    $tuple = $resService->fetchRow();

    $valueToAdd = _CENTREONURL_.'/include/configuration/configKnowledge/proxy/proxy.php?host_name=$HOSTNAME$&service_description=$SERVICEDESC$';
    $queryUpdate = "UPDATE extended_service_information "
        ."SET esi_notes_url = '$valueToAdd' "
        ."WHERE service_service_id = '".$tuple['service_id']."'";
    $dbConnector->query($queryUpdate);
}


/**
 *************************
 ******     MAIN     *****
 *************************
 */

// Initiate connexion
$dbConnector = new CentreonDB();
// Get all pages title that where changed
$allPagesModificationInMediaWiki = array_merge(getCreatedPages(), getEditedPages());
$centreonObjects = detectCentreonObjects($allPagesModificationInMediaWiki);

// Synchro with Centreon
synchronizeWithCentreon($dbConnector, $centreonObjects);

?>
