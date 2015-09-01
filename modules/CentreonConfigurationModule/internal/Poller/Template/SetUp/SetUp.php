<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Internal\Poller\Template\SetUp;

/**
 * Description of SetUp
 *
 * @author lionel
 */
abstract class SetUp
{
    /**
     *
     * @var string 
     */
    protected $name;
    
    /**
     *
     * @var array 
     */
    protected $forms;
    
    /**
     *
     * @var array 
     */
    protected $params;


    /**
     * 
     * @param array $content
     */
    public function __construct($content)
    {
        if (isset($content['name'])) {
            $this->name = $content['name'];
        }
        if (isset($content['forms'])) {
            $this->forms = $content['forms'];
        }
        if (isset($content['params'])) {
            $this->params = $content['params'];
        }
    }
    
    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     *
     * @return array $fields
     */
    public function getFields()
    {
        $fields = array();

        if (!is_null($this->forms)) {
            foreach ($this->forms['steps'] as $step) {
                foreach ($step['fields'] as $field) {
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }
    
    /**
     * 
     * @param type $steps
     */
    public function genForm(&$steps)
    {
        if (!is_null($this->forms)) {
            foreach ($this->forms['steps'] as $step) {
                $steps[$step['name']] = $step['fields'];
            }
        }
    }
}
