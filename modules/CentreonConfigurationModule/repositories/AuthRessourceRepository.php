<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CentreonConfiguration\Repository;

use CentreonConfiguration\Repository\Repository;
/**
 * Description of AuthRessourceRepository
 *
 * @author bsauveton
 */
class AuthRessourceRepository extends Repository
{
    public static $objectClass = '\CentreonConfiguration\Models\AuthRessource';
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'auth_resources' => 'cfg_auth_resources,ar_id,ar_name'
        ),
    );
    
    //put your code here
}
