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

namespace CentreonMain\Events;

use Centreon\Internal\Exception;

class SlideMenu 
{
    /**
     *
     * @var type 
     */
    private $menuList = array();
    
    /**
     *
     * @var type 
     */
    private $defaultMenu = array();
    
    /**
     *
     * @var type 
     */
    private $MendatorykeyList = array('name','url','icon','order','tpl');
    
    /**
     *
     * @var type 
     */
    private $hostId;
    
    /**
     * 
     * @param type $hostId
     */
    public function __construct($hostId)
    {
        $this->id = $hostId;
    }
    
    /**
     * Get host Id associated with this event
     *
     * return long $hostId 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get service Id associated with this event
     *
     * return long $serviceId 
     */
    public function getServiceId(){
        return $this->serviceId;
    }

    /**
     * Get the array of menu populated with events
     *
     * return array $menuList 
     */
    public function getMenu()
    {
        return $this->menuList;
    }
    
    /**
     * 
     * @param type $menuList
     * @return boolean
     */
    private function testMenuArray($menuList)
    {
        foreach($this->MendatorykeyList as $key){
            if(!array_key_exists($key,$menuList)){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 
     */
    private function orderMenu()
    {
        $temporaryArray = array();
        foreach($this->menuList as $menu){
            $temporaryArray[] = $menu['order'];
        }
        array_multisort($temporaryArray, SORT_ASC, SORT_NUMERIC, $this->menuList);
    }
    
    
    /**
     * Add a menu to the slideMenu
     *
     * @param array $menuList 
     */
    public function addMenu($menuList)
    {
        if($this->testMenuArray($menuList)){
            $this->menuList[] = $menuList;
            $this->orderMenu();
        }else{
            throw new Exception("Invalid menu nomenclature, array(".implode(",",array_keys($menuList))." ) sended, should be : array(name,url,icon,order,tpl)",0);
        }
    }

    /**
     * 
     * @param type $defaultMenu
     * @throws Exception
     */
    public function setDefaultMenu($defaultMenu)
    {

        if ($this->testMenuArray($defaultMenu)) {
            $this->defaultMenu = $defaultMenu;
        } else {
            throw new Exception(
                "Invalid menu nomenclature, array("
                    . implode(",",array_keys($menuList))
                    ." ) sended, should be : array(name,url,icon,order,tpl)"
                ,
                0
            );
        }

    }

    /**
     * 
     * @return type
     */
    public function getDefaultMenu()
    {
        return $this->defaultMenu;
    }


    
}
