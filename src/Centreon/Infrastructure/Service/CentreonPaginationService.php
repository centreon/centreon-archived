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

namespace Centreon\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use Centreon\ServiceProvider;
use Centreon\Application\DataRepresenter;
use Centreon\Infrastructure\CentreonLegacyDB\Interfaces\PaginationRepositoryInterface;
use ReflectionClass;
use JsonSerializable;
use Exception;
use RuntimeException;

class CentreonPaginationService
{

    const LIMIT_MAX = 500;

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $db;

    /**
     * @var mixed
     */
    protected $filters;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var string
     */
    protected $repository;

    /**
     * @var array
     */
    protected $ordering;

    /**
     * @var array
     */
    protected $extras;

    /**
     * @var string
     */
    protected $dataRepresenter;

    /**
     * List of required services
     *
     * @return array
     */
    public static function dependencies(): array
    {
        return [
            ServiceProvider::CENTREON_DB_MANAGER,
        ];
    }

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get(ServiceProvider::CENTREON_DB_MANAGER);
    }

    /**
     * Set pagination filters
     *
     * @param mixed $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * Set pagination limit
     *
     * @param int $limit
     * @throws \RuntimeException
     */
    public function setLimit(int $limit = null)
    {
        if ($limit !== null && $limit > static::LIMIT_MAX) {
            throw new RuntimeException(sprintf('Max value of limit has to be %d instead %d', static::LIMIT_MAX, $limit));
        } elseif ($limit !== null && $limit < 1) {
            throw new RuntimeException(sprintf('Minimum value of limit has to be 1 instead %d', $limit));
        }

        $this->limit = $limit;
    }

    /**
     * Set pagination offset
     *
     * @param int $offset
     * @throws \RuntimeException
     */
    public function setOffset(int $offset = null)
    {
        if ($offset !== null && $offset < 1) {
            throw new RuntimeException(sprintf('Minimum value of offset has to be 1 instead %d', $offset));
        }

        $this->offset = $offset;
    }

    /**
     * Set pagination order
     *
     * @param int $offset
     * @throws \RuntimeException
     */
    public function setOrder($field, $order)
    {
        $order = (!empty($order) && ($order == "DESC")) ? $order : 'ASC';

        $this->ordering = ['field' => $field, 'order'=> $order];
    }

    /**
     * Set pagination order
     *
     * @param array $extras
     * @throws \RuntimeException
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
    }

    /**
     * Set repository class
     *
     * @param string $repository
     * @throws \Exception
     */
    public function setRepository(string $repository)
    {
        $interface = PaginationRepositoryInterface::class;
        $ref = new ReflectionClass($repository);
        $hasInterface = $ref->isSubclassOf($interface);

        if ($hasInterface === false) {
            throw new Exception(sprintf('Repository class %s has to implement %s', $repository, $interface));
        }

        $this->repository = $repository;
    }

    /**
     * Set data representer class
     *
     * @param string $dataRepresenter
     * @throws \Exception
     */
    public function setDataRepresenter(string $dataRepresenter)
    {
        $interface = JsonSerializable::class;
        $ref = new ReflectionClass($dataRepresenter);
        $hasInterface = $ref->isSubclassOf($interface);

        if ($hasInterface === false) {
            throw new Exception(sprintf('Class %s has to implement %s to be DataRepresenter', $dataRepresenter, $interface));
        }

        $this->dataRepresenter = $dataRepresenter;
    }

    /**
     * Get paginated list
     *
     * @return \Centreon\Application\DataRepresenter\Listing
     */
    public function getListing(): DataRepresenter\Listing
    {
        $entities = $this->db
            ->getRepository($this->repository)
            ->getPaginationList($this->filters, $this->limit, $this->offset, $this->ordering, $this->extras);

        $total = $this->db
            ->getRepository($this->repository)
            ->getPaginationListTotal();

        $result = new DataRepresenter\Listing($entities, $total, $this->offset, $this->limit, $this->dataRepresenter);

        return $result;
    }

    /**
     * Get response data representer with paginated list
     *
     * @return \Centreon\Application\DataRepresenter\Response
     */
    public function getResponse(): DataRepresenter\Response
    {
        return new DataRepresenter\Response($this->getListing());
    }
}