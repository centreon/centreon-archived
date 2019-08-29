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

namespace ConfigGenerateRemote;

use \PDO;

class Media extends AbstractObject
{
    private $medias = null;
    protected $table = 'view_img';
    protected $generateFilename = 'view_img.infile';
    protected $attributesSelect = '
        img_id,
        img_name,
        img_path,
        img_comment,
        dir_id,
        dir_name,
        dir_alias,
        dir_comment
    ';
    protected $attributesWrite = [
        'img_id',
        'img_name',
        'img_path',
        'img_comment',
    ];
    protected $path_img = null;

    private function getMedias()
    {
        $query = "
            SELECT $this->attributesSelect
            FROM view_img, view_img_dir_relation, view_img_dir " .
            "WHERE view_img.img_id = view_img_dir_relation.img_img_id " .
            "AND view_img_dir_relation.dir_dir_parent_id = view_img_dir.dir_id";
        $stmt = $this->backendInstance->db->prepare($query);
        $stmt->execute();
        $this->medias = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        $stmt = $this->backendInstance->db->prepare('SELECT * FROM options WHERE `key` = "nagios_path_img"');
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->pathImg = $row['value'];
    }

    protected function copyMedia($dir, $file)
    {
        $this->backendInstance->createDirectories([$this->backendInstance->getPath() . '/media/' . $dir]);
        @copy($this->pathImg . '/' . $dir . '/' . $file,
            $this->backendInstance->getPath() . '/media/' . $dir . '/' . $file);
    }

    public function getMediaPathFromId($mediaId)
    {
        if (is_null($this->medias)) {
            $this->getMedias();
        }

        $result = null;
        if (!is_null($mediaId) && isset($this->medias[$mediaId])) {
            $result = $this->medias[$mediaId]['dir_name'] . '/' . $this->medias[$mediaId]['img_path'];
            if ($this->checkGenerate($mediaId)) {
                return $result;
            }

            $media = [
                'img_id' => $mediaId,
                'img_name' => $this->medias[$mediaId]['img_name'],
                'img_path' => $this->medias[$mediaId]['img_path'],
                'img_comment' => $this->medias[$mediaId]['img_comment'],
            ];
            $this->generateObjectInFile($media, $mediaId);
            viewImgDirRelation::getInstance($this->dependencyInjector)
                ->addRelation($mediaId, $this->medias[$mediaId]['dir_id']);
            viewImageDir::getInstance($this->dependencyInjector)
                ->add($this->medias[$mediaId], $this->medias[$mediaId]['dir_id']);
            $this->copyMedia($this->medias[$mediaId]['dir_name'], $this->medias[$mediaId]['img_path']);
        }

        return $result;
    }
}
