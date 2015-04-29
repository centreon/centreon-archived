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

namespace CentreonConfiguration\Forms\Validators;
use Centreon\Internal\Di;
use Centreon\Internal\Form\Validators\ValidatorInterface;
use CentreonConfiguration\Repository\ServicetemplateRepository;
use Centreon\Internal\Exception\Validator\MissingParameterException;


/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Unique implements ValidatorInterface
{
    
    public static $unicityFields = array(); 

    /**
     * 
     * @param type $value
     * @param array $params
     * @return boolean
     */
    
    public function validate($value, $params = array())
    {
        $db = Di::getDefault()->get('db_centreon');
        $tables = array();
        $conditions = array();
        $bSuccess = true;
        $resultError = _("Object already exists");
        $sMessage = '';

        if (isset($params['object'])) {
            $objClass = "CentreonConfiguration\\Repository\\".ucfirst($params['object']."Repository");
            self::$unicityFields = $objClass::$unicityFields;
            
            // Check if all mandatory unicty fields are present
            $requiredFields = array_keys(self::$unicityFields['fields']);
            $givenFields = array_keys($params['extraParams']);
            
         
            /*
            $missingFields = array_diff($requiredFields, $givenFields);
            if (count($missingFields) > 0) {
                $errorMessage = _("The following mandatory parameters are missing") . " :\n    - ";
                $errorMessage .= implode("\n    - ", $missingFields);
                throw new MissingParameterException($errorMessage);
            }
            */
            // Checking por unicity's params
            foreach ($params['extraParams'] as $key => $unicityParam) {
                if (isset(self::$unicityFields['fields'][$key])) {
                    $fieldComponents = explode (',', self::$unicityFields['fields'][$key]);
                    $tables[] = $fieldComponents[0];
                    $conditions[] = $fieldComponents[2] . "='$unicityParam'";
                }
            }

            // 
            if (isset(self::$unicityFields['joint'])) {
                $tables[] = self::$unicityFields['joint'];
                $conditions[] = self::$unicityFields['jointCondition'];
            }
            
            // FInalizing query
            $query = ' SELECT COUNT(*) AS NB FROM ' . implode(', ', $tables) . ' WHERE ' . implode(' AND ', $conditions);
            

            // Execute request
            $stmt = $db->query($query);
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (isset($result[0]['NB']) && $result[0]['NB'] > 0) {
                $bSuccess = false;
                $sMessage = $resultError;
            }
        }
       
        return array('success' => $bSuccess, 'error' => $sMessage);
    }
}
