<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
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

namespace Centreon\Tests\Domain\Repository;

use Centreon\Domain\Entity\Image;
use Centreon\Domain\Entity\ImageDir;
use Centreon\Domain\Repository\ImagesRepository;
use PHPUnit\Framework\TestCase;
use Centreon\Test\Mock\CentreonDB;
use Centreon\Tests\Resources\Traits;

/**
 * @group Centreon
 * @group ORM-repository
 */
class ImagesRepositoryTest extends TestCase
{
    use Traits\CheckListOfIdsTrait;

    /**
     * @var array
     */
    protected $datasets = [];

    /**
     * @var \Centreon\Domain\Repository\ImagesRepository
     */
    protected $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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

    /**
     * Test the method checkListOfIds
     */
    public function testCheckListOfIds(): void
    {
        $this->checkListOfIdsTrait(
            ImagesRepository::class,
            'checkListOfIds',
            Image::TABLE,
            'img_id'
        );
    }

    /**
     * Test the method getPaginationList
     */
    public function testGetPaginationList(): void
    {
        $result = $this->repository->getPaginationList();
        $expectedImage = new Image();
        if (
            array_key_exists('img_id', $result[0])
            && array_key_exists('img_name', $result[0])
            && array_key_exists('img_path', $result[0])
        ) {
            $expectedImage->setImgId($result[0]['img_id']);
            $expectedImage->setImgName($result[0]['img_name']);
            $expectedImage->setImgPath($result[0]['img_path']);
        }

        $data = $this->datasets[0]['data'][0];
        $entity = new Image();
        $entity->setImgId($data['img_id']);
        $entity->setImgName($data['img_name']);
        $entity->setImgPath($data['img_path']);

        $this->assertEquals($entity, $expectedImage);
    }

    /**
     * Test the method getPaginationList with a different arguments
     */
    public function testGetPaginationListWithArguments(): void
    {
        $filters = [
            'search' => 'name',
            'ids' => ['ids'],
        ];
        $limit = 1;
        $offset = 0;

        $result = $this->repository->getPaginationList($filters, $limit, $offset);
        $expectedImage = new Image();
        if (
            array_key_exists('img_id', $result[0])
            && array_key_exists('img_name', $result[0])
            && array_key_exists('img_path', $result[0])
        ) {
            $expectedImage->setImgId($result[0]['img_id']);
            $expectedImage->setImgName($result[0]['img_name']);
            $expectedImage->setImgPath($result[0]['img_path']);
        }

        $data = $this->datasets[1]['data'][0];
        $entity = new Image();
        $entity->setImgId($data['img_id']);
        $entity->setImgName($data['img_name']);
        $entity->setImgPath($data['img_path']);

        $this->assertEquals($entity, $expectedImage);
    }

    /**
     * Test the method getPaginationTotal
     */
    public function testGetPaginationListTotal(): void
    {
        $total = (int)$this->datasets[2]['data'][0]['number'];
        $result = $this->repository->getPaginationListTotal();
        $this->assertEquals($total, $result);
    }
}
