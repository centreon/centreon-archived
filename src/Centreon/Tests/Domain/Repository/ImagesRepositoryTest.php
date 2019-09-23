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

namespace Centreon\Tests\Domain\Repository;

use Centreon\Domain\Entity\Image;
use Centreon\Domain\Entity\ImageDir;
use Centreon\Domain\Repository\ImagesRepository;
use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Tests\Resource\Traits;

/**
 * @group Centreon
 * @group ORM-repository
 */
class ImagesRepositoryTest extends TestCase
{
    use Traits\CheckListOfIdsTrait;

    protected $datasets = [];
    protected $repository;

    protected function setUp()
    {
        $db = new CentreonDB;
        $this->datasets = [
            [
                'query' => "SELECT * FROM `view_img_dir`,`view_img_dir_relation` vidr,`view_img` "
                    . "WHERE `img_id` = `vidr`.`img_img_id` AND `dir_id` = `vidr`.`dir_dir_parent_id` "
                    . "ORDER BY `dir_name`, `img_name`",
                'data' => [
                    [
                        'img_id' => '1',
                        'img_name' => 'centreon',
                        'img_path' => 'centreon.png'
                    ],
                ],
            ],
            [
                'query' => "SELECT * FROM `view_img_dir`,`view_img_dir_relation` vidr,`view_img` "
                . "WHERE `img_id` = `vidr`.`img_img_id` AND `dir_id` = `vidr`.`dir_dir_parent_id` "
                . "AND `img_name` LIKE :search AND `img_id` IN (:id0) "
                . "LIMIT :limit OFFSET :offset ORDER BY `dir_name`, `img_name`",
                'data' => [
                    [
                        'img_id' => '1',
                        'img_name' => 'centreon',
                        'img_path' => 'centreon.png'
                    ],
                ],
            ],
            [
                'query' => "SELECT FOUND_ROWS() AS number",
                'data' => [
                    [
                        'number' => '10',
                    ],
                ],
            ],
        ];
        foreach ($this->datasets as $dataset) {
            $db->addResultSet($dataset['query'], $dataset['data']);
            unset($dataset);
        }
        $this->repository = new ImagesRepository($db);
    }

    public function testCheckListOfIds()
    {
        $this->checkListOfIdsTrait(
            ImagesRepository::class,
            'checkListOfIds',
            Image::TABLE,
            'img_id'
        );
    }

    public function testGetPaginationList()
    {
        $result = $this->repository->getPaginationList();

        $data = $this->datasets[0]['data'][0];

        $imgDir = new ImageDir();
        $entity = new Image();
        $entity->setImgId($data['img_id']);
        $entity->setImgName($data['img_name']);
        $entity->setImgPath($data['img_path']);
        $entity->setImageDir($imgDir);

        $this->assertEquals($entity->getImgName(), $result[0]->getImgName());
    }

    public function testGetPaginationListWithArguments()
    {
        $filters = [
            'search' => 'name',
            'ids' => ['ids'],
        ];
        $limit = 1;
        $offset = 0;

        $data = $this->datasets[1]['data'][0];

        $imgDir = new ImageDir();
        $entity = new Image();
        $entity->setImgId($data['img_id']);
        $entity->setImgName($data['img_name']);
        $entity->setImgPath($data['img_path']);
        $entity->setImageDir($imgDir);

        $this->assertEquals([
                $entity,
            ], $this->repository->getPaginationList($filters, $limit, $offset));
    }

    public function testGetPaginationListTotal()
    {
        $total = (int)$this->datasets[2]['data'][0]['number'];
        $result = $this->repository->getPaginationListTotal();
        $this->assertEquals($total, $result);
    }
}
