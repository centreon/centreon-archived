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
use CentreonConfiguration\Internal\Poller\Template as PollerTemplate;
use CentreonConfiguration\Internal\Poller\Template\Manager as PollerTemplateManager;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package CentreonEngine
 * @subpackage Repository
 */
class BrokerFormRepository extends FormRepository
{
    /**
     * 
     * @param integer $pollerId
     * @throws Exception
     */
    public static function getFormForPoller($pollerId)
    {
        /* Get poller template */
        $paramsPoller = Poller::get($pollerId, 'tmpl_name');
        if (!isset($paramsPoller['tmpl_name']) || is_null($paramsPoller['tmpl_name'])) {
            throw new Exception('Not template defined');
        }
        $tmplName = $paramsPoller['tmpl_name'];

        /* Load template information for poller */
        $listTpl = PollerTemplateManager::buildTemplatesList();
        if (!isset($listTpl[$tmplName])) {
            throw new Exception('The template is not found on list of templates');
        }
        
        $pollerValues = BrokerRepository::loadValues($pollerId);
        $pollerForm = static::buildPollerTemplateForm($listTpl[$tmplName]->toFullTemplate());
        unset($listTpl);
        return $pollerForm;
    }
    
    /**
     * 
     * @param PollerTemplate $pollerTemplate
     * @return type
     * @throws Exception
     */
    public static function buildPollerTemplateForm(PollerTemplate $pollerTemplate)
    {
        $setUp = $pollerTemplate->getBrokerPart()->getSetup();
        if (count($setUp) < 1) {
            throw new Exception('No setup found in the template');
        }
        $currentSetUp = $setUp[0];
        $brokerMode = $currentSetUp->getMode('normal');
        
        $formHandler = new \Centreon\Internal\Form('broker_full_form');
        $formComponents = array();

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
                        $elements = static::parseLoggerParams($formHandler, $blockInitialName, $blockContent);
                        if (count($elements) > 0) {
                            $formComponents[$sectionName][$blockInitialName] = $elements;
                        }
                        break;
                    case 'input':
                    case 'output':
                        $elements = static::parseOutputInputParams($formHandler, $blockInitialName, $blockContent);
                        if (count($elements) > 0) {
                            $formComponents[$sectionName][$blockInitialName] = $elements;
                        }
                        break;
                    case 'module_directory':
                    case 'event_queue_max_size':
                    case 'log_thread_id':
                        $elements = static::parseCustomParams(
                            $formHandler,
                            $blockInitialName,
                            array($blockInitialName => $blockContent));
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
        
        $formHandler->addSubmit('save_form', _("Save"));
        
        $finalForm = static::genForm($formHandler, $formComponents);
        return $finalForm;
    }
    
    /**
     * 
     * @param type $formHandler
     * @param string $blockName
     * @param array $components
     * @return array
     */
    public static function parseCustomParams(&$formHandler, $blockName, $components)
    {
        var_dump($components);
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
    public static function parseOutputInputParams(&$formHandler, $blockName, $components)
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
                        $componentName = $blockName . '-' . $sName;
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
    public static function parseLoggerParams(&$formHandler, $blockName, $components)
    {
        $overrideableFields = array('debug', 'info', 'level', 'facility', 'max_size');
        $componentList = array();
        foreach ($components as $singleComponent) {
            foreach ($singleComponent as $sName => $svalue) {
                if (in_array($sName, $overrideableFields)) {
                    $componentName = $blockName . '-' . $sName;
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
        
        foreach ($formComponents as $sectionLabel => $sectionComponents) {
            $tabRendering .= '<li>'
                . '<a '
                . 'href="#'.str_replace(' ', '', $sectionLabel).'" '
                . 'data-toggle="tab">'
                .$sectionLabel
                .'</a>'
                . '</li>';
        }
        $formRendering .= '</ul></div></div>';
        
        $formRendering .= '<div class="tab-content">';
        foreach ($formComponents as $sectionLabel => $sectionComponents) {
            $formRendering .= '<div class="tab-pane" id="'.str_replace(' ', '', $sectionLabel).'">';
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
