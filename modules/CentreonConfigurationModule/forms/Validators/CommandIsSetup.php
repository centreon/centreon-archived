<?php

/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

/**
 * Description of CommandIsSetup
 *
 * @author bsauveton
 */

namespace CentreonConfiguration\Forms\Validators;
use Centreon\Internal\Form\Validators\ValidatorInterface;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Models\Hosttemplate;

class CommandIsSetup implements ValidatorInterface
{
    /**
     * 
     */
    public function validate($value, $params = array(), $sContext = 'server')
    {
        if(!isset($value) || $value === " "){
            // case : UI or API with host-templates params
            if(isset($params['extraParams']['host_hosttemplates']) && $params['extraParams']['host_hosttemplates'] != " " ){
                $tplIds = explode(',',$params['extraParams']['host_hosttemplates']);
                foreach($tplIds as $tplId){
                    if(!empty($tplId)){
                        $template = Hosttemplate::get($tplId);
                        
                        $childTemplates = HostRepository::getTemplateChain($tplId, array(), -1, true);
                        if(!empty($template['command_command_id'])){
                            $reponse = array('success' => true, 'error' => '');
                            return $reponse;
                        }
                        foreach($childTemplates as $childTemplate){
                            if(!empty($childTemplate['command_command_id'])){
                                $reponse = array('success' => true, 'error' => '');
                                return $reponse;
                            }
                        }
                    }
                }
            }else if(isset($params['object_id'])){
                // case : Only UI without host-templates params
                $childTemplates = HostRepository::getTemplateChain($params['object_id'], array(), -1, true);
                foreach($childTemplates as $childTemplate){
                    if(!empty($childTemplate['command_command_id'])){
                        $reponse = array('success' => true, 'error' => '');
                        return $reponse;
                    }
                }
            }
            
            $reponse = array('success' => false, 'error' => 'No check command set on the host and its templates');
        }else{
            $reponse = array('success' => true, 'error' => '');
        }
        return $reponse;
    }
}
