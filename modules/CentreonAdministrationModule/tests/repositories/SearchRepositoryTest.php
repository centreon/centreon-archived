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
