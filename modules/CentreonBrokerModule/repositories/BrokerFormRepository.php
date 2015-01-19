<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonBroker\Repository;

use Centreon\Repository\FormRepository;
use CentreonConfiguration\Models\Poller;
use CentreonBroker\Repository\BrokerRepository;
use CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Internal\Poller\Template as PollerTemplate;
use CentreonConfiguration\Internal\Poller\Template\Manager as PollerTemplateManager;
use Centreon\Internal\Form;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package CentreonEngine
 * @subpackage Repository
 */
class BrokerFormRepository extends FormRepository
{
    /**
     * Generate Form for the given poller using its template
     * 
     * @param integer $pollerId
     * @return type
     * @throws Exception
     */
    public static function getFormForPoller($pollerId)
    {
        $litePollerTemplate = PollerRepository::getTemplate($pollerId);
        $pollerForm = static::buildPollerTemplateForm($litePollerTemplate->toFullTemplate(), $pollerId);
        return $pollerForm;
    }
    
    /**
     * 
     * @param PollerTemplate $pollerTemplate
     * @return type
     * @throws Exception
     */
    public static function buildPollerTemplateForm(PollerTemplate $pollerTemplate, $pollerId)
    {
        $setUp = $pollerTemplate->getBrokerPart()->getSetup();
        if (count($setUp) < 1) {
            throw new Exception('No setup found in the template');
        }
        $currentSetUp = $setUp[0];
        $brokerMode = $currentSetUp->getMode('normal');
        
        $formHandler = new Form('broker_full_form');
        $formComponents = array();
        $defaultValues = array();
        
        $formComponents['General']['general'] = static::addGeneralParams($formHandler, $defaultValues, $pollerId);
        $formComponents['General']['path'] = static::addPathParams($formHandler, $defaultValues, $pollerId);

        foreach ($brokerMode as $mode) {
            if (!isset($mode['general'])) {
                throw new Exception('No name detected');
            }
            $sectionName = $mode['general']['name'];
            unset($mode['general']);
            $formComponents[$sectionName] = array();
            
            foreach ($mode as $blockInitialName => $blockContent) {
                
                switch ($blockInitialName) {
                    default:
                        continue;
                        break;
                    case 'logger':
                        $elements = static::parseLoggerParams($formHandler, $blockInitialName, $blockContent, $defaultValues, $sectionName);
                        if (count($elements) > 0) {
                            $formComponents[$sectionName][$blockInitialName] = $elements;
                        }
                        break;
                    case 'input':
                    case 'output':
                        $elements = static::parseOutputInputParams($formHandler, $blockInitialName, $blockContent, $defaultValues, $sectionName);
                        if (count($elements) > 0) {
                            $formComponents[$sectionName][$blockInitialName] = $elements;
                        }
                        break;
                    case 'module_directory':
                    case 'event_queue_max_size':
                    case 'write_thread_id':
                    case 'write_timestamp':
                    case 'flush_logs':
                        $elements = static::parseCustomParams(
                            $formHandler,
                            $blockInitialName,
                            array($blockInitialName => $blockContent),
                            $defaultValues
                        );
                        if (count($elements) > 0) {
                            $formComponents[$sectionName][$blockInitialName] = $elements;
                        }
                        break;
                }
            }
            
            if (count($formComponents[$sectionName]) == 0) {
                unset($formComponents[$sectionName]);
            }
        }
        
        $formHandler->addHidden('poller_id', $pollerId);
        $formHandler->addHidden('poller_tmpl', $pollerTemplate->getName());
        $formHandler->addSubmit('save_form', _("Save"));
        
        static::getSavedDefaultValues($pollerId, $defaultValues);
        
        $formHandler->setDefaults($defaultValues);
        
        $finalForm = static::genForm($formHandler, $formComponents);
        return $finalForm;
    }
    
    public static function getSavedDefaultValues($pollerId, &$defaultValues)
    {
        $userInfoList = BrokerRepository::getUserInfo($pollerId);
        foreach ($userInfoList as $moduleName => $userInfoModules) {
            foreach ($userInfoModules as $userInfo) {
                $defaultValues[$userInfo['config_key']] = $userInfo['config_value'];
            }
        }
    }
    
