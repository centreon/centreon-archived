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
