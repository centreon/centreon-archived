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

namespace Centreon\Internal\Utils\Dependency;

/**
 * Graph Dependency class
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class Graph
{
    /**
     *
     * @var array 
     */
    private $nodes = array();
    
    /**
     * 
     */
    public function __construct()
    {
        ;
    }
    
    /**
     * 
     * @param string $nodeName
     * @param array $dependencies
     */
    public function addNode($nodeName, $dependencies = array())
    {
        $myNode = new Graph\Node($nodeName);
        
        foreach($dependencies as $dependency) {
            $depNode = new Graph\Node($dependency['name']);
            $myNode->addEdge($depNode);
        }
        
        $this->nodes[$nodeName] = $myNode;
    }
    
    /**
     * 
     * @param string $nodeName
     * @return \Centreon\Internal\Utils\Dependency\Graph\Node
     */
    public function getNode($nodeName)
    {
        return $this->nodes[$nodeName];
    }


    /**
     * 
     * @param string $nodeName
     */
    public function removeNode($nodeName)
    {
        unset($this->nodes[$nodeName]);
    }
    
    /**
     * 
     * @param string $nodeName
     * @param array $resolved
     * @param array $seen
     * @throws Exception
     */
    public function resolve($nodeName, array &$resolved, array &$seen)
    {
        $myNode = $this->getNode($nodeName);
        $nodeEdges = $myNode->getEdges();
        $seen[] = $nodeName;
        
        foreach ($nodeEdges as $nodeEdge) {
            $edgeName = $nodeEdge->getName();
            if (!in_array($edgeName, $resolved)) {
                if (in_array($edgeName, $seen)) {
                    throw new Exception(sprintf("Circular reference detected %s -> %s", $nodeName, $edgeName));
                }
                $this->resolve($edgeName, $resolved, $seen);
            }
        }
        
        if (!in_array($nodeName, $resolved)) {
            $resolved[] = $nodeName;
            unset($seen[$nodeName]);
        }
    }
}