    /**
     * 
     * @param type $type
     * @param type $sName
     * @param type $element
     * @return type
     */
    public static function buildElementName($type, $sName, $element, $sectionName)
    {
        return $finalName = $sectionName . '-' . $type . '-' . $element['type'] . '-' . $element['name'] . '-' . $sName;
    }
    
    /**
     * 
     * @param type $formHandler
     * @param type $defaultValues
     * @param type $pollerId
     * @return string
     */
    public static function addGeneralParams(&$formHandler, &$defaultValues, $pollerId)
    {
        $generalOptions = array(
            'event_queue_max_size',
            'write_thread_id',
            'write_timestamp',
            'flush_logs',
        );
        
        $componentList = array();
        
        foreach ($generalOptions as $gOpt) {
            $componentList[] = $gOpt;
            $componentLabel = ucwords(str_replace('_', ' ', $gOpt));
            $formHandler->addStatic(
                array(
                    'name' => $gOpt,
                    'type' => 'text',
                    'label' => $componentLabel,
                    'mandatory' => '0'
                )
            );
        }
        
        $defaultValues = array_merge($defaultValues, BrokerRepository::getGeneralValues($pollerId));
        
        return $componentList;
    }
    
    /**
     * 
     * @param type $formHandler
     * @param type $defaultValues
     * @param type $pollerId
     * @return type
     */
    public static function addPathParams(&$formHandler, &$defaultValues, $pollerId)
    {
        $pathOptions = array(
            'broker_etc_directory' => 'directory_config',
            'broker_module_directory' => 'directory_modules',
            'broker_data_directory' => 'directory_data',
            'broker_logs_directory' => 'directory_logs',
            'init_script' => 'init_script'
        );
        
        $componentList = array();
        
        foreach (array_keys($pathOptions) as $pOpt) {
            $componentList[] = $pOpt;
            $componentLabel = ucwords(str_replace('_', ' ', $pOpt));
            $formHandler->addStatic(
                array(
                    'name' => $pOpt,
                    'type' => 'text',
                    'label' => $componentLabel,
                    'mandatory' => '1'
                )
            );
        }
        
        $currentValues = BrokerRepository::getPathsFromPollerId($pollerId);
        foreach ($currentValues as $cKey => $cValue) {
            $defaultValues[array_search($cKey, $pathOptions)] = $cValue;
        }
        
        return $componentList;
    }
    
    /**
     * 
     * @param type $formHandler
     * @param string $blockName
     * @param array $components
     * @return array
     */
    public static function parseCustomParams(&$formHandler, $blockName, $components, &$defaultValues)
    {
        $componentList = array();
        foreach ($components as $singleComponentName => $singleComponent) {
            $componentName = $blockName . '-' . $singleComponentName;
            $componentLabel = ucwords(str_replace('-', ' ', $singleComponentName));
            $componentList[] = $componentName;
            $formHandler->addStatic(
                array(
                    'name' => $componentName,
                    'type' => 'text',
                    'label' => $componentLabel,
                    'mandatory' => '0'
                )
            );
            $defaultValues[$componentName] = $singleComponent;
        }
        return $componentList;
    }
    
    /**
     * 
     * @param type $formHandler
     * @param string $blockName
     * @param array $components
     * @return array
     */
    public static function parseOutputInputParams(&$formHandler, $blockName, $components, &$defaultValues, $sectionName)
    {
        $excludeTypes = array('rrd', 'local', 'file');
        $overrideableFields = array(
            'read_timeout',
            'sql' => array('queries_per_transaction', 'read_timeout'),
            'storage' => array('queries_per_transaction', 'read_timeout'),
            'tcp' => array('port', 'one_peer_retention_mode', 'tls', 'compression_level', 'compression_buffer')
        );
        $componentList = array();
        foreach ($components as $singleComponent) {
            if (!in_array($singleComponent['type'], $excludeTypes)) {
                foreach ($singleComponent as $sName => $svalue) {
                    if (isset($overrideableFields[$singleComponent['type']]) && in_array($sName, $overrideableFields[$singleComponent['type']])) {
                        $componentName = static::buildElementName($blockName, $sName, $singleComponent, $sectionName);
                        $componentLabel = ucwords(str_replace('-', ' ', $singleComponent['type'].'-'.$sName));
                        $componentList[] = $componentName;
                        $formHandler->addStatic(
                            array(
                                'name' => $componentName,
                                'type' => 'text',
                                'label' => $componentLabel,
                                'mandatory' => '0'
                            )
                        );
                        $defaultValues[$componentName] = $svalue;
                    }
                }
            }
        }
        return $componentList;
    }
    
