<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

namespace Centreon\Tests\Resources\Traits;

use Centreon\Tests\Resources\CheckPoint;
use Symfony\Component\Serializer\Serializer;
use Centreon\ServiceProvider;

/**
 * Trait with extension methods to test the response from webservice
 *
 * @author Centreon
 * @version 1.0.0
 * @package centreon
 * @subpackage test
 */
trait WebServiceExecuteTestTrait
{
    /**
     * Path to fixtures
     *
     * @var string
     */
    protected $fixturePath;

    /**
     * Compare response with control value
     *
     * Require to be set property fixturePath and webservice object to be property of the test case
     *
     * <example>
     * $this->fixturePath = __DIR__ . '/../../Resource/Fixture/';
     * </example>
     *
     * @param string $method
     * @param string $controlJsonFile
     */
    protected function executeTest($method, $controlJsonFile)
    {
        $result = $this->webservice->{$method}();
        $file = realpath($this->fixturePath . $controlJsonFile);

        $this->assertInstanceOf(\JsonSerializable::class, $result);
        $this->assertStringEqualsFile(
            $file,
            json_encode($result),
            "Fixture file with path {$file}"
        );
    }

    /**
     * Mock the repository methods related with the pagination
     *
     * @param array $entities
     * @param \Centreon\Tests\Resources\CheckPoint $checkPoints
     * @param string $repositoryClass
     * @param callable $callback
     */
    protected function mockRepository(
        array $entities,
        CheckPoint $checkPoints,
        string $repositoryClass,
        array $expectedArgs = null
    ) {
        $methodGetPaginationList = 'getPaginationList';
        $methodGetPaginationListTotal = 'getPaginationListTotal';

        $checkPoints
            ->add($methodGetPaginationList)
            ->add($methodGetPaginationListTotal);

        $this->db
            ->resetResultSet()
            ->addRepositoryMock($repositoryClass, (function () use (
                $entities,
                $checkPoints,
                $repositoryClass,
                $methodGetPaginationList,
                $methodGetPaginationListTotal,
                $expectedArgs
            ) {
                $repository = $this->createMock($repositoryClass);

                $repository->method($methodGetPaginationList)
                    ->will($this->returnCallback(function () use (
                        $entities,
                        $checkPoints,
                        $methodGetPaginationList,
                        $expectedArgs
                    ) {
                        $checkPoints->mark($methodGetPaginationList);

                        if ($expectedArgs) {
                            $this->assertEquals($expectedArgs, func_get_args());
                        }

                        return $entities;
                    }));

                $repository->method($methodGetPaginationListTotal)
                    ->will($this->returnCallback(function () use (
                        $entities,
                        $checkPoints,
                        $methodGetPaginationListTotal
                    ) {
                        $checkPoints->mark($methodGetPaginationListTotal);

                        return count($entities);
                    }));

                return $repository;
            })());
    }

    /**
     * Make query method of the webservice
     *
     * @param string $method
     * @param array $filters
     */
    protected function mockQuery(array $filters = [])
    {
        $this->webservice
            ->method('query')
            ->will($this->returnCallback(function () use ($filters) {
                return $filters;
            }));
    }

    /**
     * Get the Serializer service from DI
     *
     * @return \Symfony\Component\Serializer\Serializer
     */
    protected static function getSerializer(): Serializer
    {
        return loadDependencyInjector()[ServiceProvider::SERIALIZER];
    }
}
