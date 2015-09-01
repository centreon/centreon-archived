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

namespace Centreon\Internal\Utils\Dependency\Graph;

/**
 * Description of Node
 *
 * @author lionel
 */
class Node
{
    /**
     *
     * @var string 
     */
    private $name;
    
    /**
     *
     * @var array 
     */
    private $edges = array();
    
    /**
     * 
     * @param string $nodeName
     */
    public function __construct($nodeName)
    {
        $this->name = $nodeName;
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
     * @return array
     */
    public function getEdges()
    {
        return $this->edges;
    }


    /**
     * 
     * @param \Centreon\Internal\Utils\Dependency\Graph\Node $node
     */
    public function addEdge(Node $node)
    {
        $this->edges[] = $node;
    }
    
    
}
