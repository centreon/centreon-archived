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

namespace Test\CentreonCustomview\Models\Relation;

use \Test\Centreon\DbTestCase,
    \CentreonCustomview\Models\Relation\Organization\WidgetModel;

class OrganizationWidgetModelModelTest extends DbTestCase
{
    protected $errMsg = 'Object not in database.';
    protected $dataPath = '/modules/CentreonCustomviewModule/tests/data/json/';

    public function testInsert()
    {
        WidgetModel::insert(2, 1);
        $this->tableEqualsXml(
            'cfg_organizations_widget_models_relations',
            dirname(dirname(__DIR__)) . '/data/organization_widget_model.insert.xml'
        );
    }

    public function testInsertDuplicate()
    {
        $this->setExpectedException(
            'PDOException',
            '',
            23000
        );
        WidgetModel::insert(1, 1);
    }

    public function testDelete()
    {
        WidgetModel::delete(1, 1);
        $this->tableEqualsXml(
            'cfg_organizations_widget_models_relations',
            dirname(dirname(__DIR__)) . '/data/organization_widget_model.delete-1.xml'
        );
        WidgetModel::delete(2);
        $this->tableEqualsXml(
            'cfg_organizations_widget_models_relations',
            dirname(dirname(__DIR__)) . '/data/organization_widget_model.delete-2.xml'
        );
    }

    public function testDeleteNotFound()
    {
        $this->setExpectedException(
            '\Centreon\Internal\Exception',
            $this->errMsg,
            0
        );
        WidgetModel::delete(42);
    }

    public function testGetMergedParameters()
    {
        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'widget_model_id' => '1',
                'name' => 'Widget 1',
                'shortname' => 'widget_1',
                'description' => 'First widget',
                'version' => '3.0.0',
                'directory' => '/dir',
                'author' => 'Author 1',
                'email' => 'author_1@domain.tld',
                'website' => 'http://modules.domain.tld',
                'keywords' => 'keyword',
                'isactivated' => '1',
                'isinstalled' => '1',
                'module_id' => '1',
                'screenshot' => null,
                'thumbnail' => null
            ),
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'widget_model_id' => '2',
                'name' => 'Widget 2',
                'shortname' => 'widget_2',
                'description' => 'Second widget',
                'version' => '3.0.0',
                'directory' => '/dir',
                'author' => 'Author 2',
                'email' => 'author_2@domain.tld',
                'website' => 'http://modules.domain.tld',
                'keywords' => 'keyword',
                'isactivated' => '0',
                'isinstalled' => '1',
                'module_id' => '1',
                'screenshot' => null,
                'thumbnail' => null
            ),
            array(
                'organization_id' => '2',
                'name' => 'Client organization',
                'shortname' => 'client',
                'active' => '0',
                'widget_model_id' => '2',
                'name' => 'Widget 2',
                'shortname' => 'widget_2',
                'description' => 'Second widget',
                'version' => '3.0.0',
                'directory' => '/dir',
                'author' => 'Author 2',
                'email' => 'author_2@domain.tld',
                'website' => 'http://modules.domain.tld',
                'keywords' => 'keyword',
                'isactivated' => '0',
                'isinstalled' => '1',
                'module_id' => '1',
                'screenshot' => null,
                'thumbnail' => null
            )
        );
        $result = WidgetModel::getMergedParameters();
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'widget_model_id' => '1',
                'name' => 'Widget 1',
                'shortname' => 'widget_1',
                'description' => 'First widget',
                'version' => '3.0.0',
                'directory' => '/dir',
                'author' => 'Author 1',
                'email' => 'author_1@domain.tld',
                'website' => 'http://modules.domain.tld',
                'keywords' => 'keyword',
                'isactivated' => '1',
                'isinstalled' => '1',
                'module_id' => '1',
                'screenshot' => null,
                'thumbnail' => null
            )
        );
        $result = WidgetModel::getMergedParameters(array(), array(), 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'organization_id' => '1',
                'name' => 'Default organization',
                'shortname' => 'default_organization',
                'active' => '1',
                'widget_model_id' => '2',
                'name' => 'Widget 2',
                'shortname' => 'widget_2',
                'description' => 'Second widget',
                'version' => '3.0.0',
                'directory' => '/dir',
                'author' => 'Author 2',
                'email' => 'author_2@domain.tld',
                'website' => 'http://modules.domain.tld',
                'keywords' => 'keyword',
                'isactivated' => '0',
                'isinstalled' => '1',
                'module_id' => '1',
                'screenshot' => null,
                'thumbnail' => null
            )
        );
        $result = WidgetModel::getMergedParameters(array(), array(), 1, 1);
        $this->assertEquals($testResult, $result);

        $testResult = array(
            array(
                'name' => 'Default organization',
                'name' => 'Widget 1'
            ),
            array(
                'name' => 'Default organization',
                'name' => 'Widget 2'
            ),
            array(
                'name' => 'Client organization',
                'name' => 'Widget 2'
            )
        );
        $result = WidgetModel::getMergedParameters(array('name'), array('name'), -1, 0, null, 'ASC', array(), 'OR');
        $this->assertEquals($testResult, $result);
    }

    public function testgetMergedParametersBySearch()
    {
        $testResult = array(
            array(
                'name' => 'Default organization',
                'name' => 'Widget 1'
            ),
            array(
                'name' => 'Default organization',
                'name' => 'Widget 2'
            ),
            array(
                'name' => 'Client organization',
                'name' => 'Widget 2'
            )
        );
        $result = WidgetModel::getMergedParametersBySearch(array('name'), array('name'), -1, 0, null, 'ASC', array('cfg_widgets_models.shortname' => 'widget'), 'OR');
        $this->assertEquals($testResult, $result);
    }

    public function testGetFirstKey()
    {
        $this->assertEquals('organization_id', WidgetModel::getFirstKey());
    }

    public function testGetSecondKey()
    {
        $this->assertEquals('widget_model_id', WidgetModel::getSecondKey());
    }
}
