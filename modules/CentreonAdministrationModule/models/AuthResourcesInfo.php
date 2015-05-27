<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CentreonAdministration\Models;

use Centreon\Models\CentreonBaseModel;


/**
 * Description of Ldap
 *
 * @author bsauveton
 */
class AuthResourcesInfo extends CentreonBaseModel
{
    protected static $table = "cfg_auth_resources_info";
    protected static $primaryKey = "ar_id";
    protected static $uniqueLabelField = "ari_name";
    protected static $relations = array(
     
    );
    
    
    //put your code here
}
