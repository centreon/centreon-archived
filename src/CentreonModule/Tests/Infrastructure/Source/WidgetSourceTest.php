<?php
/*
 * Copyright 2005-2019 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonModule\Tests\Infrastructure\Source;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as ContainerWrap;
use Symfony\Component\Finder\Finder;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;
use Centreon\Test\Mock;
use Centreon\Test\Traits\TestCaseExtensionTrait;
use CentreonModule\Infrastructure\Source\WidgetSource;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonLegacy\Core\Configuration\Configuration;
use CentreonModule\Tests\Resources\Traits\SourceDependencyTrait;

class WidgetSourceTest extends TestCase
{
    use TestCaseExtensionTrait;
    use SourceDependencyTrait;

    public static $widgetName = 'test-widget';
    public static $widgetInfo = [
        'title' => 'Curabitur congue porta neque',
        'author' => 'Centreon',
        'email' => 'centreon@mail.loc',
        'website' => 'localhost',
        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent id ante neque.',
        'version' => 'x.y.q',
        'keywords' => 'lorem,ipsum,dolor',
        'stability' => 'release candidate',
        'last_update' => '2011-11-11',
        'release_note' => 'https://github.com/centreon/centreon-dummy/releases',
        'screenshot1' => './resources/screenshot1.png',
        'screenshot2' => './resources/screenshot2.png',
        'screenshot3' => './resources/screenshot3.png',
        'screenshot4' => './resources/screenshot4.png',
        'thumbnail' => './resources/thumbnail.png',
        'url' => './widgets/test-widget/index.php',
    ];
    public static $sqlQueryVsData = [
        "SELECT `directory` AS `id`, `version` FROM `widget_models`" => [
            [
                'id' => 'test-widget',
                'version' => 'x.y.z',
            ],
        ],
    ];

    protected function setUp(): void
    {
        // mount VFS
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
        $this->fs->get('/')->add('widgets', new Directory([]));
        $this->fs->get('/widgets')->add(static::$widgetName, new Directory([]));
        $this->fs->get('/widgets/' . static::$widgetName)
            ->add(WidgetSource::CONFIG_FILE, new File(static::buildConfContent()))
        ;

        // provide services
        $container = new Container;
        $container['finder'] = new Finder;
        $container['configuration'] = $this->createMock(Configuration::class);

        // DB service
        $container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER] = new Mock\CentreonDBManagerService;
        foreach (static::$sqlQueryVsData as $query => $data) {
            $container[\Centreon\ServiceProvider::CENTREON_DB_MANAGER]->addResultSet($query, $data);
        }

        $this->setUpSourceDependency($container);

        $containerWrap = new ContainerWrap($container);

        $this->source = $this->getMockBuilder(WidgetSource::class)
            ->onlyMethods([
                'getPath',
            ])
            ->setConstructorArgs([
                $containerWrap,
            ])
            ->getMock()
        ;
        $this->source
            ->method('getPath')
            ->will($this->returnCallback(function () {
                    $result = 'vfs://widgets/';

                    return $result;
            }))
        ;
    }

    public function tearDown(): void
    {
        // unmount VFS
        $this->fs->unmount();
    }

    public function testGetList()
    {
        $result = $this->source->getList();

        $this->assertTrue(is_array($result));

        $result2 = $this->source->getList('missing-widget');
        $this->assertEquals([], $result2);
    }

    public function testGetDetail()
    {
        (function () {
            $result = $this->source->getDetail('missing-widget');

            $this->assertNull($result);
        })();

        (function () {
            $result = $this->source->getDetail(static::$widgetName);

            $this->assertInstanceOf(Module::class, $result);
        })();
    }

    public function testCreateEntityFromConfig()
    {
        $configFile = static::getConfFilePath();
        $result = $this->source->createEntityFromConfig($configFile);

        $this->assertInstanceOf(Module::class, $result);
        $this->assertEquals(static::$widgetName, $result->getId());
        $this->assertEquals(WidgetSource::TYPE, $result->getType());
        $this->assertEquals(static::$widgetInfo['title'], $result->getName());
        $this->assertEquals(static::$widgetInfo['author'], $result->getAuthor());
        $this->assertEquals(static::$widgetInfo['version'], $result->getVersion());
        $this->assertEquals(static::$widgetInfo['keywords'], $result->getKeywords());
        $this->assertTrue($result->isInstalled());
        $this->assertFalse($result->isUpdated());
    }

    public static function getConfFilePath(): string
    {
        return 'vfs://widgets/' . static::$widgetName . '/' . WidgetSource::CONFIG_FILE;
    }

    public static function buildConfContent(): string
    {
        $widgetInfo = static::$widgetInfo;
        $result = <<<CONF
<configs>
    <title>{$widgetInfo['title']}</title>
    <author>{$widgetInfo['author']}</author>
    <email>{$widgetInfo['email']}</email>
    <website>{$widgetInfo['website']}</website>
    <description>{$widgetInfo['description']}</description>
    <version>{$widgetInfo['version']}</version>
    <keywords>{$widgetInfo['keywords']}</keywords>
    <stability>{$widgetInfo['stability']}</stability>
    <last_update>{$widgetInfo['last_update']}</last_update>
    <release_note>{$widgetInfo['release_note']}</release_note>
    <screenshot>{$widgetInfo['screenshot1']}</screenshot>
    <screenshot>{$widgetInfo['screenshot2']}</screenshot>
    <screenshots>
        <screenshot src="{$widgetInfo['screenshot4']}"/>
    </screenshots>
    <thumbnail>{$widgetInfo['thumbnail']}</thumbnail>
    <url>{$widgetInfo['url']}</url>
</configs>
CONF;

        return $result;
    }
}