    /**
     * 
     * @param type $formHandler
     * @param string $blockName
     * @param array $components
     * @return array
     */
    public static function parseLoggerParams(&$formHandler, $blockName, $components, &$defaultValues, $sectionName)
    {
        $overrideableFields = array('debug', 'info', 'level', 'facility', 'max_size');
        $componentList = array();
        foreach ($components as $singleComponent) {
            foreach ($singleComponent as $sName => $svalue) {
                if (in_array($sName, $overrideableFields)) {
                    $componentName = static::buildElementName($blockName, $sName, $singleComponent, $sectionName);
                    $componentLabel = ucwords(str_replace('-', ' ', $singleComponent['type'].'-'.$sName));
                    $componentList[] = $componentName;
                    $formHandler->addStatic(
                        array(
                            'name' => $componentName,
                            'type' => 'text',
                            'label' => $componentLabel,
                            'mandatory' => '0'
                        )
                    );
                    $defaultValues[$componentName] = $svalue;
                }
            }
        }
        return $componentList;
    }
    
    /**
     * 
     * @param \Centreon\Internal\Form $formHandler
     * @param type $formComponents
     * @return string
     */
    public static function genForm($formHandler, $formComponents)
    {
        $formElements = $formHandler->toSmarty();
        $htmlRendering = '<div class="row">';
        
        $htmlRendering .= '<div '
            . 'class="bs-callout bs-callout-success" '
            . 'id="formSuccess" '
            . 'style="display: none;">'
            . 'The object has been successfully updated'
            . '</div>';
        $htmlRendering .= '<div '
            . 'class="bs-callout bs-callout-danger" '
            . 'id="formError" '
            . 'style="display: none;">'
            . 'An error occured'
            . '</div>';
        
        $htmlRendering .= '<form class="form-horizontal" role="form" '.$formElements['attributes'].'>';
        
        $formRendering = '';
        
        $tabRendering = '<div class="form-tabs-header">'
            . '<div class="inline-block">'
            . '<ul class="nav nav-tabs" id="formHeader">';
        
        $first = true;
        
        foreach ($formComponents as $sectionLabel => $sectionComponents) {
            if ($first) {
                $active = 'active';
                $first = false;
            } else {
                $active = '';
            }
            $tabRendering .= '<li class="'.$active.'">'
                . '<a '
                . 'href="#'.str_replace(' ', '', $sectionLabel).'" '
                . 'data-toggle="tab">'
                .$sectionLabel
                .'</a>'
                . '</li>';
        }
        $formRendering .= '</ul></div></div>';
        
        $formRendering .= '<div class="tab-content">';
        $first = true;
        foreach ($formComponents as $sectionLabel => $sectionComponents) {
            if ($first) {
                $active = 'active';
                $first = false;
            } else {
                $active = '';
            }
            $formRendering .= '<div class="tab-pane '.$active.'" id="'.str_replace(' ', '', $sectionLabel).'">';
            foreach ($sectionComponents as $blockLabel => $blockComponents) {
                $formRendering .= '<h4 class="page-header" style="padding-top:0px;">'.ucwords($blockLabel).'</h4>';
                $formRendering .= '<div class="panel-body">';
                foreach ($blockComponents as $component) {
                    if (isset($formElements[$component]['html'])) {
                        $formRendering .= $formElements[$component]['html'];
                    }
                }
                $formRendering .= '</div>';
            }
            $formRendering .= '</div>';
        }
        $formRendering .= '</div>';
        
        $formRendering .= '<div>'.$formElements['save_form']['html'].'</div>';
        
        $formRendering .= $formElements['hidden'];
        
        $htmlRendering .= $tabRendering.$formRendering;
        
        return $htmlRendering;
    }
}
