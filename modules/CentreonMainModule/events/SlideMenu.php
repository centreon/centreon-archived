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

namespace CentreonMain\Events;

use Centreon\Internal\Exception;

class SlideMenu 
{
    
    private $menuList = array();
    private $MendatorykeyList = array('name','url','icon','order');
    private $hostId;
    
    public function __construct($hostId)
    {
        $this->hostId = $hostId;
    }
    
    /**
     * Get host Id associated with this event
     *
     * return long $hostId 
     */
    public function getHostId()
    {
        return $this->hostId;
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
    
    private function testMenuArray($menuList)
    {
        foreach($this->MendatorykeyList as $key){
            if(!array_key_exists($key,$menuList)){
                return false;
            }
        }
        return true;
    }
    
    private function orderMenu(){
           
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
            throw new Exception("Invalid menu nomenclature, array(".implode(",",array_keys($menuList))." ) sended, should be : array(name,url,icon,order)",0);
        }
    }
    
}
