<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace Test\CentreonAdministration\Repository;

use CentreonAdministration\Repository\SearchRepository;
use CentreonAdministration\Internal\User;

use \Test\Centreon\DbTestCase;

class SearchRepositoryTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonAdministrationModule/tests/data/json/';
    
    public function setUp()
    {
        parent::setUp();
        if (!isset($_SESSION)) {
            $_SESSION = array();
        }
        $_SESSION['user'] = new User(1);
    }
    
    public function testSaveSearch()
    {
        $newSearch = array(
            'route' => '/test/route/new',
            'label' => 'new search test',
            'searchText' => 'host:test service:ping'
        );
        
        SearchRepository::saveSearch($newSearch['route'], $newSearch['label'], $newSearch['searchText']);
        
        $this->tableEqualsXml(
            'cfg_searches',
            dirname(__DIR__) . '/data/search.insert.xml'
        );
    }
    
    public function testUpdateSearch()
    {
        $updateSearch = array(
            'route' => '/test/route',
            'label' => 'test search',
            'searchText' => 'host:test service:disk'
        );
        
        SearchRepository::saveSearch($updateSearch['route'], $updateSearch['label'], $updateSearch['searchText']);
        
        $this->tableEqualsXml(
            'cfg_searches',
            dirname(__DIR__) . '/data/search.update.xml'
        );
    }
    
    public function testLoadSearch()
    {
        $searchToLoad = array(
            'route' => '/test/route',
            'label' => 'test search 2'
        );
        
        $expectedResult = "host:testsearchhost2 service:testsearchservice2";
        $this->assertEquals(
            $expectedResult,
            SearchRepository::loadSearch($searchToLoad['route'], $searchToLoad['label'])
        );
    }
    
    public function testLoadNonExistSearch()
    {
        $searchToDelete = array(
            'route' => '/test/route',
            'label' => 'no exist search test'
        );
        
        $this->setExpectedException(
            'Exception',
            'Object not exist',
            0
        );
        
        SearchRepository::loadSearch($searchToDelete['route'], $searchToDelete['label']);
    }
    
    public function testDeleteSearch()
    {
        $searchToDelete = array(
            'route' => '/test/route',
            'label' => 'test search'
        );
        
        SearchRepository::deleteSearch($searchToDelete['route'], $searchToDelete['label']);
        
        $this->tableEqualsXml(
            'cfg_searches',
            dirname(__DIR__) . '/data/search.delete.xml'
        );
    }
    
    public function testDeleteNonExistSearch()
    {
        $searchToDelete = array(
            'route' => '/test/route',
            'label' => 'no exist search test'
        );
        
        $this->setExpectedException(
            'Exception',
            'Object not exist',
            0
        );
        
        SearchRepository::deleteSearch($searchToDelete['route'], $searchToDelete['label']);
    }
}
