<?php

namespace CentreonAdministration\Install;

/**
 * 
 */
class Installer extends \Centreon\Internal\Module\Installer
{
    /**
     * 
     * @param type $moduleInfo
     */
    public function __construct($moduleDirectory, $moduleInfo)
    {
        parent::__construct($moduleDirectory, $moduleInfo);
    }
    
    /**
     * @todo to change if with user defined password when we know if we'll seperate user from contact
     */
    public function customInstall()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $db = $di->get('db_centreon');
        $updateUser = "UPDATE contact SET contact_passwd = '" . md5('centreon') . "' WHERE contact_alias = 'admin'";
        $db->query($updateUser);
    }
    
    /**
     * 
     */
    public function customRemove()
    {
        
    }
}
