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
namespace Centreon\Internal\Form\Generator;

use Centreon\Internal\Di;

/**
 * Description of Generator
 *
 * @author lionel
 */
abstract class Generator
{
    /**
     *
     * @var type 
     */
    protected $dbconn;
    
    /**
     *
     * @var string 
     */
    protected $formName = '';
    
    /**
     *
     * @var string 
     */
    protected $formRoute;
    
    /**
     *
     * @var string 
     */
    protected $formRedirect;
    
    /**
     *
     * @var string 
     */
    protected $formRedirectRoute;
    
    /**
     *
     * @var array 
     */
    protected $formComponents = array();
    
    /**
     *
     * @var array 
     */
    protected $formDefaults = array();
    
    /**
     *
     * @var \Centreon\Internal\Form 
     */
    protected $formHandler;
    
    /**
     *
     * @var type 
     */
    protected $firstSection;
    
    /**
     *
     * @var array 
     */
    protected $extraParams;

    /**
     * The product version
     *
     * @var string
     */
    protected $productVersion = '';
    
    /**
     * 
     * @param type $formRoute
     * @param type $extraParams
     * @param type $productVersion
     */
    public function __construct($formRoute, $extraParams = array(), $productVersion = '')
    {
        $this->formRoute = $formRoute;
        $this->extraParams = $extraParams;
        $this->productVersion = $productVersion;
        $di = Di::getDefault();
        $this->dbconn = $di->get('db_centreon');
    }
    
    /**
     * 
     * @return array
     */
    public function getValidationScheme()
    {
        $validatorsScheme = $this->getValidators();
        $validatorsScheme['mandatory'] = $this->getMandatoryFields();
        $validationScheme = array('mandatory' => $validatorsScheme['mandatory'], 'fieldScheme' => $validatorsScheme['fieldScheme']);
        return $validationScheme;
    }
    
    /**
     * 
     * @return type
     */
    public function getMandatoryFields()
    {
        $mandatoryQuery = "SELECT name FROM cfg_forms_fields WHERE mandatory = '1' "
            . "AND field_id IN (
                    SELECT
                        fi.field_id
                    FROM
                        cfg_forms_fields fi, cfg_forms_blocks fb, cfg_forms_blocks_fields_relations fbf, cfg_forms_sections fs, cfg_forms f
                    WHERE
                        fi.field_id = fbf.field_id
                    AND
                        fbf.block_id = fb.block_id
                    AND
                        fb.section_id = fs.section_id
                    AND
                        fs.form_id = f.form_id
                    AND
                        f.route = '$this->formRoute'
            )";
        $stmt = $this->dbconn->query($mandatoryQuery);
        $mandatoryFieldList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_column($mandatoryFieldList, 'name');
    }
}
